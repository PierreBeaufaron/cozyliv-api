<?php

namespace App\EventSubscriber;

use App\Entity\Booking;
use App\Entity\User;
use App\Repository\BookingRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class BookingEventSubscriber
{

    public function __construct(
        private Security $security,
        private BookingRepository $bookingRepository,
    ) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->handleBookingEvent($args, true);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->handleBookingEvent($args, false);
    }
    
    public function handleBookingEvent(LifecycleEventArgs $args, bool $isPersist): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Booking) {
            return;
        }

        // Check date conflicts 
        $this->validateDateConflicts($entity);

        $this->validateMinimumDuration($entity);

        if ($isPersist) {
            // Set connected user as tenant
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $entity->setTenant($user);
            }

            // Initialise status
            $entity->setStatus(1);
        }
    }

    private function validateDateConflicts(Booking $booking): void
    {
        $room = $booking->getRoom();
        $startDate = $booking->getStartDate();
        $endDate = $booking->getEndDate();

        if (!$room || !$startDate || !$endDate) {
            throw new BadRequestHttpException('Les dates et la chambre doivent être spécifiées.');
        }

        $conflicts = $this->bookingRepository->findConflictingBookings($room, $startDate, $endDate);

        if (!empty($conflicts)) {
            throw new BadRequestHttpException('Cette chambre est déjà réservée pour les dates sélectionnées.');
        }
    }

    private function validateMinimumDuration(Booking $booking): void
    {
        $startDate = $booking->getStartDate();
        $endDate = $booking->getEndDate();

        if (!$startDate || !$endDate) {
            throw new BadRequestHttpException('Les dates doivent être spécifiées.');
        }

        $duration = $startDate->diff($endDate)->days;

        if ($duration < 30) {
            throw new BadRequestHttpException('La réservation doit durer au moins 30 jours.');
        }
    }

    // TODO Ajouter mail de confirmation
}
