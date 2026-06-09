<?php

declare(strict_types=1);

namespace Tests\FluxSE\SyliusStripePlugin\Unit\Provider\Transition\Checkout;

use FluxSE\SyliusStripePlugin\Provider\Transition\Checkout\PaymentModeTransitionProvider;
use FluxSE\SyliusStripePlugin\Provider\Transition\Checkout\SessionTransitionProvider;
use FluxSE\SyliusStripePlugin\Provider\Transition\WebElements\PaymentIntentTransitionProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stripe\Charge;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

final class SessionTransitionProviderTest extends TestCase
{
    private SessionTransitionProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new SessionTransitionProvider(
            new PaymentModeTransitionProvider(new PaymentIntentTransitionProvider()),
        );
    }

    /**
     * @return iterable<string, array{string, string, string, array<string, mixed>|null, bool}>
     */
    public static function cancelDataProvider(): iterable
    {
        yield 'complete session, PI canceled → cancel' => [
            Session::STATUS_COMPLETE, Session::PAYMENT_STATUS_UNPAID, PaymentIntent::STATUS_CANCELED, null, true,
        ];

        yield 'complete session, PI requires_payment_method with last_payment_error → cancel' => [
            Session::STATUS_COMPLETE, Session::PAYMENT_STATUS_UNPAID, PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD, ['code' => 'payment_method_provider_decline'], true,
        ];

        yield 'complete session, PI succeeded → no cancel' => [
            Session::STATUS_COMPLETE, Session::PAYMENT_STATUS_PAID, PaymentIntent::STATUS_SUCCEEDED, null, false,
        ];

        yield 'complete session, PI requires_capture → no cancel' => [
            Session::STATUS_COMPLETE, Session::PAYMENT_STATUS_UNPAID, PaymentIntent::STATUS_REQUIRES_CAPTURE, null, false,
        ];

        yield 'complete session, PI processing → no cancel' => [
            Session::STATUS_COMPLETE, Session::PAYMENT_STATUS_UNPAID, PaymentIntent::STATUS_PROCESSING, null, false,
        ];

        yield 'complete session, PI requires_payment_method without error → no cancel' => [
            Session::STATUS_COMPLETE, Session::PAYMENT_STATUS_UNPAID, PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD, null, false,
        ];

        yield 'expired session → no cancel' => [
            Session::STATUS_EXPIRED, Session::PAYMENT_STATUS_UNPAID, PaymentIntent::STATUS_CANCELED, null, false,
        ];

        yield 'open session → no cancel' => [
            Session::STATUS_OPEN, Session::PAYMENT_STATUS_UNPAID, PaymentIntent::STATUS_CANCELED, null, false,
        ];
    }

    /**
     * @param array<string, mixed>|null $lastPaymentError
     */
    #[DataProvider('cancelDataProvider')]
    public function test_is_cancel(
        string $sessionStatus,
        string $sessionPaymentStatus,
        string $paymentIntentStatus,
        ?array $lastPaymentError,
        bool $expected,
    ): void {
        $session = $this->createSession($sessionStatus, $sessionPaymentStatus, $paymentIntentStatus, $lastPaymentError);

        self::assertSame($expected, $this->provider->isCancel($session));
    }

    /**
     * @return iterable<string, array{string, string, bool}>
     */
    public static function authorizeDataProvider(): iterable
    {
        yield 'complete, requires_capture → authorize' => [Session::STATUS_COMPLETE, PaymentIntent::STATUS_REQUIRES_CAPTURE, true];
        yield 'complete, succeeded → no authorize' => [Session::STATUS_COMPLETE, PaymentIntent::STATUS_SUCCEEDED, false];
        yield 'complete, canceled → no authorize' => [Session::STATUS_COMPLETE, PaymentIntent::STATUS_CANCELED, false];
        yield 'expired → no authorize' => [Session::STATUS_EXPIRED, PaymentIntent::STATUS_REQUIRES_CAPTURE, false];
        yield 'open → no authorize' => [Session::STATUS_OPEN, PaymentIntent::STATUS_REQUIRES_CAPTURE, false];
    }

    #[DataProvider('authorizeDataProvider')]
    public function test_is_authorize(string $sessionStatus, string $paymentIntentStatus, bool $expected): void
    {
        $session = $this->createSession($sessionStatus, Session::PAYMENT_STATUS_UNPAID, $paymentIntentStatus);

        self::assertSame($expected, $this->provider->isAuthorize($session));
    }

    /**
     * @return iterable<string, array{string, string, string, bool}>
     */
    public static function completeDataProvider(): iterable
    {
        yield 'complete, succeeded, paid → complete' => [Session::STATUS_COMPLETE, Session::PAYMENT_STATUS_PAID, PaymentIntent::STATUS_SUCCEEDED, true];
        yield 'complete, succeeded, unpaid → no complete' => [Session::STATUS_COMPLETE, Session::PAYMENT_STATUS_UNPAID, PaymentIntent::STATUS_SUCCEEDED, false];
        yield 'complete, requires_capture, paid → no complete' => [Session::STATUS_COMPLETE, Session::PAYMENT_STATUS_PAID, PaymentIntent::STATUS_REQUIRES_CAPTURE, false];
        yield 'expired → no complete' => [Session::STATUS_EXPIRED, Session::PAYMENT_STATUS_PAID, PaymentIntent::STATUS_SUCCEEDED, false];
    }

    #[DataProvider('completeDataProvider')]
    public function test_is_complete(
        string $sessionStatus,
        string $sessionPaymentStatus,
        string $paymentIntentStatus,
        bool $expected,
    ): void {
        $session = $this->createSession($sessionStatus, $sessionPaymentStatus, $paymentIntentStatus);

        self::assertSame($expected, $this->provider->isComplete($session));
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function failDataProvider(): iterable
    {
        yield 'expired → fail' => [Session::STATUS_EXPIRED, true];
        yield 'complete → no fail' => [Session::STATUS_COMPLETE, false];
        yield 'open → no fail' => [Session::STATUS_OPEN, false];
    }

    #[DataProvider('failDataProvider')]
    public function test_is_fail(string $sessionStatus, bool $expected): void
    {
        $session = $this->createSession($sessionStatus, Session::PAYMENT_STATUS_UNPAID, PaymentIntent::STATUS_CANCELED);

        self::assertSame($expected, $this->provider->isFail($session));
    }

    /**
     * @return iterable<string, array{string, string, string, bool}>
     */
    public static function processDataProvider(): iterable
    {
        yield 'complete, processing, unpaid → process' => [Session::STATUS_COMPLETE, Session::PAYMENT_STATUS_UNPAID, PaymentIntent::STATUS_PROCESSING, true];
        yield 'complete, processing, paid → no process' => [Session::STATUS_COMPLETE, Session::PAYMENT_STATUS_PAID, PaymentIntent::STATUS_PROCESSING, false];
        yield 'complete, succeeded, unpaid → no process' => [Session::STATUS_COMPLETE, Session::PAYMENT_STATUS_UNPAID, PaymentIntent::STATUS_SUCCEEDED, false];
        yield 'expired → no process' => [Session::STATUS_EXPIRED, Session::PAYMENT_STATUS_UNPAID, PaymentIntent::STATUS_PROCESSING, false];
    }

    #[DataProvider('processDataProvider')]
    public function test_is_process(
        string $sessionStatus,
        string $sessionPaymentStatus,
        string $paymentIntentStatus,
        bool $expected,
    ): void {
        $session = $this->createSession($sessionStatus, $sessionPaymentStatus, $paymentIntentStatus);

        self::assertSame($expected, $this->provider->isProcess($session));
    }

    /**
     * @param array<string, mixed>|null $lastPaymentError
     */
    private function createSession(
        string $sessionStatus,
        string $sessionPaymentStatus,
        string $paymentIntentStatus,
        ?array $lastPaymentError = null,
        bool $chargeRefunded = false,
    ): Session {
        $paymentIntentData = [
            'id' => 'pi_test_1',
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => $paymentIntentStatus,
        ];

        $paymentIntentData['last_payment_error'] = $lastPaymentError;

        if ($chargeRefunded || $paymentIntentStatus === PaymentIntent::STATUS_SUCCEEDED) {
            $paymentIntentData['latest_charge'] = [
                'id' => 'ch_test_1',
                'object' => Charge::OBJECT_NAME,
                'refunded' => $chargeRefunded,
            ];
        }

        return Session::constructFrom([
            'id' => 'cs_test_1',
            'object' => Session::OBJECT_NAME,
            'mode' => Session::MODE_PAYMENT,
            'status' => $sessionStatus,
            'payment_status' => $sessionPaymentStatus,
            'payment_intent' => $paymentIntentData,
        ]);
    }
}
