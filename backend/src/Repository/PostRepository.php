<?php

namespace App\Repository;

use App\Entity\Post;
use App\Enum\PostType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\Query\ResultSetMapping;
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
        $word = trim($word);
        $word = mb_strtolower($word);
        $word = preg_replace('/[^\w+\s]/u', '', $word);
        $word = str_replace(['NEAR', 'AND', 'OR'], '', $word);

        $sql = '
            SELECT 
                id 
            FROM search 
            WHERE 
                title MATCH :word OR 
                body MATCH :word
            ORDER BY rank
            LIMIT 50
        ';

        $rsm = new ResultSetMapping();
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter(':word', $word);
        $result = $query->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);

        if ($result) {
            $qb = $this->createQueryBuilderExcludeSomeTypesAndNot404();
            $qb->andWhere($qb->expr()->in('q.id', $result));
            $qb->orderBy(new OrderBy('q.createdAt', 'DESC'));
            $qb->setMaxResults(50);

            return $qb->getQuery()->getResult();
        }

        return [];
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

    public function findForUpdateIterator()
    {
        $qb = $this->createQueryBuilderExcludeSomeTypesAndNot404();
        $qb->select('q.id');

        $qb->andWhere(
            $qb->expr()->orX(
                'q.updatedAt IS NULL',
                'q.updatedAt < :updated'
            )
        );

        $qb->setParameter('updated', date('Y-m-d', strtotime('-90 days')));

        $qb->setMaxResults(1000);

        return $qb->getQuery()->toIterable();
    }

    public function getForDbUpdate()
    {
        $qb = $this->createQueryBuilderExcludeSomeTypesAndNot404();
        $qb->select('q.id, q.title, q.html');

        return $qb->getQuery()->toIterable();
    }
}
