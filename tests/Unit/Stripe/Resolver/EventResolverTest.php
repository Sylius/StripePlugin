<?php

declare(strict_types=1);

namespace Tests\FluxSE\SyliusStripePlugin\Unit\Stripe\Resolver;

use FluxSE\SyliusStripePlugin\Stripe\Resolver\EventResolver;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Symfony\Component\HttpFoundation\Request;

#[AllowMockObjectsWithoutExpectations]
final class EventResolverTest extends TestCase
{
    private const FIRST_WEBHOOK_SECRET_KEY = 'whsec_first_secret_key_value';

    private const SECOND_WEBHOOK_SECRET_KEY = 'whsec_second_secret_key_value';

    private const PAYLOAD = '{"id":"evt_test_1","object":"event","type":"payment_intent.succeeded","data":{"object":{"id":"pi_test_1","object":"payment_intent"}}}';

    private EventResolver $eventResolver;

    protected function setUp(): void
    {
        $this->eventResolver = new EventResolver();
    }

    public function test_it_resolves_the_event_when_the_signature_matches_one_of_the_configured_keys(): void
    {
        $request = $this->createRequest($this->signHeader(self::SECOND_WEBHOOK_SECRET_KEY));
        $paymentMethod = $this->createPaymentMethod([
            self::FIRST_WEBHOOK_SECRET_KEY,
            self::SECOND_WEBHOOK_SECRET_KEY,
        ]);

        $event = $this->eventResolver->resolve($request, $paymentMethod);

        self::assertSame('evt_test_1', $event->id);
        self::assertSame(Event::PAYMENT_INTENT_SUCCEEDED, $event->type);
    }

    public function test_it_does_not_put_the_webhook_secret_keys_in_the_exception_when_no_key_matches(): void
    {
        $request = $this->createRequest('t=1,v1=x');
        $paymentMethod = $this->createPaymentMethod([
            self::FIRST_WEBHOOK_SECRET_KEY,
            self::SECOND_WEBHOOK_SECRET_KEY,
        ]);

        try {
            $this->eventResolver->resolve($request, $paymentMethod);
        } catch (SignatureVerificationException $exception) {
            $message = $exception->getMessage();
            self::assertStringContainsString('key #1', $message);
            self::assertStringContainsString('key #2', $message);
            self::assertStringNotContainsString(self::FIRST_WEBHOOK_SECRET_KEY, $message);
            self::assertStringNotContainsString(self::SECOND_WEBHOOK_SECRET_KEY, $message);

            return;
        }

        self::fail(sprintf('Expected %s to be thrown.', SignatureVerificationException::class));
    }

    public function test_it_fails_the_signature_verification_when_no_webhook_secret_key_is_configured(): void
    {
        $request = $this->createRequest($this->signHeader(self::FIRST_WEBHOOK_SECRET_KEY));
        $paymentMethod = $this->createPaymentMethod([]);

        $this->expectException(SignatureVerificationException::class);

        $this->eventResolver->resolve($request, $paymentMethod);
    }

    private function signHeader(string $webhookSecretKey): string
    {
        $timestamp = time();
        $signature = hash_hmac('sha256', sprintf('%d.%s', $timestamp, self::PAYLOAD), $webhookSecretKey);

        return sprintf('t=%d,v1=%s', $timestamp, $signature);
    }

    private function createRequest(string $stripeSignature): Request
    {
        return Request::create(
            '/payment-methods/STRIPE_WEB_ELEMENTS',
            'POST',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => $stripeSignature,
            ],
            self::PAYLOAD,
        );
    }

    /** @param string[] $webhookSecretKeys */
    private function createPaymentMethod(array $webhookSecretKeys): PaymentMethodInterface
    {
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->method('getConfig')->willReturn([
            'publishable_key' => 'pk_test_123',
            'secret_key' => 'sk_test_123',
            'webhook_secret_keys' => $webhookSecretKeys,
        ]);

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);

        return $paymentMethod;
    }
}
