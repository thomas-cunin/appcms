<?php

namespace App\Form;

use App\Entity\Application;
use App\Entity\Menu;
use App\Entity\MenuItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;

class MenuType extends AbstractType
{
    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $slugger = $this->slugger;
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
            ])
            // if Menu entity have no id add automatically ->setMenuType(Menu::TYPE_MAIN)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $menu = $event->getData();
                $form = $event->getForm();
                if (null === $menu->getId()) {
                    $menu->setMenuType(Menu::TYPE_SUBMENU);
                }
            } )
            // when the form is submitted, set slug
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($slugger) {
                $menu = $event->getData();
                $form = $event->getForm();
                if (null === $menu->getId()) {
                    $menu->setSlug($slugger->slug($menu->getName()));
                }
            } )
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}
