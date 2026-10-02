<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Formatter\PrettyFormatter;
use Behat\Config\GherkinOptions;
use Behat\Config\Profile;
use Behat\Config\TesterOptions;
use Behat\MinkExtension\ServiceContainer\MinkExtension;
use DMore\ChromeExtension\Behat\ServiceContainer\ChromeExtension;
use FriendsOfBehat\MinkDebugExtension\ServiceContainer\MinkDebugExtension;
use FriendsOfBehat\SuiteSettingsExtension\ServiceContainer\SuiteSettingsExtension;
use FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension;
use FriendsOfBehat\VariadicExtension\ServiceContainer\VariadicExtension;
use Tests\ThreeBRS\SyliusShipmentExportPlugin\Kernel;

return (new Config())
    ->import('tests/Behat/Resources/config/suites.php')
    ->withProfile(
        (new Profile('default'))
            ->withTesterOptions((new TesterOptions())->withErrorReporting(\E_ALL ^ \E_DEPRECATED))
            ->withFormatter(new PrettyFormatter(paths: false))
            // CLI is excluded as it registers an error handler that mutes fatal errors
            ->withGherkinOptions((new GherkinOptions())->withFilter(new TagFilter('~@todo&&~@cli')))
            ->withExtension(new Extension(ChromeExtension::class))
            ->withExtension(new Extension(MinkDebugExtension::class, [
                'directory' => 'tests/Application/var/log',
                'clean_start' => false,
                'screenshot' => true,
            ]))
            ->withExtension(new Extension(MinkExtension::class, [
                'files_path' => '%paths.base%/vendor/sylius/sylius/src/Sylius/Behat/Resources/fixtures/',
                'base_url' => 'http://localhost/',
                'default_session' => 'symfony',
                'sessions' => [
                    'symfony' => [
                        'symfony' => null,
                    ],
                ],
                'show_auto' => false,
            ]))
            ->withExtension(new Extension(SymfonyExtension::class, [
                'bootstrap' => 'tests/Application/config/bootstrap.php',
                'kernel' => [
                    'class' => Kernel::class,
                ],
            ]))
            ->withExtension(new Extension(VariadicExtension::class))
            ->withExtension(new Extension(SuiteSettingsExtension::class, [
                'paths' => ['features'],
            ])),
    );
