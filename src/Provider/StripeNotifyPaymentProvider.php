<?php

declare(strict_types=1);

namespace FluxSE\SyliusStripePlugin\Provider;

use ArrayAccess;
use FluxSE\SyliusStripePlugin\Repository\StripePaymentRequestRepositoryInterface;
use FluxSE\SyliusStripePlugin\Stripe\Resolver\EventResolverInterface;
use Stripe\StripeObject;
use Sylius\Bundle\PaymentBundle\Provider\NotifyPaymentProviderInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class StripeNotifyPaymentProvider implements NotifyPaymentProviderInterface
{
    /**
     * @param string[] $supportedFactories
     */
    public function __construct(
        private array $supportedFactories,
        private StripePaymentRequestRepositoryInterface $paymentRequestRepository,
        private EventResolverInterface $eventResolver,
    ) {
    }

    public function getPayment(Request $request, PaymentMethodInterface $paymentMethod): PaymentInterface
    {
        $event = $this->eventResolver->resolve($request, $paymentMethod);

        /** @var StripeObject|null $stripeObject */
        $stripeObject = $event->offsetGet('data');
        if (false === $stripeObject instanceof StripeObject) {
            throw new \LogicException('The Stripe event is not a StripeObject.');
        }

        /** @var StripeObject|null $object */
        $object = $stripeObject->offsetGet('object');
        if (false === $object instanceof StripeObject) {
            throw new \LogicException('The Stripe event data object is not a StripeObject.');
        }

        /** @var ArrayAccess<string, string>|null $metadata */
        $metadata = $object->offsetGet('metadata');
        if (false === $metadata instanceof ArrayAccess) {
            throw new \LogicException('The Stripe event metadata is not an \ArrayAccess.');
        }

        $hash = $metadata->offsetGet(MetadataProviderInterface::DEFAULT_TOKEN_HASH_KEY_NAME);
        if (is_string($hash)) {
            $paymentRequest = $this->paymentRequestRepository->findOneBy(['hash' => $hash]);
            if (null === $paymentRequest) {
                throw new \LogicException(sprintf(
                    'Unable to retrieve the payment request (hash:%s) related to this Stripe event (ID:"%s").',
                    $hash,
                    $event->id,
                ));
            }

            return $paymentRequest->getPayment();
        }

        $stripeObjectId = $object->offsetGet('id');
        if (!is_string($stripeObjectId)) {
            throw new \LogicException(sprintf(
                'The Stripe event object metadata (key: "%s") must be a string.',
                MetadataProviderInterface::DEFAULT_TOKEN_HASH_KEY_NAME,
            ));
        }

        $paymentRequest = $this->paymentRequestRepository->findOneByStripeObjectId($stripeObjectId);
        if (null === $paymentRequest) {
            throw new \LogicException(sprintf(
                'Unable to retrieve the payment request for Stripe event object (ID:"%s").',
                $stripeObjectId,
            ));
        }

        return $paymentRequest->getPayment();
    }

    public function supports(Request $request, PaymentMethodInterface $paymentMethod): bool
    {
        return in_array(
            $paymentMethod->getGatewayConfig()?->getFactoryName(),
            $this->supportedFactories,
            true,
        );
    }
}
