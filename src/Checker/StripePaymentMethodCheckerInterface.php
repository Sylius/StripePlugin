<?php

declare(strict_types=1);

namespace FluxSE\SyliusStripePlugin\Checker;

interface StripePaymentMethodCheckerInterface
{
    public function hasStripePaymentMethod(): bool;
}
