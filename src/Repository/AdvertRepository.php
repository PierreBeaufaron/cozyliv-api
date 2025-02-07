<?php

namespace App\Repository;

use App\Entity\Advert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Advert>
 */
class AdvertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advert::class);
    }

    public function searchAdverts(
        ?string $location, 
        ?\DateTimeInterface $startDate, 
        ?\DateTimeInterface $endDate, 
        ?string $roomsRange, 
        ?array $services
        ): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.rooms', 'r')
            ->leftJoin('a.services', 's')
            ->addSelect('r', 's');

        if ($location) {
            $qb->andWhere('a.city LIKE :location OR a.country LIKE :location')
               ->setParameter('location', '%' . $location . '%');
        }

        if ($startDate && $endDate) {
            $qb->andWhere(':startDate NOT BETWEEN r.bookings.startDate AND r.bookings.endDate')
               ->andWhere(':endDate NOT BETWEEN r.bookings.startDate AND r.bookings.endDate')
               ->andWhere('NOT (:startDate <= r.bookings.startDate AND :endDate >= r.bookings.endDate)')
               ->setParameter('startDate', $startDate)
               ->setParameter('endDate', $endDate);
        }

        // Filter by number of rooms with parseRoomRange function.
        if ($roomsRange) {
            [$minRooms, $maxRooms] = $this->parseRoomsRange($roomsRange);
            
            $qb->andWhere('a.nbRoom BETWEEN :minRooms AND :maxRooms')
               ->setParameter('minRooms', $minRooms)
               ->setParameter('maxRooms', $maxRooms);
        }

        // For filter with services later
        if ($services && count($services) > 0) {
            $qb->andWhere('s.name IN (:services)')
               ->setParameter('services', $services);
        }

        return $qb->getQuery()->getResult();
    }

    private function parseRoomsRange(string $roomsRange): array
    {
        switch ($roomsRange) {
            case '2-3':
                return [2, 3];
            case '4-6':
                return [4, 6];
            case '7-9':
                return [7, 9];
            case '10+':
                return [10, 999];
            default:
                return [0, 999];
        }
    }
}
