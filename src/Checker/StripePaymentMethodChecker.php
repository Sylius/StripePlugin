<?php

declare(strict_types=1);

namespace FluxSE\SyliusStripePlugin\Checker;

use Doctrine\ORM\EntityRepository;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Repository\PaymentMethodRepositoryInterface;

final readonly class StripePaymentMethodChecker implements StripePaymentMethodCheckerInterface
{
    /**
     * @param PaymentMethodRepositoryInterface<PaymentMethodInterface>&EntityRepository<PaymentMethodInterface> $paymentMethodRepository
     * @param list<string> $stripeFactoryNames
     */
    public function __construct(
        private PaymentMethodRepositoryInterface $paymentMethodRepository,
        private array $stripeFactoryNames,
    ) {
    }

    public function hasStripePaymentMethod(): bool
    {
        $count = (int) $this->paymentMethodRepository->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->innerJoin('o.gatewayConfig', 'gatewayConfig')
            ->andWhere('gatewayConfig.factoryName IN (:factoryNames)')
            ->setParameter('factoryNames', $this->stripeFactoryNames)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $count > 0;
    }
}
