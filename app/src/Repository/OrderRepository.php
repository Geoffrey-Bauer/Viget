<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function getTotalRevenue(): float
    {
        $qb = $this->createQueryBuilder('o')
            ->select('SUM(o.totalAmount)') // Assurez-vous que 'totalAmount' est le bon champ dans votre entité Order
            ->where('o.status IN (:statuses)') // Filtre par les statuts spécifiés
            ->setParameter('statuses', ['Payé', 'Livré']); // Remplacez par les valeurs exactes de vos statuts

        return (float) $qb->getQuery()->getSingleScalarResult();
    }
}
