<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusShipmentExportPlugin\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use SM\Factory\FactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Repository\ShipmentRepositoryInterface;
use Sylius\Component\Shipping\ShipmentTransitions;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use ThreeBRS\SyliusShipmentExportPlugin\Model\ShipmentExporterInterface;
use Twig\Environment;

class ShipmentExportController
{
    public function __construct(
        private Environment $templatingEngine,
        private EntityManager $entityManager,
        private FactoryInterface $stateMachineFatory,
        private EventDispatcherInterface $eventDispatcher,
        private RouterInterface $router,
        private ShipmentExporterInterface $shipmentExporter,
        private ParameterBagInterface $parameterBag,
        private ShipmentRepositoryInterface $shipmentRepository,
        private TranslatorInterface $translator
    )
    {
    }

    public function showAllUnshipShipments(string $exporterName): Response
    {
        $shippingCodes = $this->shipmentExporter->getShippingMethodsCodes();
        $shipments = $this->getReadyShipments($shippingCodes);

        return new Response(
            $this->templatingEngine->render(
                '@ThreeBRSSyliusShipmentExportPlugin/index.html.twig',
                [
                    'shipments' => $shipments,
                    'exporterName' => $exporterName,
                    'exporter' => $this->shipmentExporter,
                    'exporterLabel' => $this->getExporterLabel($exporterName),
                ],
            ),
        );
    }

    public function exportShipmentsAction(Request $request, string $exporterName): StreamedResponse
    {
        $ids = $request->get('ids', []);
        $shipments = $this->getShipmentsByIds($ids);
        $questionsArray = $request->get('questions', []);

        return $this->doCsvFile($shipments, $exporterName, $questionsArray);
    }

    public function markAsSend(Request $request, Session $session, string $exporterName): RedirectResponse
    {
        $ids = $request->get('ids', []);
        $shipments = $this->getShipmentsByIds($ids);

        foreach ($shipments as $shipment) {
            assert($shipment instanceof ShipmentInterface);

            $this->shipShipment($shipment);
        }
        $this->entityManager->flush();

        foreach ($shipments as $shipment) {
            assert($shipment instanceof ShipmentInterface);

            $this->dispatchEvent('sylius.shipment.post_ship', $shipment);
        }

        $message = $this->translator->trans('threebrs.ui.shippingExport.exportAndShipSuccess', ['{{ count }}' => count($shipments)]);
        $this->addFlashMessage('success', $message, $session);

        $url = $this->router->generate('threebrs_admin_Shipment_export', ['exporterName' => $exporterName]);

        return new RedirectResponse($url);
    }

    private function addFlashMessage(string $type, string $message, Session $session): void
    {
        $session->getFlashBag()->add($type, $message);
    }

    public function getExporterLabel(string $exporterCode): string
    {
        $exporters = $this->parameterBag->get('threebrs.shipment_exporters');

        return array_key_exists($exporterCode, $exporters) ? $exporters[$exporterCode] : '';
    }

    public function getShipmentsByIds(array $ids): array
    {
        /** @var EntityRepository $shipmentRepository */
        $shipmentRepository = $this->shipmentRepository;

        return $shipmentRepository->createQueryBuilder('s')
            ->select('s')
            ->join('s.order', 'o')
            ->andWhere('s.id in (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('o.number', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getReadyShipments(array $shippingMethodCodes): array
    {
        /** @var EntityRepository $shipmentRepository */
        $shipmentRepository = $this->shipmentRepository;

        return $shipmentRepository->createQueryBuilder('s')
            ->select('s')
            ->join('s.method', 'm')
            ->join('s.order', 'o')
            ->join('o.payments', 'p')
            ->join('p.method', 'pm')
            ->join('pm.gatewayConfig', 'gc')
            ->where('s.state = :shipmentStateReady')
            ->andWhere('m.code IN (:shippingMethodCode)')
            ->andWhere('p.state IN (:supportedPaymentStates) ')
            ->andWhere('(gc.factoryName = :offline OR p.state = :paymentCompleteState)')
            ->setParameter('offline', 'offline')
            ->setParameter('shipmentStateReady', ShipmentInterface::STATE_READY)
            ->setParameter('paymentCompleteState', PaymentInterface::STATE_COMPLETED)
            ->setParameter('supportedPaymentStates', [PaymentInterface::STATE_COMPLETED, PaymentInterface::STATE_NEW])
            ->setParameter('shippingMethodCode', $shippingMethodCodes)
            ->orderBy('o.number', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function dispatchEvent(string $name, ShipmentInterface $shipment): void
    {
        $event = new GenericEvent($shipment);
        $this->eventDispatcher->dispatch($event, $name);
    }

    public function shipShipment(ShipmentInterface $shipment): void
    {
        $this->dispatchEvent('sylius.shipment.pre_ship', $shipment);

        $stateMachine = $this->stateMachineFatory->get($shipment, ShipmentTransitions::GRAPH);
        $stateMachine->apply(ShipmentTransitions::TRANSITION_SHIP);
    }

    public function doCsvFile(array $shipments, string $shippingMethodCode, array $questionsArray): StreamedResponse
    {
        $response = new StreamedResponse();
        $response->setCallback(function () use ($shipments, $questionsArray): void {
            $handle = fopen('php://output', 'w+b');
            if (!$handle) {
                throw new \RuntimeException('Cannot open file: php://output');
            }

            if ($this->shipmentExporter->getHeaders() !== null) {
                foreach ($this->shipmentExporter->getHeaders() as $header) {
                    fputcsv(
                        $handle,
                        $header,
                        $this->shipmentExporter->getDelimiter(),
                    );
                }
            }

            foreach ($shipments as $shipment) {
                assert($shipment instanceof ShipmentInterface);
                fputcsv(
                    $handle,
                    $this->shipmentExporter->getRow($shipment, $questionsArray),
                    $this->shipmentExporter->getDelimiter(),
                );
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $shippingMethodCode . '.csv"');

        return $response;
    }
}
