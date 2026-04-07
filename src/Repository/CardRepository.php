<?php
namespace App\Repository;

use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Card>
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    public function searchByNameOrIllustrator(string $query): array
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(c.name) LIKE :query')
            ->orWhere('LOWER(c.illustrator) LIKE :query')
            ->setParameter('query', '%' . mb_strtolower($query) . '%')
            ->orderBy('c.set', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
