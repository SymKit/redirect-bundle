<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symkit\RedirectBundle\Contract\RedirectServiceInterface;

final readonly class RedirectListener
{
    public function __construct(
        private readonly RedirectServiceInterface $redirectService,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $targetUrl = $this->redirectService->getRedirectTarget($request->getPathInfo());

        if ($targetUrl) {
            $event->setResponse(new RedirectResponse($targetUrl, Response::HTTP_PERMANENTLY_REDIRECT));
        }
    }
}
