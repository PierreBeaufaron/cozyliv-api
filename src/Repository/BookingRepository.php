<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function findVisibleBookings(UserInterface $user): array
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.room', 'r')
            ->leftJoin('r.advert', 'a')
            ->where('b.tenant = :user OR a.owner = :user')
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    public function findConflictingBookings(
        Room $room, 
        \DateTimeInterface $startDate, 
        \DateTimeInterface $endDate
        ): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.room = :room')
            ->andWhere('(
                (:startDate BETWEEN b.startDate AND b.endDate) OR
                (:endDate BETWEEN b.startDate AND b.endDate) OR
                (b.startDate BETWEEN :startDate AND :endDate) OR
                (b.endDate BETWEEN :startDate AND :endDate) OR
                (:startDate <= b.startDate AND :endDate >= b.endDate)
            )')
            ->setParameter('room', $room)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

}
