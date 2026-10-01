<?php

declare(strict_types=1);

namespace FluxSE\SyliusStripePlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;

final class StripeAppearanceType extends AbstractType
{
    public const COLOR_PATTERN = '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6})$/';

    public const BORDER_RADIUS_PATTERN = '/^\d+(px|rem|em|%)$/';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('theme', ChoiceType::class, [
                'label' => 'flux_se_sylius_stripe_plugin.stripe_appearance.theme',
                'required' => false,
                'placeholder' => 'flux_se_sylius_stripe_plugin.stripe_appearance.theme_choices.stripe',
                'choices' => [
                    'flux_se_sylius_stripe_plugin.stripe_appearance.theme_choices.night' => 'night',
                    'flux_se_sylius_stripe_plugin.stripe_appearance.theme_choices.flat' => 'flat',
                ],
            ])
            ->add('inputs', ChoiceType::class, [
                'label' => 'flux_se_sylius_stripe_plugin.stripe_appearance.inputs',
                'required' => false,
                'placeholder' => 'flux_se_sylius_stripe_plugin.stripe_appearance.inputs_choices.spaced',
                'choices' => [
                    'flux_se_sylius_stripe_plugin.stripe_appearance.inputs_choices.condensed' => 'condensed',
                ],
            ])
            ->add('labels', ChoiceType::class, [
                'label' => 'flux_se_sylius_stripe_plugin.stripe_appearance.labels',
                'required' => false,
                'placeholder' => 'flux_se_sylius_stripe_plugin.stripe_appearance.labels_choices.auto',
                'choices' => [
                    'flux_se_sylius_stripe_plugin.stripe_appearance.labels_choices.above' => 'above',
                    'flux_se_sylius_stripe_plugin.stripe_appearance.labels_choices.floating' => 'floating',
                ],
            ])
            ->add('colorPrimary', TextType::class, [
                'label' => 'flux_se_sylius_stripe_plugin.stripe_appearance.color_primary',
                'required' => false,
                'constraints' => [
                    new Regex(
                        pattern: self::COLOR_PATTERN,
                        message: 'flux_se_sylius_stripe_plugin.stripe_appearance.color',
                        groups: ['sylius', 'stripe_web_elements'],
                    ),
                ],
            ])
            ->add('colorBackground', TextType::class, [
                'label' => 'flux_se_sylius_stripe_plugin.stripe_appearance.color_background',
                'required' => false,
                'constraints' => [
                    new Regex(
                        pattern: self::COLOR_PATTERN,
                        message: 'flux_se_sylius_stripe_plugin.stripe_appearance.color',
                        groups: ['sylius', 'stripe_web_elements'],
                    ),
                ],
            ])
            ->add('colorText', TextType::class, [
                'label' => 'flux_se_sylius_stripe_plugin.stripe_appearance.color_text',
                'required' => false,
                'constraints' => [
                    new Regex(
                        pattern: self::COLOR_PATTERN,
                        message: 'flux_se_sylius_stripe_plugin.stripe_appearance.color',
                        groups: ['sylius', 'stripe_web_elements'],
                    ),
                ],
            ])
            ->add('colorDanger', TextType::class, [
                'label' => 'flux_se_sylius_stripe_plugin.stripe_appearance.color_danger',
                'required' => false,
                'constraints' => [
                    new Regex(
                        pattern: self::COLOR_PATTERN,
                        message: 'flux_se_sylius_stripe_plugin.stripe_appearance.color',
                        groups: ['sylius', 'stripe_web_elements'],
                    ),
                ],
            ])
            ->add('fontFamily', TextType::class, [
                'label' => 'flux_se_sylius_stripe_plugin.stripe_appearance.font_family',
                'required' => false,
            ])
            ->add('borderRadius', TextType::class, [
                'label' => 'flux_se_sylius_stripe_plugin.stripe_appearance.border_radius',
                'required' => false,
                'constraints' => [
                    new Regex(
                        pattern: self::BORDER_RADIUS_PATTERN,
                        message: 'flux_se_sylius_stripe_plugin.stripe_appearance.border_radius',
                        groups: ['sylius', 'stripe_web_elements'],
                    ),
                ],
            ])
            ->add('spacingUnit', TextType::class, [
                'label' => 'flux_se_sylius_stripe_plugin.stripe_appearance.spacing_unit',
                'required' => false,
                'constraints' => [
                    new Regex(
                        pattern: self::BORDER_RADIUS_PATTERN,
                        message: 'flux_se_sylius_stripe_plugin.stripe_appearance.border_radius',
                        groups: ['sylius', 'stripe_web_elements'],
                    ),
                ],
            ])
        ;
    }
}
