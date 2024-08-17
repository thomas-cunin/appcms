<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PageTypeService
{
    private array $pageTypes;

    public function __construct(ParameterBagInterface $params)
    {
        $this->pageTypes = $params->get('page_types');
    }

    /**
     * Returns the page type metadata for the given page type.
     *
     * @param string $type The page type to get the metadata for.
     * @return array The page type metadata.
     * @throws \InvalidArgumentException If the given page type is invalid.
     */
    public function getPageTypeMetadata(string $type): array
    {
        if (isset($this->pageTypes[$type])) {
            return $this->pageTypes[$type];
        }

        throw new \InvalidArgumentException("Invalid page type: $type");
    }

    /**
     * Returns an array of all available page types.
     *
     * @return array An array of page types.
     */
    public function getAllPageTypes(): array
    {
        return $this->pageTypes;
    }

    /**
     * Returns the form class for the given page type.
     *
     * @param string $type The page type to get the form class for.
     * @return string The form class for the given page type.
     * @throws \InvalidArgumentException If the given page type is invalid.
     */
    public function getFormClass(string $type): string
    {
        return $this->getPageTypeMetadata($type)['form_class'];
    }
}