<?php

declare(strict_types=1);

namespace FluxSE\SyliusStripePlugin\Repository;

use Sylius\Component\Payment\Model\PaymentRequestInterface;

interface StripePaymentRequestRepositoryInterface
{
    public function findOneByStripeObjectId(string $stripeObjectId): ?PaymentRequestInterface;
}
