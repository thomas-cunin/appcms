<?php

namespace App\EventListener;

use App\Entity\MenuItem;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;

#[AsDoctrineListener(event: Events::onFlush, priority: 100)]
class MenuItemPositionListener
{
    /**
     * Listens for entity flush events and updates the position of MenuItem entities.
     *
     * This method is called when the unit of work is flushed, which happens when entities
     * are inserted, updated, or deleted. It iterates through the scheduled entity updates
     * and updates the position of any MenuItem entities that have been updated.
     *
     * @param OnFlushEventArgs $args The event arguments containing the object manager and
     *                              unit of work.
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        dump('onFlush');
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        // Process updated or inserted MenuItem entities
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof MenuItem) {
                $this->updateMenuItemPositions($entity, $em, $uow);
            }
        }
    }

    /**
     * Updates the position indices of menu items with the same parent menu.
     *
     * This method is responsible for updating the position indices of all menu items
     * that have the same parent menu as the provided `$menuItem`. It ensures that the
     * position indices are sequential and start from 0.
     *
     * @param MenuItem $menuItem The menu item for which the positions should be updated.
     * @param EntityManagerInterface $em The entity manager to persist the updated menu items.
     * @param UnitOfWork $uow The unit of work to compute the change set for the updated menu items.
     */
    private function updateMenuItemPositions(MenuItem $menuItem, EntityManagerInterface $em, UnitOfWork $uow): void
    {
//        // Get the parent menu and new position
//        $parentMenu = $menuItem->getParentMenu();
//        $newPositionIndex = $menuItem->getPositionIndex();
//
//        if ($parentMenu === null) {
//            return; // Do nothing if parent menu is not set
//        }
//
//        // Fetch all MenuItems with the same parent menu, ordered by positionIndex
//        $menuItems = $em->getRepository(MenuItem::class)->findBy(
//            ['parentMenu' => $parentMenu],
//            ['positionIndex' => 'ASC']
//        );
//
//        // Exclude the current item from the list if it's an update
//        $menuItems = array_filter($menuItems, fn($item) => $item->getId() !== $menuItem->getId());
//
//        // Insert the current item at the new position
//        array_splice($menuItems, $newPositionIndex, 0, [$menuItem]);
//
//        // Update positions
//        /** @var MenuItem $item */
//        foreach ($menuItems as $index => $item) {
//            $item->setPositionIndex($index);
//            $em->persist($item);
//            dump($item->getPage()->getName(),$item);
//            $uow->computeChangeSet($em->getClassMetadata(MenuItem::class), $item);
//        }
    }
}
