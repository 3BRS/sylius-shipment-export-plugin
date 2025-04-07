<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusShipmentExportPlugin\Behat\Page\Admin\ShipmentsExport;

use Sylius\Behat\Page\Admin\Crud\IndexPage as BaseIndexPage;

class CzechPostIndexPage extends BaseIndexPage implements CzechPostIndexPageInterface
{
	public function downloadCsv(): void
	{
		$downloadCsv = $this->getElement('download_csv');
		$downloadCsv->click();
	}

	protected function getDefinedElements(): array
	{
		return array_merge(parent::getDefinedElements(), [
			'download_csv' => '#shipment_exports_only_download',
		]);
	}
}
