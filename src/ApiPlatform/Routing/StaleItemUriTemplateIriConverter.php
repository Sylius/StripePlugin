<?php

declare(strict_types=1);

namespace FluxSE\SyliusStripePlugin\ApiPlatform\Routing;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Operation\Factory\OperationMetadataFactoryInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;

final readonly class StaleItemUriTemplateIriConverter implements IriConverterInterface
{
    public function __construct(
        private IriConverterInterface $decoratedIriConverter,
        private OperationMetadataFactoryInterface $operationMetadataFactory,
    ) {
    }

    public function getResourceFromIri(string $iri, array $context = [], ?Operation $operation = null): object
    {
        return $this->decoratedIriConverter->getResourceFromIri($iri, $context, $operation);
    }

    public function getIriFromResource(
        object|string $resource,
        int $referenceType = UrlGeneratorInterface::ABS_PATH,
        ?Operation $operation = null,
        array $context = [],
    ): ?string {
        $itemUriTemplate = $context['item_uri_template'] ?? null;

        if (is_object($resource) && is_string($itemUriTemplate)) {
            $itemOperation = $this->operationMetadataFactory->create($itemUriTemplate);
            $itemOperationClass = $itemOperation?->getClass();

            if (null !== $itemOperationClass && !is_a($resource, $itemOperationClass)) {
                unset($context['item_uri_template']);
            }
        }

        return $this->decoratedIriConverter->getIriFromResource($resource, $referenceType, $operation, $context);
    }
}
