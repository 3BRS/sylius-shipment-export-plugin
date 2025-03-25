<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusShipmentExportPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ShipmentExportMenuBuilder
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
        private TranslatorInterface $translator,
    ) {
    }

    public function buildMenu(MenuBuilderEvent $event): void
    {
        $exporters = $this->parameterBag->get('threebrs.shipment_exporters');
        if (!is_iterable($exporters)) {
            return;
        }

        foreach ($exporters as $exporterCode => $exporterName) {
            $sales = $event->getMenu()->getChild('sales');
            assert($sales !== null);

            $sales->addChild('Shipment_exports_' . $exporterCode, [
                'route' => 'threebrs_admin_Shipment_export',
                'routeParameters' => ['exporterName' => $exporterCode],
            ])->setName(sprintf(
                '%s: %s',
                $this->translator->trans('threebrs.ui.shippingExport.menu.exportShipment'),
                $exporterName,
            ))->setLabelAttribute('icon', 'arrow up');
        }
    }
}
