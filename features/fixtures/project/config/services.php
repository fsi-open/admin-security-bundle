<?php

declare(strict_types=1);

use FSi\Bundle\AdminSecurityBundle\Behat\Context\DataContext;
use FSi\FixturesBundle\Admin\News;
use FSi\FixturesBundle\Admin\PageSettings;
use FSi\FixturesBundle\Listener\MailCollector;
use FSi\FixturesBundle\Time\Clock;
use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services->defaults()
            ->autowire()
            ->autoconfigure()
            ->private()
            ->bind('$session', service('behat.mink.default_session'))
            ->bind('$minkParameters', service('behat.mink.parameters'));

    $services->load(
            'FSi\\Bundle\\AdminSecurityBundle\\Behat\\Context\\',
            '../../../../Behat/Context'
    );

    $services->set(DataContext::class)
            ->arg('$container', service('behat.driver.service_container')->nullOnInvalid());

    $services->set(News::class);
    $services->set(PageSettings::class);
    $services->set(MailCollector::class);

    $services->set(Clock::class);
    $services->alias(ClockInterface::class, Clock::class)->public();
};
