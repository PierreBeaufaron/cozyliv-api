<?php

namespace App\Controller;

use App\ApiResource\Enum\Country;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CountryController extends AbstractController
{
    #[Route('/api/countries', name: 'get_countries', methods: ['GET'])]
    public function getCountries(): JsonResponse
    {
        $countries = array_map(fn(Country $c) => $c->value, Country::cases());
        return $this->json($countries);
    }
}
