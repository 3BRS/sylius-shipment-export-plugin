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

Hint: see test application for example configuration [test/Application](tests%2FApplication)

1. Get plugin

   ```bash
   composer require 3brs/sylius-shipment-export-plugin
   ```

1. Register plugin in your `config/bundles.php`

   ```php
      return [
         # ...
         ThreeBRS\SyliusShipmentExportPlugin\ThreeBRSSyliusShipmentExportPlugin::class => ['all' => true],
      ];
   ```

1. Add routing to `config/_routes.yaml`
    ```yaml
    threebrs_shipment_export_plugin:
        resource: "@ThreeBRSSyliusShipmentExportPlugin/Resources/config/routing.yml"
        prefix: /admin
   ```

### Usage

You can use predefined CSV type for shipment providers Geis and Czech Post) or write your own exporter.

Your custom exporter has to implement `ThreeBRS\SyliusShipmentExportPlugin\Model\ShipmentExporterInterface`
and must be defined as service. Check out our sample implementations.


Predefined shipping providers:

* Czech post
```yaml
ThreeBRS\SyliusShipmentExportPlugin\Model\CeskaPostaShipmentExporter:
    public: true
    arguments:
        $currencyConverter: '@sylius.currency_converter'    
    tags:
        - name: threebrs.shipment_exporter_type
          type: 'ceska_posta'
          label: 'Česká pošta'
```

* Geis
```yaml
ThreeBRS\SyliusShipmentExportPlugin\Model\GeisShipmentExporter:
    public: true
    arguments:
        $currencyConverter: '@sylius.currency_converter'
    tags:
        - name: threebrs.shipment_exporter_type
          type: 'geis'
          label: 'Geis'
```

## Development

### Usage

- Alter plugin in `/src`
- See `bin/` dir for useful commands

### Testing

After your changes you must ensure that the tests are still passing.

```bash
composer install
bin/console doctrine:database:create --if-not-exists --env=test
bin/console doctrine:schema:update --complete --force --env=test
yarn --cwd tests/Application install
yarn --cwd tests/Application build

bin/behat
bin/phpstan.sh
bin/ecs.sh
vendor/bin/phpspec run
```

### Opening Sylius with your plugin

1. Install symfony CLI command: https://symfony.com/download
    - hint: for Docker (with Ubuntu) use _Debian/Ubuntu — APT based
      Linux_ installation steps as `root` user and without `sudo` command
        - you may need to install `curl` first ```apt-get update && apt-get install curl --yes```
2. Run app

```bash
(cd tests/Application && APP_ENV=test bin/console sylius:fixtures:load)
(cd tests/Application && APP_ENV=test symfony server:start --dir=public --port=8080)
```

- change `APP_ENV` to `dev` if you need it

License
-------
This library is under the MIT license.

Credits
-------
Developed by [3BRS](https://3brs.com)<br>
Forked from [manGoweb](https://github.com/mangoweb-sylius/SyliusShipmentExportPlugin).
