<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\KernelEvents;
use Symkit\RedirectBundle\Contract\RedirectServiceInterface;
use Symkit\RedirectBundle\Controller\Admin\RedirectController;
use Symkit\RedirectBundle\Entity\Redirect;
use Symkit\RedirectBundle\EventListener\RedirectListener;
use Symkit\RedirectBundle\Form\RedirectType;
use Symkit\RedirectBundle\Repository\RedirectRepository;
use Symkit\RedirectBundle\Search\RedirectSearchProvider;
use Symkit\RedirectBundle\Service\RedirectService;

class RedirectBundle extends AbstractBundle
{
    protected string $extensionAlias = 'symkit_redirect';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->booleanNode('enabled')
                    ->defaultTrue()
                    ->info('Master switch to enable or disable the bundle features.')
                ->end()
                ->arrayNode('doctrine')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('entity_class')
                            ->defaultValue(Redirect::class)
                            ->cannotBeEmpty()
                            ->info('FQCN of the redirect entity.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Enable the admin CRUD controller for redirects.')
                        ->end()
                        ->scalarNode('route_prefix')
                            ->defaultValue('admin_redirect')
                            ->cannotBeEmpty()
                            ->info('Prefix for admin route names (e.g. admin_redirect_list).')
                        ->end()
                        ->scalarNode('path_prefix')
                            ->defaultValue('/admin/redirects')
                            ->cannotBeEmpty()
                            ->info('URL path prefix for admin routes.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('listener')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Enable the request listener that performs redirects.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('search')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Register the redirect search provider for global search.')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param array{
     *     enabled: bool,
     *     doctrine: array{entity_class: class-string},
     *     admin: array{enabled: bool, route_prefix: string, path_prefix: string},
     *     listener: array{enabled: bool},
     *     search: array{enabled: bool},
     * } $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (!$config['enabled']) {
            return;
        }

        $entityClass = $config['doctrine']['entity_class'];

        $container->parameters()
            ->set('symkit_redirect.admin.path_prefix', $config['admin']['path_prefix']);

        $services = $container->services();
        $services->defaults()
            ->autowire()
            ->autoconfigure();

        $services->set(RedirectRepository::class)
            ->arg('$entityClass', $entityClass)
            ->tag('doctrine.repository_service');

        if ($config['listener']['enabled']) {
            $services->set(RedirectService::class)
                ->alias(RedirectServiceInterface::class, RedirectService::class);
            $services->set(RedirectListener::class)
                ->tag('kernel.event_listener', [
                    'event' => KernelEvents::REQUEST,
                    'priority' => 33,
                ]);
        }

        if ($config['admin']['enabled']) {
            $services->set(RedirectType::class)
                ->tag('form.type');
            $services->set(RedirectController::class)
                ->arg('$redirectEntityClass', $entityClass)
                ->arg('$routePrefix', $config['admin']['route_prefix'])
                ->tag('controller.service_arguments');
        }

        if ($config['search']['enabled']) {
            $services->set(RedirectSearchProvider::class)
                ->tag('symkit_search.provider');
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('twig', [
            'paths' => [
                $this->getPath().'/templates' => 'SymkitRedirect',
            ],
        ]);
    }
}
