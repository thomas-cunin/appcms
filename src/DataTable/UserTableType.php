<?php

namespace App\DataTable;

use App\Entity\Page;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder; // Utilisez la classe QueryBuilder de Doctrine ORM
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\NumberColumn;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserTableType implements DataTableTypeInterface
{
    public function configure(DataTable $dataTable, array $options) : void
    {
        $dataTable
            ->add('checkbox', TextColumn::class, [
                'label' => '',
                'data' => function($context, $value) {
                    return '<input type="checkbox" name="ids[]" value="'.$context->getId().'">';
                },
                'raw' => true,
                'globalSearchable' => true,
            ])
            ->add('email', TextColumn::class, ['label' => 'Email'])
            // Ajoutez une colonne pour le prénom + nom
            ->add('fullName', TextColumn::class, [
                'label' => 'Full Name',
                'propertyPath' => 'fullName',
            ])
            ->add('createdAt', DateTimeColumn::class, [
                'label' => 'Created At',
                'format' => 'd-m-Y'
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => User::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('u')
                        ->from(User::class, 'u');
                    ;
                },
            ])
            ->setName('users_table');
    }
}
