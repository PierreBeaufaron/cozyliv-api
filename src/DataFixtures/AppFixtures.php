<?php

namespace App\DataFixtures;

use App\Entity\Advert;
use App\Entity\AdvertImg;
use App\ApiResource\Enum\Country;
use App\Entity\Room;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AppFixtures extends Fixture
{
    private array $dbUsers = [];
    private array $dbOwners = [];
    private array $dbServices = [];
    private array $dbAdvertServices = [];
    private array $dbRoomServices = [];
    private array $dbAdverts = [];
    public function __construct(
        private SerializerInterface $serializer,
        private UserPasswordHasherInterface $hasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        
        $faker = Factory::create('fr_FR');

        // Call others function in order
        $this->loadUsers($manager, $faker);
        $this->loadServices($manager);
        $this->loadAdverts($manager, $faker);
        $this->loadRooms($manager, $faker);
        $this->loadAdvertImg($manager);
        $manager->flush();
    }

    // Users
    public function loadUsers(ObjectManager $manager, Generator $faker): void
    {
        $rawUserContent = file_get_contents(__DIR__ . '/users.json');
        /** @var User[] $users */
        $users = $this->serializer->deserialize($rawUserContent, User::class . '[]', 'json');

        foreach ($users as $user) {
            $user
                ->setBirthDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-70 years')))
                ->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-3 years')));

                $manager->persist($user);
                $this->dbUsers[] = $user;
                $userRoles = $user->getRoles();
                if ($userRoles[0] == "ROLE_OWNER") {
                    $this->dbOwners[] = $user;
                }             
        }    
    }

    // Services
    public function loadServices(ObjectManager $manager): void
    {
        $rawServiceContent = file_get_contents(__DIR__ . '/services.json');
        /** @var Service[] $services */
        $services = $this->serializer->deserialize($rawServiceContent, Service::class . '[]', 'json');

        foreach ($services as $service) {
                $manager->persist($service);
                $this->dbServices[] = $service;
                if ($service->getType() !== 3){
                    $this->dbAdvertServices[] = $service;
                } else {
                    $this->dbRoomServices[] = $service;
                }
        }    
    }

    // Adverts
    public function loadAdverts(ObjectManager $manager, Generator $faker): void
    {
        $rawAdvertContent = file_get_contents(__DIR__ . '/adverts.json');
        /** @var Advert[] $adverts */
        $adverts = $this->serializer->deserialize($rawAdvertContent, Advert::class . '[]', 'json');

        foreach ($adverts as $advert) {
            $advert
                ->setSurfaceArea($faker->numberBetween(65, 250))
                ->setCountry(Country::FRANCE)
                ->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-3 years')))
                ->setOwner($faker->randomElement($this->dbOwners));

                $randomServices = $faker->randomElements($this->dbAdvertServices, $faker->numberBetween(8, 25));

                foreach ($randomServices as $service) {
                    $advert->addService($service);
                }   

                $manager->persist($advert);
                $this->dbAdverts[] = $advert;
        }
    }

    // Rooms
    public function loadRooms(ObjectManager $manager, Generator $faker): void
    {
        $rawRoomContent = file_get_contents(__DIR__ . '/rooms.json');
        /** @var Room[] $rooms */
        $rooms = $this->serializer->deserialize($rawRoomContent, Room::class . '[]', 'json');

        foreach ($rooms as $room) {
            $room
                ->setAdvert($faker->randomElement($this->dbAdverts));

                $randomServices = $faker->randomElements($this->dbRoomServices, $faker->numberBetween(2, 5));
                foreach ($randomServices as $service) {
                    $room->addService($service);
                }

                $manager->persist($room);
        }
    }

    // AdvertImg
    public function loadAdvertImg(ObjectManager $manager): void
    {
        $rawAdvertImgContent = file_get_contents(__DIR__ . '/advertImg.json');
        /** @var AdvertImg[] $advertImgs */
        $advertImgs = $this->serializer->deserialize($rawAdvertImgContent, AdvertImg::class . '[]', 'json');

        $imgIndex = 0;
        for($i = 0; $i < (count($this->dbAdverts) - 1); $i++) {
            for ($j = 0; $j<5; $j++) {
                $advertImg = $advertImgs[$imgIndex];
                $advertImg
                    ->setAdvert($this->dbAdverts[$i]);
    
                $manager->persist($advertImg);
                $imgIndex++;
            }
        }

    } 

}
