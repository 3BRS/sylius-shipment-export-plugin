<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusShipmentExportPlugin\Factory;

use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use ThreeBRS\SyliusShipmentExportPlugin\Factory\Exception\ShipmentExporterNotFoundException;
use ThreeBRS\SyliusShipmentExportPlugin\Model\ShipmentExporterInterface;

readonly class ShipmentExportFactory
{
    public function __construct(
        private RequestStack $requestStack,
        private ServiceRegistryInterface $serviceRegistry,
    ) {
    }

    /**
     * @throws ShipmentExporterNotFoundException
     *
     * @noinspection PhpUnused used as factory in services
     */
    public function getExporter(): ?ShipmentExporterInterface
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return null;
        }

        $exporterName = $request->get('exporterName');
        if ($exporterName === null) {
            return null;
        }

        assert(is_string($exporterName));

        if ($this->serviceRegistry->has($exporterName) === false) {
            throw new  ShipmentExporterNotFoundException(sprintf(
                'Exporter with %s exporterName could not be found',
                $exporterName,
            ));
        }

        $exporter = $this->serviceRegistry->get($exporterName);
        assert($exporter instanceof ShipmentExporterInterface);

        return $exporter;
    }
}
