<?php

namespace App\Security;

use App\Entity\Room;
use App\Entity\Advert;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Doctrine\ORM\EntityManagerInterface;

class RoomVoter extends Voter
{
    public const CREATE = 'ROOM_CREATE';

    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {}

    protected function supports(string $attribute, $subject): bool
    {

        if ($attribute !== self::CREATE) {
            return false;
        }

        if (!$subject instanceof Room) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        // Check subject is Room
        if (!$subject instanceof Room) {
            return false;
        }

        $user = $this->security->getUser();

        // Check there is connected user
        if (!$user) {
            return false;
        }

        // Check user has OWNER role
        if (!$this->security->isGranted('ROLE_OWNER')) {
            return false;
        }

        // Check and transform advert from URI
        $advert = $subject->getAdvert();

        if (is_string($advert)) {
            $advertId = $this->extractAdvertIdFromUri($advert);

            if (!$advertId) {
                return false;
            }

            $advertEntity = $this->entityManager->getRepository(Advert::class)->find($advertId);

            if (!$advertEntity) {
                return false;
            }

            // Set Advert in Room
            $subject->setAdvert($advertEntity);
            $advert = $advertEntity;
        }

        if (!$advert instanceof Advert) {
            return false;
        }

        // Check user is advert's owner
        if ($advert->getOwner() !== $user) {
            return false;
        }

        return true;
    }

    private function extractAdvertIdFromUri(string $uri): ?int
    {
        // Extract advert ID from URI
        if (preg_match('/\/api\/adverts\/(\d+)$/', $uri, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
