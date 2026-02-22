<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Tests\Unit\Form;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symkit\RedirectBundle\Entity\Redirect;
use Symkit\RedirectBundle\Form\RedirectType;

final class RedirectTypeTest extends TestCase
{
    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $type = new RedirectType($translator);
        $type->configureOptions($resolver);

        $options = $resolver->resolve([]);

        self::assertSame(Redirect::class, $options['data_class']);
        self::assertSame('SymkitRedirectBundle', $options['translation_domain']);
    }

    public function testBuildFormRunsWithoutError(): void
    {
        $innerBuilder = $this->createMock(FormBuilderInterface::class);
        $innerBuilder->method('add')->willReturnSelf();
        $innerBuilder->method('create')->willReturn($innerBuilder);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->method('create')->with('general', self::anything(), self::anything())->willReturn($innerBuilder);
        $builder->method('add')->willReturn($builder);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $type = new RedirectType($translator);
        $type->buildForm($builder, ['data_class' => Redirect::class]);
    }
}
