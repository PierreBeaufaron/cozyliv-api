<?php

namespace App\EventSubscriber;

use App\Entity\Advert;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
        $this->capitalizeCity($entity);
        // $this->resolveServices($entity);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Advert) {
            return;
        }

        $this->capitalizeCity($entity);
        // $this->resolveServices($entity);
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

    // Capitalize city's name
    private function capitalizeCity(Advert $advert): void
    {
        $city = $advert->getCity();
        if ($city) {
            $capitalizedCity = ucwords(mb_strtolower($city, 'UTF-8'));
            $advert->setCity($capitalizedCity);
        }
    }

    // Handle services
    // private function resolveServices(Advert $advert): void
    // {
    //     $services = $advert->getServices(); // Obtient la collection envoyée dans le JSON
    //     $resolvedServices = new \Doctrine\Common\Collections\ArrayCollection();

    //     foreach ($services as $service) {
    //         if (method_exists($service, 'getId') && $service->getId()) {
    //             $existingService = $this->entityManager
    //                 ->getRepository(Service::class)
    //                 ->find($service->getId());
    //             if ($existingService) {
    //                 $resolvedServices->add($existingService);
    //             } else {
    //                 error_log("Service with ID {$service->getId()} not found");
    //             }
    //         } else {
    //             error_log("Invalid service object in request");
    //         }
    //     }

    //     // Remplace la collection actuelle par les services résolus
    //     foreach ($advert->getServices() as $service) {
    //         $advert->removeService($service);
    //     }

    //     foreach ($resolvedServices as $resolvedService) {
    //         $advert->addService($resolvedService);
    //     }
    // }


    // TODO Ajouter la logique d'Upload des images
}
