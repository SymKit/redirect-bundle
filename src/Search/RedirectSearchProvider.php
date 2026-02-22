<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Search;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symkit\RedirectBundle\Contract\RedirectRepositoryInterface;
use Symkit\SearchBundle\Contract\SearchProviderInterface;
use Symkit\SearchBundle\Model\SearchResult;

final readonly class RedirectSearchProvider implements SearchProviderInterface
{
    private const TRANSLATION_DOMAIN = 'SymkitRedirectBundle';

    public function __construct(
        private RedirectRepositoryInterface $redirectRepository,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
    ) {
    }

    public function search(string $query): iterable
    {
        $redirects = $this->redirectRepository->findForGlobalSearch($query);

        foreach ($redirects as $redirect) {
            yield new SearchResult(
                title: $redirect->getUrlFrom() ?? '',
                subtitle: '→ '.$redirect->getUrlTo(),
                url: $this->urlGenerator->generate('admin_redirect_edit', ['id' => $redirect->getId()]),
                icon: 'heroicons:arrow-path-20-solid',
                badge: null,
            );
        }
    }

    public function getCategory(): string
    {
        return $this->translator->trans('search.category', [], self::TRANSLATION_DOMAIN);
    }

    public function getPriority(): int
    {
        return 40;
    }
}
