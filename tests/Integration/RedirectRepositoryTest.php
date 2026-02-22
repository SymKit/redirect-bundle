<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Tests\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\Tools\SchemaTool;
use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symkit\RedirectBundle\Entity\Redirect;
use Symkit\RedirectBundle\RedirectBundle;
use Symkit\RoutingBundle\Entity\Route;
use Symkit\RoutingBundle\RoutingBundle;

final class RedirectRepositoryTest extends KernelTestCase
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
                'listener' => ['enabled' => false],
                'admin' => ['enabled' => false],
                'search' => ['enabled' => false],
            ]);
        });
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testFindForGlobalSearchReturnsMatchingRedirects(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        /** @var \Doctrine\Persistence\ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        $em = $doctrine->getManager();
        \assert($em instanceof \Doctrine\ORM\EntityManagerInterface);
        /** @var \Symkit\RedirectBundle\Repository\RedirectRepository<Redirect> $repository */
        $repository = $em->getRepository(Redirect::class);

        $schemaTool = new SchemaTool($em);
        /** @var list<\Doctrine\ORM\Mapping\ClassMetadata<object>> $metadata */
        $metadata = [
            $em->getClassMetadata(Route::class),
            $em->getClassMetadata(Redirect::class),
        ];
        $schemaTool->createSchema($metadata);

        $r1 = new Redirect();
        $r1->setUrlFrom('/old-page');
        $r1->setUrlTo('/new-page');
        $em->persist($r1);

        $r2 = new Redirect();
        $r2->setUrlFrom('/other');
        $r2->setUrlTo('/target');
        $em->persist($r2);

        $em->flush();
        $em->clear();

        $results = iterator_to_array($repository->findForGlobalSearch('old', 10));

        self::assertCount(1, $results);
        self::assertSame('/old-page', $results[0]->getUrlFrom());
        self::assertSame('/new-page', $results[0]->getUrlTo());
    }

    public function testFindForGlobalSearchRespectsLimit(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        /** @var \Doctrine\Persistence\ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        $em = $doctrine->getManager();
        \assert($em instanceof \Doctrine\ORM\EntityManagerInterface);
        /** @var \Symkit\RedirectBundle\Repository\RedirectRepository<Redirect> $repository */
        $repository = $em->getRepository(Redirect::class);

        $schemaTool = new SchemaTool($em);
        /** @var list<\Doctrine\ORM\Mapping\ClassMetadata<object>> $metadata */
        $metadata = [
            $em->getClassMetadata(Route::class),
            $em->getClassMetadata(Redirect::class),
        ];
        $schemaTool->createSchema($metadata);

        foreach (['/page-a', '/page-b', '/page-c', '/page-d', '/page-e'] as $i => $from) {
            $r = new Redirect();
            $r->setUrlFrom($from);
            $r->setUrlTo('/target-'.$i);
            $em->persist($r);
        }
        $em->flush();
        $em->clear();

        $results = iterator_to_array($repository->findForGlobalSearch('page', 2));

        self::assertCount(2, $results);
    }
}
