<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Contract;

interface RedirectServiceInterface
{
    public function getRedirectTarget(string $path): ?string;
}
