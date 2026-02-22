<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symkit\RedirectBundle\Contract\RedirectServiceInterface;
use Symkit\RedirectBundle\Entity\Redirect;
use Symkit\RedirectBundle\Repository\RedirectRepository;
use Throwable;

final class RedirectService implements RedirectServiceInterface
{
    /**
     * @param RedirectRepository<Redirect> $redirectRepository
     */
    public function __construct(
        private readonly RedirectRepository $redirectRepository,
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
