<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Tests\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symkit\RedirectBundle\RedirectBundle;
use Symkit\RoutingBundle\RoutingBundle;

final class RedirectBundleBootTest extends KernelTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        restore_exception_handler();
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        /** @var TestKernel $kernel */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(RedirectBundle::class);
        if (!isset($options['config'])) {
            $kernel->addTestConfig(static function (ContainerBuilder $container): void {
                $container->loadFromExtension('framework', [
                    'test' => true,
                    'secret' => 'test',
                ]);
                $container->loadFromExtension('symkit_redirect', [
                    'enabled' => false,
                ]);
            });
        }
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testBundleBootsWithDisabledConfig(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

        self::assertTrue($container->has('kernel'));
    }

    public function testBundleBootsWithEnabledConfigAndServicesRegistered(): void
    {
        $kernel = self::bootKernel([
            'config' => function (TestKernel $kernel): void {
                $kernel->setTestProjectDir(\dirname(__DIR__, 2));
                $kernel->addTestBundle(DoctrineBundle::class);
                $kernel->addTestBundle(RoutingBundle::class);
                $kernel->addTestBundle(RedirectBundle::class);
                $kernel->addTestConfig(static function (ContainerBuilder $container): void {
                    $container->loadFromExtension('framework', [
                        'test' => true,
                        'secret' => 'test',
                    ]);
                    $container->loadFromExtension('doctrine', [
                        'dbal' => ['url' => 'sqlite:///:memory:'],
                        'orm' => [
                            'mappings' => [
                                'RedirectBundle' => [
                                    'type' => 'attribute',
                                    'is_bundle' => true,
                                ],
                                'RoutingBundle' => [
                                    'type' => 'attribute',
                                    'is_bundle' => true,
                                ],
                            ],
                        ],
                    ]);
                    $container->loadFromExtension('symkit_redirect', [
                        'enabled' => true,
                        'listener' => ['enabled' => true],
                        'admin' => ['enabled' => false],
                        'search' => ['enabled' => false],
                    ]);
                });
            },
        ]);

        $container = $kernel->getContainer();

        self::assertTrue($container->has('kernel'));
    }
}
