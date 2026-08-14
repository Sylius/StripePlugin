<?php

declare(strict_types=1);

use FluxSE\SyliusStripePlugin\Checker\StripePaymentMethodChecker;
use FluxSE\SyliusStripePlugin\Checker\StripePaymentMethodCheckerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('flux_se.sylius_stripe.checker.stripe_payment_method', StripePaymentMethodChecker::class)
        ->args([
            service('sylius.repository.payment_method'),
            param('flux_se.sylius_stripe.factories'),
        ]);
    $services->alias(StripePaymentMethodCheckerInterface::class, 'flux_se.sylius_stripe.checker.stripe_payment_method');
};
