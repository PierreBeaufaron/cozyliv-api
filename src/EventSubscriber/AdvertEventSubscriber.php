<?php

namespace App\EventSubscriber;

use App\Entity\Advert;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class AdvertEventSubscriber
{

    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {}
    
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Advert) {
            return;
        }

        $this->assignOwner($entity);
        $this->capitalizeTitle($entity);
        $this->capitalizeCity($entity);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Advert) {
            return;
        }

        $this->capitalizeTitle($entity);
        $this->capitalizeCity($entity);
    }

    private function assignOwner(Advert $advert): void
    {
        // Get connected user
        $user = $this->security->getUser();

        if ($user instanceof User) {
            // Set connected user as owner
            $advert->setOwner($user);

            // Add ROLE_OWNER if user dont has
            if (in_array('ROLE_USER', $user->getRoles()) && !in_array('ROLE_OWNER', $user->getRoles())) {
                $roles[] = 'ROLE_OWNER';
                $user->setRoles(array_unique($roles));
            }
        }
    }

    // Capitalize the first letter of the title
    private function capitalizeTitle(Advert $advert): void
    {
        $title = $advert->getTitle();
        if ($title) {
            $capitalizedTitle = ucfirst(mb_strtolower($title, 'UTF-8'));
            $advert->setTitle($capitalizedTitle);
        }
    }

    // Capitalize city's name
    private function capitalizeCity(Advert $advert): void
    {
        $city = $advert->getCity();
        if ($city) {
            $capitalizedCity = ucwords(mb_strtolower($city, 'UTF-8'));
            $advert->setCity($capitalizedCity);
        }
    }

    // TODO Ajouter la logique d'Upload des images

    // TODO Ajouter mail de confirmation
}
