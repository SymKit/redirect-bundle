<?php

declare(strict_types=1);

namespace Symkit\RedirectBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symkit\FormBundle\Form\Type\FormSectionType;
use Symkit\RedirectBundle\Entity\Redirect;
use Symkit\RoutingBundle\Entity\Route;

final class RedirectType extends AbstractType
{
    private const TRANSLATION_DOMAIN = 'SymkitRedirectBundle';

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            $builder->create('general', FormSectionType::class, [
                'inherit_data' => true,
                'label' => 'form.section.general',
                'section_icon' => 'heroicons:arrows-right-left-20-solid',
                'section_description' => 'form.section.general_description',
            ])
                ->add('urlFrom', TextType::class, [
                    'label' => 'form.field.url_from',
                    'attr' => ['placeholder' => $this->translator->trans('form.field.url_from_placeholder', [], self::TRANSLATION_DOMAIN)],
                    'help' => 'form.field.url_from_help',
                ])
                ->add('urlTo', TextType::class, [
                    'label' => 'form.field.url_to',
                    'required' => false,
                    'attr' => ['placeholder' => $this->translator->trans('form.field.url_to_placeholder', [], self::TRANSLATION_DOMAIN)],
                    'help' => 'form.field.url_to_help',
                    'dependency_group' => 'destination',
                    'dependency_icon' => 'heroicons:globe-alt-20-solid',
                ])
                ->add('route', EntityType::class, [
                    'class' => Route::class,
                    'choice_label' => 'name',
                    'label' => 'form.field.route',
                    'required' => false,
                    'placeholder' => $this->translator->trans('form.field.route_placeholder', [], self::TRANSLATION_DOMAIN),
                    'help' => 'form.field.route_help',
                    'dependency_group' => 'destination',
                    'dependency_icon' => 'heroicons:link-20-solid',
                ]),
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Redirect::class,
            'translation_domain' => 'SymkitRedirectBundle',
        ]);
    }
}
