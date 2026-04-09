<?php
namespace App\Repository;

use App\Entity\Card;
use App\Entity\User;
use App\Entity\UserCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserCard>
 */
class UserCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCard::class);
    }

    public function findOwnedCardsBySet($user, $set)
    {
        return $this->createQueryBuilder('uc')
            ->join('uc.card', 'c')
            ->where('uc.user = :user')
            ->andWhere('c.set = :set')
            ->setParameter('user', $user)
            ->setParameter('set', $set)
            ->getQuery()
            ->getResult();
    }

    public function findOwnedCardsByQuery($user, $query)
    {
        return $this->createQueryBuilder('uc')
            ->join('uc.card', 'c')
            ->where('uc.user = :user')
            ->andWhere('(LOWER(c.name) LIKE :query OR LOWER(c.illustrator) LIKE :query)')
            ->setParameter('user', $user)
            ->setParameter('query', '%' . strtolower($query) . '%')
            ->getQuery()
            ->getResult();
    }

}
