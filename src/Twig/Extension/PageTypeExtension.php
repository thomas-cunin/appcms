<?php

namespace App\Twig\Extension;

use App\Service\PageTypeService;
use App\Twig\Runtime\PageTypeExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PageTypeExtension extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_page_metadata', [PageTypeExtensionRuntime::class, 'getPageTypeMetadata']),
            new TwigFunction('get_all_page_types', [PageTypeExtensionRuntime::class, 'getAllPageTypes']),
        ];
    }

}