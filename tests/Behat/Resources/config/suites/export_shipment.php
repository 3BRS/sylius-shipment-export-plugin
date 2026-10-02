<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withSuite(
                (new Suite('export_shipment'))
                    ->withContexts(
                        'sylius.behat.context.hook.doctrine_orm',
                        'sylius.behat.context.transform.address',
                        'sylius.behat.context.transform.payment',
                        'sylius.behat.context.transform.product',
                        'sylius.behat.context.transform.shared_storage',
                        'sylius.behat.context.transform.shipping_method',
                        'sylius.behat.context.setup.channel',
                        'sylius.behat.context.setup.currency',
                        'sylius.behat.context.setup.locale',
                        'sylius.behat.context.setup.order',
                        'sylius.behat.context.setup.payment',
                        'sylius.behat.context.setup.product',
                        'sylius.behat.context.setup.shipping',
                        'sylius.behat.context.setup.admin_security',
                        'theebrs_sylius.shipment_export.behat.context.ui.admin.managing_shipments_export',
                    )
                    ->withFilter(new TagFilter('@exporting_shipments && @ui')),
            ),
    );
