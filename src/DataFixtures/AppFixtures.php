<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AppFixtures extends Fixture
{
    private array $dbCountries = [];
    private array $dbCities = [];
    private array $dbUsers = [];

    public function __construct(
        private SerializerInterface $serializer,
        private UserPasswordHasherInterface $hasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        
        $faker = Factory::create('fr_FR');

        // Appelle des autres fonctions dans le bon ordre
        $this->loadCountries($manager);
        $this->loadCities($manager);
        $this->loadUsers($manager, $faker);

        $manager->flush();
    }

    private function loadCountries(ObjectManager $manager): void
    {
        // Countries
        $rawCountries = file(__DIR__ . '/countries.txt');
        $countries = array_map(fn (string $c) => trim($c), $rawCountries);

        foreach ($countries as $countryName) {
            $country = new Country();
            $country->setName($countryName);

            $manager->persist($country);
            $this->dbCountries[] = $country;
        }
    }

    private function loadCities(ObjectManager $manager): void
    {
        // Cities
        $rawCities = file_get_contents(__DIR__ . '/cities.json');
        /** @var City[] $cities */
        $cities = $this->serializer->deserialize($rawCities, City::class . '[]', 'json');

        foreach ($cities as $city) {
            $city->setCountry($this->dbCountries[7]);

            $manager->persist($city);
            $this->dbCities[] = $city;
        }
    }

    public function loadUsers(ObjectManager $manager, Generator $faker): void
    {
        // Users
        $rawUserContent = file_get_contents(__DIR__ . '/users.json');
        /** @var User[] $users */
        $users = $this->serializer->deserialize($rawUserContent, User::class . '[]', 'json');

        foreach ($users as $user) {
            $user
                ->setBirthDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-70 years')))
                ->setMemberSince(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-3 years')))
                ->setPassword($this->hasher->hashPassword($user, $user->getPassword()))
                ->setCity($faker->randomElement($this->dbCities));

                $manager->persist($user);
                $this->dbUsers[] = $user;
        }    
    }

}
