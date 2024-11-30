<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function findTicketWithPurchase(int $id): ?Ticket
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.purchase', 'o') // Utilisez 'purchase' ici
            ->addSelect('o')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
