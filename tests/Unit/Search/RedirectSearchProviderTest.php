<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Tests\Unit\Search;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symkit\RedirectBundle\Entity\Redirect;
use Symkit\RedirectBundle\Repository\RedirectRepository;
use Symkit\RedirectBundle\Search\RedirectSearchProvider;
use Symkit\SearchBundle\Model\SearchResult;

final class RedirectSearchProviderTest extends TestCase
{
    public function testSearchYieldsSearchResults(): void
    {
        $redirect = new Redirect();
        $redirect->setUrlFrom('/old');
        $redirect->setUrlTo('/new');
        $ref = new ReflectionProperty(Redirect::class, 'id');
        $ref->setValue($redirect, 1);

        $repository = $this->createMock(RedirectRepository::class);
        $repository->method('findForGlobalSearch')->with('old', 5)->willReturn([$redirect]);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->with('admin_redirect_edit', ['id' => 1])->willReturn('/admin/redirects/1/edit');

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->with('search.category', [], 'SymkitRedirectBundle')->willReturn('Redirects');

        $provider = new RedirectSearchProvider($repository, $urlGenerator, $translator);

        $results = iterator_to_array($provider->search('old'));

        self::assertCount(1, $results);
        $result = $results[0];
        self::assertInstanceOf(SearchResult::class, $result);
        self::assertSame('/old', $result->title);
        self::assertSame('→ /new', $result->subtitle);
        self::assertSame('/admin/redirects/1/edit', $result->url);
        self::assertSame(40, $provider->getPriority());
    }

    public function testGetCategoryReturnsTranslatedCategory(): void
    {
        $repository = $this->createMock(RedirectRepository::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->with('search.category', [], 'SymkitRedirectBundle')->willReturn('Redirections');

        $provider = new RedirectSearchProvider($repository, $urlGenerator, $translator);

        self::assertSame('Redirections', $provider->getCategory());
    }
}
