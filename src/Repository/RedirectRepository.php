<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symkit\RedirectBundle\Entity\Redirect;

/**
 * @extends ServiceEntityRepository<Redirect>
 *
 * @template T of Redirect
 */
class RedirectRepository extends ServiceEntityRepository
{
    /**
     * @param class-string<T> $entityClass
     */
    public function __construct(ManagerRegistry $registry, string $entityClass = Redirect::class)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @return iterable<int, Redirect>
     */
    public function findForGlobalSearch(string $query, int $limit = 5): iterable
    {
        /** @var iterable<int, Redirect> $result */
        $result = $this->createQueryBuilder('r')
            ->leftJoin('r.route', 'route')
            ->addSelect('route')
            ->where('r.urlFrom LIKE :query OR r.urlTo LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->setMaxResults($limit)
            ->orderBy('r.urlFrom', 'ASC')
            ->getQuery()
            ->toIterable();

        return $result;
    }
}
