<p align="center">
    <a href="https://www.3brs.com" target="_blank">
        <img src="https://3brs1.fra1.cdn.digitaloceanspaces.com/3brs/logo/3BRS-logo-sylius-200.png"/>
    </a>
</p>
<h1 align="center">
Shipment Export Plugin
<br />
	<a href="https://packagist.org/packages/3brs/sylius-shipment-export-plugin" title="License" target="_blank">
        <img src="https://img.shields.io/packagist/l/3brs/sylius-shipment-export-plugin" />
    </a>
    <a href="https://packagist.org/packages/3brs/sylius-shipment-export-plugin" title="Version" target="_blank">
        <img src="https://img.shields.io/packagist/v/3brs/sylius-shipment-export-plugin" />
    </a>
    <a href="https://circleci.com/gh/3BRS/sylius-shipment-export-plugin" title="Build status" target="_blank">
        <img src="https://circleci.com/gh/3BRS/sylius-shipment-export-plugin.svg?style=shield" />
    </a>
</h1>

## Features

* See list of all ready to ship orders (offline payment method or payment completed for non offline method)
* Mark more orders at once as shipped
* Download CSV for submitting batch shipments with Geis
* Download CSV for submitting batch shipments with Czech Post
* You can easily extend the module to support custom CSV format for other shipping providers


<p align="center">
	<img src="https://raw.githubusercontent.com/3BRS/sylius-shipment-export-plugin/master/doc/menu.png"/>
</p>


<p align="center">
	<img src="https://raw.githubusercontent.com/3BRS/sylius-shipment-export-plugin/master/doc/list.png"/>
</p>

## Installation

1. Run `composer require 3brs/sylius-shipment-export-plugin`.
2. Register `ThreeBRS\SyliusShipmentExportPlugin\ThreeBRSSyliusShipmentExportPlugin::class => ['all' => true]` in your `config/bundles.php`.
3. Import routes in your `config/routes.yaml`

```
threebrs_shipment_export_plugin:
    resource: "@ThreeBRSSyliusShipmentExportPlugin/config/admin_routing.{yml,yaml}"
    prefix: /admin
```

4. Import plugin configuration in your `config/packages/_sylius.yaml`

```yaml
imports:
    - { resource: "@ThreeBRSSyliusShipmentExportPlugin/config/config.{yml,yaml}" }
```

### Usage

You can use predefined CSV type for shipment providers Geis and Czech Post) or write your own exporter.

Your custom exporter has to implement `ThreeBRS\SyliusShipmentExportPlugin\Model\ShipmentExporterInterface`
and must be defined as service. Check out our sample implementations.

Predefined shipping providers:

* Czech post
```
ThreeBRS\SyliusShipmentExportPlugin\Model\CeskaPostaShipmentExporter:
    public: true
    arguments:
        $currencyConverter: '@sylius.converter.currency'    
    tags:
        - name: threebrs.shipment_exporter_type
          type: 'ceska_posta'
          label: 'Česká pošta'
```

* Geis
```
ThreeBRS\SyliusShipmentExportPlugin\Model\GeisShipmentExporter:
    public: true
    arguments:
        $currencyConverter: '@sylius.converter.currency'
    tags:
        - name: threebrs.shipment_exporter_type
          type: 'geis'
          label: 'Geis'
```

### Shipping methods

Your shipping methods must have a code that matches the code of the shipping method in the exporter
 - for Geis see `\ThreeBRS\SyliusShipmentExportPlugin\Model\GeisShipmentExporter::getShippingMethodsCodes`
 - for Czech Post see `\ThreeBRS\SyliusShipmentExportPlugin\Model\CeskaPostaShipmentExporter::getShippingMethodsCodes`

## Development

### Usage

- Develop your plugin in `/src`
- See `bin/` for useful commands

### Testing

After your changes you must ensure that the tests are still passing.

```bash
$ composer install
$ bin/phpstan.sh
$ bin/ecs.sh
```

License
-------
This library is under the MIT license.

Credits
-------
Developed by [3BRS](https://3brs.com)<br>
Forked from [manGoweb](https://github.com/mangoweb-sylius/SyliusShipmentExportPlugin).
