<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Tests\Unit\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symkit\RedirectBundle\Contract\RedirectServiceInterface;
use Symkit\RedirectBundle\EventListener\RedirectListener;

final class RedirectListenerTest extends TestCase
{
    public function testSubRequestIsIgnored(): void
    {
        $redirectService = $this->createMock(RedirectServiceInterface::class);
        $redirectService->expects(self::never())->method('getRedirectTarget');

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/foo');
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $listener = new RedirectListener($redirectService);
        $listener($event);

        self::assertNull($event->getResponse());
    }

    public function testNoResponseWhenNoRedirectTarget(): void
    {
        $redirectService = $this->createMock(RedirectServiceInterface::class);
        $redirectService->method('getRedirectTarget')->with('/foo')->willReturn(null);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/foo');
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener = new RedirectListener($redirectService);
        $listener($event);

        self::assertNull($event->getResponse());
    }

    public function testRedirectResponseSetWhenTargetFound(): void
    {
        $redirectService = $this->createMock(RedirectServiceInterface::class);
        $redirectService->method('getRedirectTarget')->with('/old')->willReturn('/new');

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/old');
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $listener = new RedirectListener($redirectService);
        $listener($event);

        $response = $event->getResponse();
        self::assertInstanceOf(Response::class, $response);
        self::assertSame(Response::HTTP_PERMANENTLY_REDIRECT, $response->getStatusCode());
        self::assertSame('/new', $response->headers->get('Location'));
    }
}
