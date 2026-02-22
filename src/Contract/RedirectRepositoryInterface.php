<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Contract;

/**
 * Contract for the redirect repository (find by path, global search).
 */
interface RedirectRepositoryInterface
{
    /**
     * @param array<string, mixed>       $criteria
     * @param array<string, string>|null $orderBy
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?RedirectEntityInterface;

    /**
     * @return iterable<int, RedirectEntityInterface>
     */
    public function findForGlobalSearch(string $query, int $limit = 5): iterable;
}
