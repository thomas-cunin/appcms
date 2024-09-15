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
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\String\Slugger\SluggerInterface;

class PageType extends AbstractType
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class, [
                'required' => true,
            ])
            ->add('description',TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('cancel', ButtonType::class, [
                'attr' => [
                    'data-action' => 'cancel_form',
                ],
            ])
            ->add('submit', SubmitType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $page = $event->getData();
            $form = $event->getForm();
            $pageEntity = $form->getData();

            if (!$page || !isset($page['name'])) {
                return;
            }

            $slug = $this->slugger->slug($page['name']);
            $pageEntity->setSlug($slug);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}
