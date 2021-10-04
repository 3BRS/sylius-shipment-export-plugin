<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusShipmentExportPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use ThreeBRS\SyliusShipmentExportPlugin\DependencyInjection\Compiler\RegisterShipmentExporersPass;

class ThreeBRSSyliusShipmentExportPlugin extends Bundle
{
    use SyliusPluginTrait;

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterShipmentExporersPass());
    }
}
