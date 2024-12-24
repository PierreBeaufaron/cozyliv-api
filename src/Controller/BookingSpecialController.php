<?php

namespace App\Controller;

use App\Repository\BookingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class BookingSpecialController extends AbstractController
{
    #[Route('/api/bookings', name: 'get_bookings', methods: ['GET'])]
    public function getBookings(BookingRepository $bookingRepository): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof UserInterface) {
            return $this->json(['error' => 'Access denied.'], 403);
        }

        // Filtrer les réservations visibles par l'utilisateur
        $bookings = $bookingRepository->findVisibleBookings($user);

        return $this->json($bookings);
    }
}
