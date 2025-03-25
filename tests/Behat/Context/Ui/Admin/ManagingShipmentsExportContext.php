<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusShipmentExportPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\ThreeBRS\SyliusShipmentExportPlugin\Behat\Page\Admin\ShipmentsExport\CzechPostIndexPageInterface;
use Webmozart\Assert\Assert;

final class ManagingShipmentsExportContext implements Context
{
	public function __construct(
		private CzechPostIndexPageInterface $indexPage,
	) {}

	/**
	 * @When I browse shipments export for shipping Czech post
	 */
	public function iBrowseShipmentsExport(): void
	{
		/**
		 * See exporter name in @link tests/Application/config/services_test.yaml
		 * and see route parameters in @link src/Resources/config/routing.yml
		 */
		$this->indexPage->open(['exporterName' => 'czech_post']);
	}

	/**
	 * @Then I should see( only) :count shipment(s) in the list
	 * @Then I should see a single shipment in the list
	 */
	public function iShouldSeeCountShipmentsInList(int $count = 1): void
	{
		Assert::same($this->indexPage->countItems(), $count);
	}

	/**
	 * @Then I can select download CSV from menu to export shipments for Czech post
	 */
	public function iCanSelectDownloadCsvFromMenuToExportShipmentsForCzechPost(): void
	{
		$this->indexPage->downloadCsv();
	}
}
