<?php

declare(strict_types=1);

use FluxSE\SyliusStripePlugin\ApiPlatform\Metadata\PaymentRequestRelationPropertyMetadataFactory;
use FluxSE\SyliusStripePlugin\ApiPlatform\Routing\StaleItemUriTemplateIriConverter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('flux_se.sylius_stripe.api_platform.routing.iri_converter', StaleItemUriTemplateIriConverter::class)
        ->decorate('api_platform.symfony.iri_converter', null, 48)
        ->args([
            service('.inner'),
            service('api_platform.metadata.operation.metadata_factory'),
        ])
    ;

    $services
        ->set('flux_se.sylius_stripe.api_platform.property_metadata_factory', PaymentRequestRelationPropertyMetadataFactory::class)
        ->decorate('api_platform.metadata.property.metadata_factory', null, -20)
        ->args([
            service('.inner'),
            param('sylius.model.payment_request.class'),
            param('sylius.model.payment.class'),
            param('sylius.model.payment_method.class'),
        ])
    ;
};
