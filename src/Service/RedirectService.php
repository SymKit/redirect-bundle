<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symkit\RedirectBundle\Contract\RedirectRepositoryInterface;
use Symkit\RedirectBundle\Contract\RedirectServiceInterface;
use Throwable;

final readonly class RedirectService implements RedirectServiceInterface
{
    public function __construct(
        private readonly RedirectRepositoryInterface $redirectRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getRedirectTarget(string $path): ?string
    {
        $redirect = $this->redirectRepository->findOneBy(['urlFrom' => $path]);

        if (!$redirect) {
            return null;
        }

        if ($route = $redirect->getRoute()) {
            try {
                return $this->urlGenerator->generate($route->getName() ?? '');
            } catch (Throwable) {
                // If route generation fails, fall back to urlTo if available
            }
        }

        return $redirect->getUrlTo();
    }
}
