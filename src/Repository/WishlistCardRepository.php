<?php
namespace App\Repository;

use App\Entity\WishlistCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WishlistCard>
 */
class WishlistCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WishlistCard::class);
    }

    public function findWishlistCardsBySet($user, $set)
    {
        return $this->createQueryBuilder('wc')
            ->join('wc.card', 'c')
            ->where('wc.user = :user')
            ->andWhere('c.set = :set')
            ->setParameter('user', $user)
            ->setParameter('set', $set)
            ->getQuery()
            ->getResult();
    }

    public function findWishlistCardsByQuery($user, $query)
    {
        return $this->createQueryBuilder('wc')
            ->join('wc.card', 'c')
            ->where('wc.user = :user')
            ->andWhere('(LOWER(c.name) LIKE :query OR LOWER(c.illustrator) LIKE :query)')
            ->setParameter('user', $user)
            ->setParameter('query', '%' . strtolower($query) . '%')
            ->getQuery()
            ->getResult();
    }
}
