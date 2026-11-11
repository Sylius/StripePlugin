<?php

declare(strict_types=1);

namespace Tests\FluxSE\SyliusStripePlugin\Unit\Twig\Component\WebElements;

use FluxSE\SyliusStripePlugin\Exception\WebElementsSummaryException;
use FluxSE\SyliusStripePlugin\Twig\Component\WebElements\SummaryPaymentComponent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Sylius\Component\Payment\Repository\PaymentRequestRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class SummaryPaymentComponentTest extends TestCase
{
    /** @var PaymentRequestRepositoryInterface<PaymentRequestInterface>&MockObject */
    private PaymentRequestRepositoryInterface&MockObject $paymentRequestRepository;

    private RequestStack $requestStack;

    protected function setUp(): void
    {
        $this->paymentRequestRepository = $this->createMock(PaymentRequestRepositoryInterface::class);
        $this->requestStack = new RequestStack();
    }

    public function test_it_loads_the_order_from_the_hash_route_attribute(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getOrder')->willReturn($order);
        $paymentRequest = $this->createMock(PaymentRequestInterface::class);
        $paymentRequest->method('getPayment')->willReturn($payment);

        $this->paymentRequestRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['hash' => 'abc123'])
            ->willReturn($paymentRequest)
        ;

        // The hash is a path parameter of the pay route, so it lives in the request attributes.
        $this->requestStack->push(new Request(attributes: ['hash' => 'abc123']));

        $component = $this->createComponent();
        $component->mount();

        self::assertSame($order, $component->order);
    }

    public function test_it_fails_when_the_request_carries_no_hash(): void
    {
        $this->paymentRequestRepository->expects(self::never())->method('findOneBy');
        $this->requestStack->push(new Request());

        $this->expectException(WebElementsSummaryException::class);
        $this->expectExceptionMessage('Token hash is required to load the payment summary component.');

        $this->createComponent()->mount();
    }

    public function test_it_fails_when_there_is_no_active_request(): void
    {
        $this->expectException(WebElementsSummaryException::class);
        $this->expectExceptionMessage('No active request found.');

        $this->createComponent()->mount();
    }

    private function createComponent(): SummaryPaymentComponent
    {
        return new SummaryPaymentComponent($this->requestStack, $this->paymentRequestRepository);
    }
}
