<?php

declare(strict_types=1);

namespace FluxSE\SyliusStripePlugin\Provider;

use ArrayAccess;
use FluxSE\SyliusStripePlugin\Stripe\Resolver\EventResolverInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeObject;
use Sylius\Bundle\PaymentBundle\Provider\NotifyPaymentProviderInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Sylius\Component\Payment\Repository\PaymentRequestRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class StripeNotifyPaymentProvider implements NotifyPaymentProviderInterface
{
    /**
     * @param string[] $supportedFactories
     * @param PaymentRequestRepositoryInterface<PaymentRequestInterface> $paymentRequestRepository
     */
    public function __construct(
        private array $supportedFactories,
        private PaymentRequestRepositoryInterface $paymentRequestRepository,
        private EventResolverInterface $eventResolver,
        private RefundEventTokenHashResolverInterface $refundEventTokenHashResolver,
    ) {
    }

    public function getPayment(Request $request, PaymentMethodInterface $paymentMethod): PaymentInterface
    {
        try {
            $event = $this->eventResolver->resolve($request, $paymentMethod);
        } catch (SignatureVerificationException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

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

        $hash = $this->resolveTokenHash($object, $paymentMethod);
        if (!is_string($hash)) {
            throw new \LogicException(sprintf(
                'Unable to resolve the token hash (key: "%s") for this Stripe event (ID:"%s").',
                MetadataProviderInterface::DEFAULT_TOKEN_HASH_KEY_NAME,
                $event->id,
            ));
        }

        $paymentRequest = $this->paymentRequestRepository->findOneBy([
            'hash' => $hash,
        ]);
        if (null === $paymentRequest) {
            throw new \LogicException(sprintf(
                'Unable to retrieve the payment request (hash:%s) related to this Stripe event (ID:"%s").',
                $hash,
                $event->id,
            ));
        }

        return $paymentRequest->getPayment();
    }

    /**
     * Reads the token hash from the event object metadata. Refund events (e.g. "charge.refunded")
     * carry a Charge whose metadata has no token hash, so we fall back to the related PaymentIntent.
     */
    private function resolveTokenHash(StripeObject $object, PaymentMethodInterface $paymentMethod): ?string
    {
        /** @var ArrayAccess<string, string>|null $metadata */
        $metadata = $object->offsetGet('metadata');
        if ($metadata instanceof ArrayAccess) {
            $hash = $metadata->offsetGet(MetadataProviderInterface::DEFAULT_TOKEN_HASH_KEY_NAME);
            if (is_string($hash)) {
                return $hash;
            }
        }

        return $this->refundEventTokenHashResolver->resolve($object, $paymentMethod);
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
