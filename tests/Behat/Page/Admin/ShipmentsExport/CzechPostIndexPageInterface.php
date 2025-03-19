<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusShipmentExportPlugin\Behat\Page\Admin\ShipmentsExport;

use Sylius\Behat\Page\Admin\Crud\IndexPageInterface as BaseIndexPageInterface;

interface CzechPostIndexPageInterface extends BaseIndexPageInterface
{
	public function downloadCsv(): void;
}
