<?php

namespace App\EventSubscriber;

use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManagerInterface;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preRemove)]
class RoomEventSubscriber
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->updateAdvertRoomCount($args->getObject(), 1);
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $this->updateAdvertRoomCount($args->getObject(), -1);
    }

    private function updateAdvertRoomCount(object $entity, int $delta): void
    {
        // Check entity is Room.
        if (!$entity instanceof Room) {
            return;
        }

        $advert = $entity->getAdvert();
        if ($advert) {
            $advert->setNbRoom($advert->getNbRoom() + $delta);
            $this->entityManager->persist($advert);
        }

        $this->capitalizeName($entity);
    }

    private function capitalizeName(Room $room): void
    {
        $title = $room->getName();
        if ($title) {
            $capitalizedName = ucfirst(mb_strtolower($title, 'UTF-8'));
            $room->setName($capitalizedName);
        }
    }
}
