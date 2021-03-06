<?php

namespace App\Repository;

use App\Entity\Post;
use App\Enum\PostType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

final class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    private function createQueryBuilderExcludeSomeTypesAndNot404(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('q');
        $qb->where($qb->expr()->notIn('q.postType', [PostType::INTRO->value, PostType::WEEKLY_DIGEST->value]));
        $qb->andWhere($qb->expr()->isNull('q.deletedAt'));

        return $qb;
    }

    /**
     * @return Post[]
     */
    public function findNew(): array
    {
        $qb = $this->createQueryBuilderExcludeSomeTypesAndNot404();
        $qb->andWhere($qb->expr()->isNull('q.viewedAt'));
        $qb->addSelect('CASE WHEN q.createdAt IS NULL THEN 1 ELSE 0 END AS HIDDEN orderNullFirst');
        $qb->addOrderBy('orderNullFirst', 'DESC');
        $qb->addOrderBy('q.createdAt', 'DESC');
        $qb->addOrderBy('q.updatedAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Post[]
     */
    public function findBest(): array
    {
        $qb = $this->createQueryBuilderExcludeSomeTypesAndNot404();
        $qb->andWhere($qb->expr()->eq('q.like', true));
        $qb->orderBy(new OrderBy('q.createdAt', 'DESC'));

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Post[]
     */
    public function findPast(): array
    {
        $qb = $this->createQueryBuilderExcludeSomeTypesAndNot404();
        $qb->andWhere($qb->expr()->isNotNull('q.viewedAt'));
        $qb->orderBy(new OrderBy('q.createdAt', 'DESC'));

        return $qb->getQuery()->getResult();
    }

    public function countProgress()
    {
        $qb = $this->createQueryBuilderExcludeSomeTypesAndNot404();
        $qb->select('count(q.id)');
        $total = $qb->getQuery()->getSingleScalarResult();
        $qb->where($qb->expr()->isNotNull('q.viewedAt'));
        $viewed = $qb->getQuery()->getSingleScalarResult();

        return [$total, $viewed];
    }

    /**
     * @return Post[]
     */
    public function findByDate(string $dateAsString): array
    {
        $qb = $this->createQueryBuilderExcludeSomeTypesAndNot404();
        $qb->andWhere($qb->expr()->isNull('q.viewedAt'));
        $qb->andWhere($qb->expr()->eq('q.updatedAt', $qb->expr()->literal($dateAsString)));
        $qb->orderBy(new OrderBy('q.updatedAt', 'DESC'));

        return $qb->getQuery()->getResult();
    }
}
