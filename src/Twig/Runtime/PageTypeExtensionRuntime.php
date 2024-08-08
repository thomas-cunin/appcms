<?php

namespace App\Twig\Runtime;

use App\Service\PageTypeService;
use Twig\Extension\RuntimeExtensionInterface;

class PageTypeExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private PageTypeService $pageTypeService)
    {
    }

    /**
     * Returns metadata for a specific page type.
     *
     * @param string $type The page type identifier.
     * @return array The metadata for the specified page type.
     */
    public function getPageTypeMetadata(string $type): array
    {
        return $this->pageTypeService->getPageTypeMetadata($type);
    }

    /**
     * Returns all page types and their metadata.
     *
     * @return array An associative array of all page types with their metadata.
     */
    public function getAllPageTypes(): array
    {
        return $this->pageTypeService->getAllPageTypes();
    }
}
