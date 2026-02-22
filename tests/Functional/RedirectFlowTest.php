<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\Tools\SchemaTool;
use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symkit\RedirectBundle\Entity\Redirect;
use Symkit\RedirectBundle\RedirectBundle;
use Symkit\RoutingBundle\RoutingBundle;

final class RedirectFlowTest extends WebTestCase
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
                    'naming_strategy' => 'doctrine.orm.naming_strategy.underscore',
                    'mappings' => [
                        'RedirectBundle' => ['type' => 'attribute', 'is_bundle' => true],
                        'RoutingBundle' => ['type' => 'attribute', 'is_bundle' => true],
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
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testRequestToRedirectedPathReturns301WithLocation(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var \Doctrine\Persistence\ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        $em = $doctrine->getManager();
        \assert($em instanceof \Doctrine\ORM\EntityManagerInterface);

        $schemaTool = new SchemaTool($em);
        /** @var list<\Doctrine\ORM\Mapping\ClassMetadata<object>> $metadata */
        $metadata = [
            $em->getClassMetadata(\Symkit\RoutingBundle\Entity\Route::class),
            $em->getClassMetadata(Redirect::class),
        ];
        $schemaTool->createSchema($metadata);

        $redirect = new Redirect();
        $redirect->setUrlFrom('/old-path');
        $redirect->setUrlTo('/new-path');
        $em->persist($redirect);
        $em->flush();

        $client->request('GET', '/old-path');

        self::assertResponseStatusCodeSame(308); // HTTP_PERMANENTLY_REDIRECT
        self::assertResponseHeaderSame('Location', '/new-path');
    }
}
