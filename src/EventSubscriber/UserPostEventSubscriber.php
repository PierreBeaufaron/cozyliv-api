<?php

namespace App\EventSubscriber;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPostEventSubscriber implements EventSubscriberInterface
{
    private $doctrine;
    public function __construct(private UserPasswordHasherInterface $hasher, ManagerRegistry $doctrine)
  {
        $this->doctrine = $doctrine;
  }

  public function getSubscribedEvents(): array
  {
    return [
      Events::prePersist,
      Events::preUpdate,
    ];
  }

  public function prePersist(PrePersistEventArgs $args): void
  {
    $entity = $args->getObject();

    if (!$entity instanceof User) {
      return;
    }

    // Check if the password field has content
    if (empty($entity->getPassword())) {
        return;
    }
    // Hashing password
    $entity->setPassword($this->hasher->hashPassword($entity, $entity->getPassword()));
    
    // Check if user->getRoles return empty or only ['ROLE_USER'] for persist ['ROLE_USER']
    if (empty($entity->getRoles()) || $entity->getRoles() === ['ROLE_USER']) {
        $entity->setRoles(['ROLE_USER']);
    }

    $entityManager = $this->doctrine->getManager();

    // No repeat Country
    if ($entity->getCity() && $entity->getCity()->getCountry()) {
        $countryName = $entity->getCity()->getCountry()->getName();
        $countryRepository = $entityManager->getRepository(Country::class);

        // Check if country already exist
        $existingCountry = $countryRepository->findOneBy(['name' => $countryName]);

        if ($existingCountry) {
            $entity->getCity()->setCountry($existingCountry);
        }
    }

    // No repeat City
if ($entity->getCity()) {
    $cityName = $entity->getCity()->getName();
    $zipCode = $entity->getCity()->getZipCode();

    $cityRepository = $entityManager->getRepository(City::class);

    // Check if country already exist with name + zipCode
    $existingCity = $cityRepository->findOneBy([
        'name' => $cityName,
        'zipCode' => $zipCode,
    ]);

    if ($existingCity) {
        $entity->setCity($existingCity);
    }
}
  }

  public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof User) {
            return;
        }

        // Check if the password field has been updated
        $changeSet = $args->getEntityChangeSet();

        if (isset($changeSet['password'])) {
            $newPassword = $changeSet['password'][1]; // New password value

            // Hash the new password
            $entity->setPassword($this->hasher->hashPassword($entity, $newPassword));

            // Recompute changeset for Doctrine
            $entityManager = $args->getObjectManager();
            $meta = $entityManager->getClassMetadata(get_class($entity));
            $entityManager->getUnitOfWork()->recomputeSingleEntityChangeSet($meta, $entity);
        }
    }
}
