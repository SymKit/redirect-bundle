<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Contract;

use Symkit\RedirectBundle\Entity\Redirect;

/**
 * Contract for the redirect repository (find by path, global search).
 */
interface RedirectRepositoryInterface
{
    /**
     * @param array<string, mixed>       $criteria
     * @param array<string, string>|null $orderBy
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?Redirect;

    /**
     * @return iterable<int, Redirect>
     */
    public function findForGlobalSearch(string $query, int $limit = 5): iterable;
}
