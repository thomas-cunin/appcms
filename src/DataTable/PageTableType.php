<?php

namespace App\DataTable;

use App\Entity\Page;
use Doctrine\ORM\QueryBuilder; // Utilisez la classe QueryBuilder de Doctrine ORM
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\NumberColumn;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageTableType implements DataTableTypeInterface
{
    public function configure(DataTable $dataTable, array $options) : void
    {
        $dataTable
            ->add('name', TextColumn::class, ['label' => 'Name'])
            ->add('slug', TextColumn::class, ['label' => 'Slug'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Page::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('p')
                        ->from(Page::class, 'p');
                    ;
                },
            ])
            ->setName('page_table');
    }
}