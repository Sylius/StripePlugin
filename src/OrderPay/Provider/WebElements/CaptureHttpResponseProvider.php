<?php

declare(strict_types=1);

namespace FluxSE\SyliusStripePlugin\OrderPay\Provider\WebElements;

use FluxSE\SyliusStripePlugin\Appearance\AppearanceBuilder;
use FluxSE\SyliusStripePlugin\Provider\AfterUrlProviderInterface;
use Stripe\PaymentIntent;
use Sylius\Bundle\PaymentBundle\Provider\HttpResponseProviderInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final readonly class CaptureHttpResponseProvider implements HttpResponseProviderInterface
{
    public function __construct(
        private AfterUrlProviderInterface $afterUrlProvider,
        private Environment $twig,
        private AppearanceBuilder $appearanceBuilder,
    ) {
    }

    // Sylius 2.2 passes a RequestConfiguration here, 2.3 a Request; neither is used.
    public function supports(
        Request|RequestConfiguration $request,
        PaymentRequestInterface $paymentRequest,
    ): bool {
        return $paymentRequest->getState() === PaymentRequestInterface::STATE_PROCESSING;
    }

    public function getResponse(
        Request|RequestConfiguration $request,
        PaymentRequestInterface $paymentRequest,
    ): Response {
        $data = $paymentRequest->getResponseData();

        /** @var string|null $publishableKey */
        $publishableKey = $data['publishable_key'] ?? 'null';
        if (null === $publishableKey) {
            throw new \LogicException('The publishable key must be defined!');
        }

        /** @var array<string, mixed> $appearanceConfig */
        $appearanceConfig = $paymentRequest->getMethod()->getGatewayConfig()?->getConfig()['stripe_appearance'] ?? [];
        $appearance = $this->appearanceBuilder->build($appearanceConfig);

        return new Response(
            $this->twig->render(
                '@FluxSESyliusStripePlugin/shop/order_pay/web_elements/capture.html.twig',
                [
                    'publishable_key' => $publishableKey,
                    'model' => PaymentIntent::constructFrom($paymentRequest->getPayment()->getDetails()),
                    'action_url' => $this->afterUrlProvider->getUrl($paymentRequest, AfterUrlProviderInterface::ACTION_URL),
                    'appearance' => $appearance,
                ],
            ),
        );
    }
}
