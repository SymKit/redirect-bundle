<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symkit\CrudBundle\Contract\CrudPersistenceManagerInterface;
use Symkit\CrudBundle\Controller\AbstractCrudController;
use Symkit\MenuBundle\Attribute\ActiveMenu;
use Symkit\MetadataBundle\Attribute\Breadcrumb;
use Symkit\MetadataBundle\Attribute\Seo;
use Symkit\MetadataBundle\Contract\PageContextBuilderInterface;
use Symkit\RedirectBundle\Entity\Redirect;
use Symkit\RedirectBundle\Form\RedirectType;

final class RedirectController extends AbstractCrudController
{
    private const TRANSLATION_DOMAIN = 'SymkitRedirectBundle';

    /**
     * @param class-string<Redirect> $redirectEntityClass
     */
    public function __construct(
        CrudPersistenceManagerInterface $persistenceManager,
        PageContextBuilderInterface $pageContextBuilder,
        private readonly TranslatorInterface $translator,
        private readonly string $redirectEntityClass = Redirect::class,
        private readonly string $routePrefix = 'admin_redirect',
    ) {
        parent::__construct($persistenceManager, $pageContextBuilder);
    }

    protected function getEntityClass(): string
    {
        return $this->redirectEntityClass;
    }

    protected function getFormClass(): string
    {
        return RedirectType::class;
    }

    protected function getRoutePrefix(): string
    {
        return $this->routePrefix;
    }

    protected function configureListFields(): array
    {
        return [
            'urlFrom' => [
                'label' => $this->translator->trans('admin.field.source', [], self::TRANSLATION_DOMAIN),
                'sortable' => true,
                'cell_class' => 'font-mono text-sm',
            ],
            'urlTo' => [
                'label' => $this->translator->trans('admin.field.destination', [], self::TRANSLATION_DOMAIN),
                'template' => '@SymkitRedirect/crud/field/destination.html.twig',
            ],
            'createdAt' => [
                'label' => $this->translator->trans('admin.field.created', [], self::TRANSLATION_DOMAIN),
                'sortable' => true,
                'template' => '@SymkitCrud/crud/field/date.html.twig',
            ],
            'actions' => [
                'label' => '',
                'template' => '@SymkitCrud/crud/field/actions.html.twig',
                'edit_route' => $this->getRoutePrefix().'_edit',
                'header_class' => 'text-right',
                'cell_class' => 'text-right',
            ],
        ];
    }

    protected function configureSearchFields(): array
    {
        return ['urlFrom', 'urlTo'];
    }

    #[Seo(title: 'Redirects', description: 'Manage URL redirects.')]
    #[Breadcrumb(context: 'admin')]
    #[ActiveMenu('admin', 'redirects')]
    public function list(Request $request): Response
    {
        return $this->renderIndex($request, [
            'page_title' => new TranslatableMessage('admin.page_title.list', [], self::TRANSLATION_DOMAIN),
            'page_description' => new TranslatableMessage('admin.page_description.list', [], self::TRANSLATION_DOMAIN),
        ]);
    }

    #[Seo(title: 'Create Redirect', description: 'Create a new redirect.')]
    #[Breadcrumb(context: 'admin')]
    #[ActiveMenu('admin', 'redirects')]
    public function create(Request $request): Response
    {
        $entity = new $this->redirectEntityClass();

        return $this->renderNew($entity, $request, [
            'page_title' => new TranslatableMessage('admin.page_title.create', [], self::TRANSLATION_DOMAIN),
            'page_description' => new TranslatableMessage('admin.page_description.create', [], self::TRANSLATION_DOMAIN),
        ]);
    }

    #[Seo(title: 'Edit Redirect', description: 'Update redirect.')]
    #[Breadcrumb(context: 'admin')]
    #[ActiveMenu('admin', 'redirects')]
    public function edit(Redirect $redirect, Request $request): Response
    {
        return $this->renderEdit($redirect, $request, [
            'page_title' => new TranslatableMessage('admin.page_title.edit', [], self::TRANSLATION_DOMAIN),
            'page_description' => new TranslatableMessage('admin.page_description.edit', [], self::TRANSLATION_DOMAIN),
        ]);
    }

    public function delete(Redirect $redirect, Request $request): Response
    {
        return $this->performDelete($redirect, $request);
    }
}
