<?php

namespace App\Repository;

use App\Entity\Post;
use App\Enum\PostType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

final class PostRepository extends ServiceEntityRepository
{
    private const POST_PEER_PAGE = 20;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function getPostsPeerPage(): int
    {
        return self::POST_PEER_PAGE;
    }

    private function createQueryBuilderExcludeSomeTypes(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('q');
        $qb->select(implode(',', [
            'q.id',
            'q.createdAt',
            'q.lastModified',
            'q.like',
            'q.title',
            'q.postType',
        ]));
        $qb->where($qb->expr()->notIn('q.postType', [PostType::INTRO->value, PostType::WEEKLY_DIGEST->value]));

        return $qb;
    }

    private function createQueryBuilderExcludeSomeTypesAndNot404(): QueryBuilder
    {
        $qb = $this->createQueryBuilderExcludeSomeTypes();
        $qb->andWhere($qb->expr()->isNull('q.deletedAt'));

        return $qb;
    }

    /**
     * @return Post[]
     */
    public function getForDbUpdate()
    {
        $qb = $this->createQueryBuilderExcludeSomeTypesAndNot404();
        $qb->select('q.id');
        $qb->andWhere('q.lastModified < :updated');
        $qb->setParameter('updated', date('Y-m-d', strtotime('-1 week')));

        return $qb->getQuery()->getSingleColumnResult();
    }

    public function filter(string $type, int $page): Paginator
    {
        $qb = $this->createQueryBuilderExcludeSomeTypesAndNot404();

        switch ($type) {
            case 'new':
                $qb->andWhere($qb->expr()->isNull('q.viewedAt'));
                $qb->addSelect('CASE WHEN q.createdAt IS NULL THEN 1 ELSE 0 END AS HIDDEN orderNullFirst');
                $qb->addOrderBy('orderNullFirst', 'DESC');
                $qb->addOrderBy('q.createdAt', 'DESC');
                $qb->addOrderBy('q.lastModified', 'DESC');
                break;

            case 'favorite':
                $qb->andWhere($qb->expr()->eq('q.like', true));
                $qb->orderBy(new OrderBy('q.createdAt', 'DESC'));
                break;

            case 'done':
                $qb->andWhere($qb->expr()->isNotNull('q.viewedAt'));
                $qb->orderBy(new OrderBy('q.createdAt', 'DESC'));
                break;

            case 'updated':
                $qb->andWhere('q.lastModified > q.viewedAt');
                $qb->orderBy(new OrderBy('q.lastModified', 'DESC'));
                break;
        }

        $qb->setFirstResult($page * $this->getPostsPeerPage());
        $qb->setMaxResults($this->getPostsPeerPage());

        return new Paginator($qb);
    }

    public function deleted(int $page): Paginator
    {
        $qb = $this->createQueryBuilderExcludeSomeTypes();
        $qb->andWhere($qb->expr()->isNotNull('q.deletedAt'));
        $qb->andWhere($qb->expr()->isNotNull('q.html'));

        $qb->setFirstResult($page * $this->getPostsPeerPage());
        $qb->setMaxResults($this->getPostsPeerPage());

        return new Paginator($qb);
    }

    public function countProgress(): array
    {
        $qb = $this->createQueryBuilderExcludeSomeTypesAndNot404();
        $qb->select('count(q.id)');

        $qb1 = clone $qb;
        $total = $qb1->getQuery()->getSingleScalarResult();

        $qb2 = clone $qb;
        $qb2->andWhere($qb2->expr()->isNotNull('q.viewedAt'));
        $viewed = $qb2->getQuery()->getSingleScalarResult();

        $qb3 = clone $qb;
        $qb3->andWhere('q.viewedAt < q.lastModified');
        $updated = $qb3->getQuery()->getSingleScalarResult();

        $qb4 = clone $qb;
        $qb4->andWhere('q.like = 1');
        $favorite = $qb4->getQuery()->getSingleScalarResult();

        return [$total, $viewed, $updated, $favorite];
    }

    /**
     * @return Post[]
     */
    public function search(string $word): array
    {
        $qb = $this->createQueryBuilderExcludeSomeTypesAndNot404();
        $qb->andWhere($qb->expr()->like(
            'q.searchIndex',
            $qb->expr()->literal('%'.str_replace(['%', '?'], '', mb_strtolower($word)).'%')
        ));
        $qb->orderBy(new OrderBy('q.createdAt', 'DESC'));

        return $qb->getQuery()->getResult();
    }

    public function markAllAsRead()
    {
        $qb = $this->createQueryBuilder('q');
        $qb->update(Post::class, 'q');

        $qb->set('q.viewedAt', ':date');
        $qb->setParameter(':date', (new \DateTime())->format('Y-m-d H:i:s'));

        $qb->andWhere('q.viewedAt < q.lastModified');
        $qb->andWhere('q.deletedAt is NULL');

        return $qb->getQuery()->execute();
    }

    // TODO q.lastModified > 7days
    public function findAllIterator()
    {
        $qb = $this->createQueryBuilderExcludeSomeTypesAndNot404();
        $qb->select('q.id');
        $qb->andWhere($qb->expr()->isNull('q.searchIndex'));

        // $qb->addOrderBy('q.id', 'ASC');
        // $qb->setFirstResult(4000);
        // $qb->setMaxResults(500);

        return $qb->getQuery()->toIterable();
    }
}
