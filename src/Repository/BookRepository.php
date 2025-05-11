<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findByPage(int $page = 1, int $limit = 10): array
    {
        $query = $this->createQueryBuilder('b')
            ->orderBy('b.title', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();
            
        return $query->getResult();
    }

    public function searchByTerms(string $term): array
    {
        if (empty($term)) {
            return [];
        }

        $lowerTerm = strtolower($term);
        
        return $this->createQueryBuilder('b')
            ->where('LOWER(b.title) LIKE :term OR LOWER(b.author) LIKE :term OR LOWER(b.isbn) LIKE :term')
            ->setParameter('term', '%' . $lowerTerm . '%')
            ->orderBy('b.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByCriteria(array $criteria = []): int
    {
        $qb = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)');
            
        foreach ($criteria as $field => $value) {
            $qb->andWhere("b.$field = :$field")
               ->setParameter($field, $value);
        }
        
        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}