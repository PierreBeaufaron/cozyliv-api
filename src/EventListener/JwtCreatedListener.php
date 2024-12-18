<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class JWTCreatedListener
{
    #[AsEventListener(event: 'JWTCreated')]
    // Écoute l'événement JWTCreated pour ajouter des propriétés au token.
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        // Récupère les données actuelles du payload de token
        $data = $event->getData();

        // Récupère l'utilisateur actuel
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        // Ajouter le prénom et nom
        $data['id'] = $user->getId();
        $data['firstname'] = $user->getFirstname();
        $data['lastname'] = $user->getLastname();

        // Mets à jour le payload avec les nouvelles données
        $event->setData($data);
    }
}
