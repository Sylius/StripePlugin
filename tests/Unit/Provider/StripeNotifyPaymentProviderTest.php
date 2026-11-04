<?php

declare(strict_types=1);

namespace Tests\FluxSE\SyliusStripePlugin\Unit\Provider;

use FluxSE\SyliusStripePlugin\Provider\RefundEventTokenHashResolverInterface;
use FluxSE\SyliusStripePlugin\Provider\StripeNotifyPaymentProvider;
use FluxSE\SyliusStripePlugin\Stripe\Resolver\EventResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Stripe\Charge;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Sylius\Component\Payment\Repository\PaymentRequestRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AllowMockObjectsWithoutExpectations]
final class StripeNotifyPaymentProviderTest extends TestCase
{
    private EventResolverInterface&MockObject $eventResolver;

    /** @var PaymentRequestRepositoryInterface<PaymentRequestInterface>&MockObject */
    private PaymentRequestRepositoryInterface&MockObject $paymentRequestRepository;

    private RefundEventTokenHashResolverInterface&MockObject $refundEventTokenHashResolver;

    private PaymentMethodInterface&MockObject $paymentMethod;

    private StripeNotifyPaymentProvider $provider;

    protected function setUp(): void
    {
        $this->eventResolver = $this->createMock(EventResolverInterface::class);
        $this->paymentRequestRepository = $this->createMock(PaymentRequestRepositoryInterface::class);
        $this->refundEventTokenHashResolver = $this->createMock(RefundEventTokenHashResolverInterface::class);
        $this->paymentMethod = $this->createMock(PaymentMethodInterface::class);

        $this->provider = new StripeNotifyPaymentProvider(
            ['stripe_web_elements', 'stripe_checkout'],
            $this->paymentRequestRepository,
            $this->eventResolver,
            $this->refundEventTokenHashResolver,
        );
    }

    public function test_it_resolves_the_payment_from_the_object_metadata_token_hash(): void
    {
        $event = Event::constructFrom([
            'id' => 'evt_test_1',
            'object' => Event::OBJECT_NAME,
            'type' => Event::PAYMENT_INTENT_SUCCEEDED,
            'data' => ['object' => [
                'id' => 'pi_test_1',
                'object' => PaymentIntent::OBJECT_NAME,
                'metadata' => ['token_hash' => 'the-hash'],
            ]],
        ]);
        $this->eventResolver->method('resolve')->willReturn($event);

        $payment = $this->createMock(PaymentInterface::class);
        $this->paymentRequestRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['hash' => 'the-hash'])
            ->willReturn($this->paymentRequest($payment));

        // The fallback is not used when the object already carries the token hash.
        $this->refundEventTokenHashResolver->expects(self::never())->method('resolve');

        self::assertSame($payment, $this->provider->getPayment(new Request(), $this->paymentMethod));
    }

    public function test_it_falls_back_to_the_refund_resolver_when_the_object_has_no_token_hash(): void
    {
        $event = Event::constructFrom([
            'id' => 'evt_test_1',
            'object' => Event::OBJECT_NAME,
            'type' => Event::CHARGE_REFUNDED,
            'data' => ['object' => [
                'id' => 'ch_test_1',
                'object' => Charge::OBJECT_NAME,
                'payment_intent' => 'pi_test_1',
            ]],
        ]);
        $this->eventResolver->method('resolve')->willReturn($event);

        $this->refundEventTokenHashResolver
            ->expects(self::once())
            ->method('resolve')
            ->willReturn('the-hash');

        $payment = $this->createMock(PaymentInterface::class);
        $this->paymentRequestRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['hash' => 'the-hash'])
            ->willReturn($this->paymentRequest($payment));

        self::assertSame($payment, $this->provider->getPayment(new Request(), $this->paymentMethod));
    }

    public function test_it_throws_when_no_token_hash_can_be_resolved(): void
    {
        $event = Event::constructFrom([
            'id' => 'evt_test_1',
            'object' => Event::OBJECT_NAME,
            'type' => Event::CHARGE_REFUNDED,
            'data' => ['object' => [
                'id' => 'ch_test_1',
                'object' => Charge::OBJECT_NAME,
            ]],
        ]);
        $this->eventResolver->method('resolve')->willReturn($event);
        $this->refundEventTokenHashResolver->method('resolve')->willReturn(null);

        $this->expectException(\LogicException::class);

        $this->provider->getPayment(new Request(), $this->paymentMethod);
    }

    public function test_it_answers_bad_request_when_the_signature_verification_fails(): void
    {
        $signatureVerificationException = SignatureVerificationException::factory('Unable to check Stripe Signature using several webhook keys:');
        $this->eventResolver->method('resolve')->willThrowException($signatureVerificationException);

        try {
            $this->provider->getPayment(new Request(), $this->paymentMethod);
        } catch (BadRequestHttpException $exception) {
            self::assertSame($signatureVerificationException->getMessage(), $exception->getMessage());
            self::assertSame($signatureVerificationException, $exception->getPrevious());

            return;
        }

        self::fail(sprintf('Expected %s to be thrown.', BadRequestHttpException::class));
    }

    private function paymentRequest(PaymentInterface $payment): PaymentRequestInterface&MockObject
    {
        $paymentRequest = $this->createMock(PaymentRequestInterface::class);
        $paymentRequest->method('getPayment')->willReturn($payment);

        return $paymentRequest;
    }
}
