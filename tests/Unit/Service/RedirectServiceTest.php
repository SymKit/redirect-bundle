<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Tests\Unit\Service;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symkit\RedirectBundle\Entity\Redirect;
use Symkit\RedirectBundle\Repository\RedirectRepository;
use Symkit\RedirectBundle\Service\RedirectService;
use Symkit\RoutingBundle\Entity\Route;

final class RedirectServiceTest extends TestCase
{
    public function testGetRedirectTargetReturnsNullWhenNoRedirect(): void
    {
        $repository = $this->createMock(RedirectRepository::class);
        $repository->method('findOneBy')->with(['urlFrom' => '/old'])->willReturn(null);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $service = new RedirectService($repository, $urlGenerator);

        self::assertNull($service->getRedirectTarget('/old'));
    }

    public function testGetRedirectTargetReturnsUrlToWhenSet(): void
    {
        $redirect = new Redirect();
        $redirect->setUrlFrom('/old');
        $redirect->setUrlTo('/new');

        $repository = $this->createMock(RedirectRepository::class);
        $repository->method('findOneBy')->with(['urlFrom' => '/old'])->willReturn($redirect);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $service = new RedirectService($repository, $urlGenerator);

        self::assertSame('/new', $service->getRedirectTarget('/old'));
    }

    public function testGetRedirectTargetReturnsGeneratedRouteUrlWhenRouteSet(): void
    {
        $route = $this->createMock(Route::class);
        $route->method('getName')->willReturn('app_home');

        $redirect = new Redirect();
        $redirect->setUrlFrom('/old');
        $redirect->setRoute($route);

        $repository = $this->createMock(RedirectRepository::class);
        $repository->method('findOneBy')->with(['urlFrom' => '/old'])->willReturn($redirect);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->with('app_home')->willReturn('/');

        $service = new RedirectService($repository, $urlGenerator);

        self::assertSame('/', $service->getRedirectTarget('/old'));
    }

    public function testGetRedirectTargetFallsBackToUrlToWhenRouteGenerationFails(): void
    {
        $route = $this->createMock(Route::class);
        $route->method('getName')->willReturn('broken');

        $redirect = new Redirect();
        $redirect->setUrlFrom('/old');
        $redirect->setUrlTo('/fallback');
        $redirect->setRoute($route);

        $repository = $this->createMock(RedirectRepository::class);
        $repository->method('findOneBy')->with(['urlFrom' => '/old'])->willReturn($redirect);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willThrowException(new Exception('Route not found'));

        $service = new RedirectService($repository, $urlGenerator);

        self::assertSame('/fallback', $service->getRedirectTarget('/old'));
    }
}
