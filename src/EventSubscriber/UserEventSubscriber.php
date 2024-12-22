<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class UserEventSubscriber
{
    public function __construct(
        private UserPasswordHasherInterface $hasher, 
        private ManagerRegistry $doctrine,
        private EntityManagerInterface $entityManager
    ) {}

    // Handles prePersist and preUpdate logic for User entity.
    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->handleUserEntity($args->getObject());
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof User) {
            $changeSet = $args->getEntityChangeSet();

            // Check if password was changed
            if (isset($changeSet['password']) && !password_get_info($changeSet['password'][1])['algo']) {
                $entity->setPassword($this->hasher->hashPassword($entity, $changeSet['password'][1]));
            }
        }

    }

    // Logic for modification before flush.
    private function handleUserEntity($entity): void
    {
        if (!$entity instanceof User) {
            return;
        }

        // Hash the password if not already hashed
        if (!empty($entity->getPassword()) && !password_get_info($entity->getPassword())['algo']) {
            $entity->setPassword($this->hasher->hashPassword($entity, $entity->getPassword()));
        }

        // Assign default roles
        if (empty($entity->getRoles()) || $entity->getRoles() === ['ROLE_USER']) {
            $entity->setRoles(['ROLE_USER']);
        }
    }

    
 
}
