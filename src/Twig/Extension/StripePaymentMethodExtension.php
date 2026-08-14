<?php

declare(strict_types=1);

namespace FluxSE\SyliusStripePlugin\Twig\Extension;

use FluxSE\SyliusStripePlugin\Checker\StripePaymentMethodCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class StripePaymentMethodExtension extends AbstractExtension
{
    public function __construct(
        private readonly StripePaymentMethodCheckerInterface $stripePaymentMethodChecker,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'sylius_stripe_has_payment_method',
                $this->stripePaymentMethodChecker->hasStripePaymentMethod(...),
            ),
        ];
    }
}
