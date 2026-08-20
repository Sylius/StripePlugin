<?php

declare(strict_types=1);

namespace FluxSE\SyliusStripePlugin\ApiPlatform\Metadata;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Symfony\Component\TypeInfo\Type;

final readonly class PaymentRequestRelationPropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    /** @var array<string, class-string> */
    private array $resourceClassByProperty;

    /**
     * @param class-string $paymentRequestResourceClass
     * @param class-string $paymentResourceClass
     * @param class-string $paymentMethodResourceClass
     */
    public function __construct(
        private PropertyMetadataFactoryInterface $decorated,
        private string $paymentRequestResourceClass,
        string $paymentResourceClass,
        string $paymentMethodResourceClass,
    ) {
        $this->resourceClassByProperty = [
            'payment' => $paymentResourceClass,
            'method' => $paymentMethodResourceClass,
        ];
    }

    public function create(string $resourceClass, string $property, array $options = []): ApiProperty
    {
        $propertyMetadata = $this->decorated->create($resourceClass, $property, $options);

        $targetClass = $this->resourceClassByProperty[$property] ?? null;

        if (null === $targetClass || !is_a($resourceClass, $this->paymentRequestResourceClass, true)) {
            return $propertyMetadata;
        }

        $nativeType = $propertyMetadata->getNativeType();

        if (null === $nativeType) {
            return $propertyMetadata;
        }

        $resolvedType = Type::object($targetClass);

        return $propertyMetadata->withNativeType($nativeType->isNullable() ? Type::nullable($resolvedType) : $resolvedType);
    }
}
