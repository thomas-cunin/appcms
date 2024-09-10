<?php

namespace App\Form;

use App\Entity\MenuItem;
use App\Entity\Page;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'empty_data' => '',
                'attr' => [
                    'maxlength' => 60,
                ],
            ])
            ->add('slug', TextType::class, [
                'required' => true,
                'empty_data' => '',
                'attr' => [
                    'maxlength' => 60,
                    // add regex pattern
                    'pattern' => '[a-z0-9-_]+',
                ],
            ])
            ->add('description',TextareaType::class, [
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'maxlength' => 255,
                ],
            ])
            ->add('cancel', ButtonType::class, [
                'attr' => [
                    'data-action' => 'cancel_form',
                ],
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}
