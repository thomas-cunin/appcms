<?php

namespace App\Form;

use App\Entity\ContentPage;
use App\Entity\MenuItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentPageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class, [
                'required' => true,
                'attr' => ['placeholder' => 'Titre de la page'],
            ])
            ->add('content',TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('description',TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContentPage::class,
        ]);
    }
}
