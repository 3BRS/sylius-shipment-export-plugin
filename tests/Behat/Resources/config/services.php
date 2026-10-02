<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Tests\ThreeBRS\SyliusShipmentExportPlugin\Behat\Context\Ui\Admin\ManagingShipmentsExportContext;
use Tests\ThreeBRS\SyliusShipmentExportPlugin\Behat\Page\Admin\ShipmentsExport\CzechPostIndexPage;
use Tests\ThreeBRS\SyliusShipmentExportPlugin\Behat\Page\Admin\ShipmentsExport\CzechPostIndexPageInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->public();

    $services->set('theebrs_sylius.shipment_export.behat.context.ui.admin.managing_shipments_export', ManagingShipmentsExportContext::class)
        ->args([
            service(CzechPostIndexPageInterface::class),
        ]);

    $services->set(CzechPostIndexPageInterface::class, CzechPostIndexPage::class)
        ->private()
        ->parent('sylius.behat.page.admin.crud.index')
        ->args([
            '$routeName' => 'threebrs_admin_shipment_export',
        ]);
};
