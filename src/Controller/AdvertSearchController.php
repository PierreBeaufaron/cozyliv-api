<?php

namespace App\Controller;

use App\Repository\AdvertRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AdvertSearchController extends AbstractController
{
    #[Route('/adverts/search', name: 'get_advert_search', methods: ['GET'])]
    public function searchAdverts(AdvertRepository $advertRepository, Request $request): JsonResponse
    {
        $location = $request->query->get('location');
        $startDate = $request->query->get('startDate') ? new \DateTime($request->query->get('startDate')) : null;
        $endDate = $request->query->get('endDate') ? new \DateTime($request->query->get('endDate')) : null;
        $roomsRange = $request->query->get('colivers');
        $services = $request->query->get('services') ? explode(',', $request->query->get('services')) : [];

        $results = $advertRepository->searchAdverts($location, $startDate, $endDate, $roomsRange, $services);

        return $this->json($results);
    }
}
