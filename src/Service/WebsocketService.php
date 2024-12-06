<?php

namespace App\Service;

use App\Entity\Message;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Symfony\Component\Serializer\SerializerInterface;

class WebsocketService implements MessageComponentInterface
{   
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private SerializerInterface $serializer;
    private JWTEncoderInterface $jwtEncoder;
    private \SplObjectStorage $clients;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        SerializerInterface $serializer,
        JWTEncoderInterface $jwtEncoder
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->jwtEncoder = $jwtEncoder;
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $from)
    {
        // Ajoute la nouvelle connexion à la collection de clients.
        $this->clients->attach($from);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {

        $msg = json_decode($msg, true);
        if(!isset($msg['type'])) {
            $from->send(json_encode([
                'type' => 'error',
                'data' => 'Key \'type\' is required'
            ]));
            return;
        }

        if ($msg['type'] === 'authentication') {
            $this->handleAuthentication($from, $msg['data']);
            return;
        }

        // Vérifier que le user est authentifié
        if (!isset($this->clients[$from]['user'])) {
            $from->send(json_encode([
                'type' => 'error',
                'data' => 'Unauthorized'

            ]));
            return;
        }


        if ($msg['type'] === 'conversation.message.created') {
            // Récup de l'expéditeur et du contenu
            $this->handleChatMessage($from, $msg['data']);
            return;
        }

    }

    private function handleAuthentication(ConnectionInterface $from, string $token): void
    {
        try {
            // Decoder le JWT
            $payload = $this->jwtEncoder->decode($token);
            $email = $payload['username'] ?? null; // Adapter le nom de la clé si nécessaire

            if (!$email) {
                $from->send(json_encode([
                    'type' => 'error',
                    'data' => 'Invalid token payload'
    
                ]));
    
                $from->close();
                return;
            }

            // Retrouver le user dans la BDD
            $user = $this->userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $from->send(json_encode([
                    'type' => 'error',
                    'data' => 'User not found'
    
                ]));
    
                $from->close();
                return;
            }
            // Stocker le user avec sa connexion 
            $this->clients[$from] = ['user' => $user];

        } catch (\Exception $e) {
            $from->send(json_encode([
                'type' => 'error',
                'data' => 'Authentication failed'

            ]));
            $from->close();
        }
    }

    // Gestion des message
    private function handleChatMessage(ConnectionInterface $from, array $data): void
    {
        if (!isset($data['content'], $data['recipientId'], $data['senderId'])) {
            $from->send(json_encode([
                'type' => 'error',
                'data' => 'Invalid message data'

            ]));
            return;
        }

        // Créer une nouvelle instance de message
        $message = new Message();
        $message->setContent($data['content']);
        $message->setCreatedAt(new \DateTimeImmutable());

        // Retrouve l'expéditeur dans la collection de clients.
        $sender = $this->userRepository->find($data['senderId']);
        if (!$sender) {
            $from->send(json_encode([
                'type' => 'error',
                'data' => 'Sender not found'
            ]));
            return;

        }

        $message->setSender($sender);

        // Trouver le destinataire
        $recipient = $this->userRepository->find($data['recipientId']);
        if (!$recipient) {
            $from->send(json_encode([
                'type' => 'error',
                'data' => 'Receiver not found'
            ]));
            return;
        }

        $message->setRecipient($recipient);
        
        // Persister les message dans la BDD
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        // Serialiser le message pour l'envoyer
        $serializedMessage = $this->serializer->serialize($message, 'json', ['groups' => ['messages:read']]);
        
        // Envoyer le message si le destinataire est connecté
        foreach ($this->clients as $client) {
            $clientUser = $this->clients[$client]['user'] ?? null;
            if ($clientUser && $clientUser->getId() === $recipient->getId()) {
                $client->send(json_encode([
                    'type' => 'conversation.message.added', 
                    'data' => $serializedMessage
                ]));        
            }
        }

        // Optionnel,message de reconnaissance envoyé à l'expéditeur
        $from->send(json_encode([
            'type' => 'conversation.message.added', 
            'data' => $serializedMessage
        ]));
    }

    public function onClose(ConnectionInterface $from)
    {
        // Supprimer la connexion du local storage
        $this->clients->detach($from);
    }

    public function onError(ConnectionInterface $from, \Exception $e)
    {
        // Fermer la connexion si erreur
        $from->close();
    }

}