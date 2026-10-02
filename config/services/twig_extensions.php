<?php

declare(strict_types=1);

use FluxSE\SyliusStripePlugin\Checker\StripePaymentMethodCheckerInterface;
use FluxSE\SyliusStripePlugin\ExpressCheckout\ExpressCheckoutAvailabilityCheckerInterface;
use FluxSE\SyliusStripePlugin\Stripe\SecretKey\LegacyKeyDetectorInterface;
use FluxSE\SyliusStripePlugin\Stripe\SecretKey\LegacyStripePaymentMethodsProviderInterface;
use FluxSE\SyliusStripePlugin\Twig\Extension\ExpressCheckoutExtension;
use FluxSE\SyliusStripePlugin\Twig\Extension\LegacyStripeKeyExtension;
use FluxSE\SyliusStripePlugin\Twig\Extension\StripePaymentMethodExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('flux_se.sylius_stripe.twig.extension.legacy_stripe_key', LegacyStripeKeyExtension::class)
        ->args([
            service(LegacyKeyDetectorInterface::class),
            service(LegacyStripePaymentMethodsProviderInterface::class),
        ])
        ->tag('twig.extension');

    $services->set('flux_se.sylius_stripe.twig.extension.express_checkout', ExpressCheckoutExtension::class)
        ->args([
            service(ExpressCheckoutAvailabilityCheckerInterface::class),
        ])
        ->tag('twig.extension');

    $services->set('flux_se.sylius_stripe.twig.extension.stripe_payment_method', StripePaymentMethodExtension::class)
        ->args([
            service(StripePaymentMethodCheckerInterface::class),
        ])
        ->tag('twig.extension');
};
