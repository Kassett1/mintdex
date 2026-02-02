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

    public function countByUserAndCard(User $user, Card $card): int
    {
        return (int) $this->createQueryBuilder('uc')
            ->select('COUNT(uc.id)')
            ->andWhere('uc.user = :user')
            ->andWhere('uc.card = :card')
            ->setParameter('user', $user)
            ->setParameter('card', $card)
            ->getQuery()
            ->getSingleScalarResult();
    }

}
