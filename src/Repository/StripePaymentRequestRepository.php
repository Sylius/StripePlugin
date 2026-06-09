<?php

declare(strict_types=1);

namespace FluxSE\SyliusStripePlugin\Repository;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Sylius\Bundle\PaymentBundle\Doctrine\ORM\PaymentRequestRepository;
use Sylius\Component\Payment\Model\PaymentRequestInterface;

class StripePaymentRequestRepository extends PaymentRequestRepository implements StripePaymentRequestRepositoryInterface
{
    public function findOneByStripeObjectId(string $stripeObjectId): ?PaymentRequestInterface
    {
        $em = $this->getEntityManager();
        $prMeta = $em->getClassMetadata($this->getEntityName());
        $pMeta = $em->getClassMetadata($prMeta->getAssociationTargetClass('payment'));

        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata($this->getEntityName(), 'pr');

        /** @var PaymentRequestInterface|null $result */
        $result = $em->createNativeQuery(
            sprintf(
                'SELECT pr.* FROM %s pr
                 INNER JOIN %s p ON pr.payment_id = p.id
                 WHERE JSON_VALUE(p.details, \'$.id\') = :id
                    OR JSON_VALUE(p.details, \'$.payment_intent\') = :id
                    OR JSON_VALUE(p.details, \'$.payment_intent.id\') = :id
                 LIMIT 1',
                $prMeta->getTableName(),
                $pMeta->getTableName(),
            ),
            $rsm,
        )
            ->setParameter('id', $stripeObjectId)
            ->getOneOrNullResult();

        return $result;
    }
}
