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

    public function onOpen(ConnectionInterface $conn)
    {
        // Ajoute la nouvelle connexion à la collection de clients.
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        echo("Data: $data");
        echo("Message: $msg");

        // if (!$data) {
        //     $from->send(json_encode(['error' => 'Invalid message format']));
        //     return;
        // }

        // if (isset($data['type']) && $data['type'] === 'auth') {
        //     // Handle authentication
        //     $this->authenticate($from, $data['token']);
        //     return;
        // }

        // // Check if the user is authenticated
        // if (!isset($this->clients[$from]['user'])) {
        //     $from->send(json_encode(['error' => 'Unauthorized']));
        //     return;
        // }

        // Gérer le message entrant
        // $this->handleChatMessage($from, $data);
    }

    // private function authenticate(ConnectionInterface $conn, string $token): void
    // {
    //     try {
    //         // Decode the JWT token to get the payload
    //         $payload = $this->jwtEncoder->decode($token);
    //         $email = $payload['username'] ?? null;

    //         if (!$email) {
    //             $conn->send(json_encode(['error' => 'Invalid token payload']));
    //             $conn->close();
    //             return;
    //         }

    //         // Retrieve the user from the database
    //         $user = $this->userRepository->findOneBy(['email' => $email]);

    //         if (!$user) {
    //             $conn->send(json_encode(['error' => 'User not found']));
    //             $conn->close();
    //             return;
    //         }

    //         // Store the user in the clients storage associated with the connection
    //         $this->clients[$conn] = ['user' => $user];

    //         $conn->send(json_encode(['success' => 'Authenticated']));
    //     } catch (\Exception $e) {
    //         $conn->send(json_encode(['error' => 'Authentication failed']));
    //         $conn->close();
    //     }
    // }

    private function handleChatMessage(ConnectionInterface $from, array $data): void
    {
        if (!isset($data['content'], $data['receiver_id'])) {
            $from->send(json_encode(['error' => 'Invalid message data']));
            return;
        }

        // Retrouve l'expéditeur dans la collection de clients.
        $sender = $this->clients[$from]['user'];

        // Créer une nouvelle instance de message
        $message = new Message();
        $message->setContent($data['content']);
        $message->setCreatedAt(new \DateTimeImmutable());
        $message->setSender($sender);

        // Trouver le destinataire
        $recipient = $this->userRepository->find($data['receiver_id']);
        if (!$recipient) {
            $from->send(json_encode(['error' => 'Receiver not found']));
            return;
        }

        $message->setRecipient($recipient);

        // Persist the message to the database
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        // Serialize the message for sending
        $serializedMessage = $this->serializer->serialize($message, 'json', ['groups' => ['messages:read']]);

        // Send the message to the receiver if connected
        foreach ($this->clients as $client) {
            $clientUser = $this->clients[$client]['user'] ?? null;
            if ($clientUser && $clientUser->getId() === $recipient->getId()) {
                $client->send($serializedMessage);
            }
        }

        // Optionally, acknowledge message sent to the sender
        $from->send(json_encode(['success' => 'Message sent']));
    }

    public function onClose(ConnectionInterface $conn)
    {
        // Remove the connection from the clients storage
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        // Close the connection on error
        $conn->close();
    }

}