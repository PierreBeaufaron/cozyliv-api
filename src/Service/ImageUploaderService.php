<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Intervention\Image\ImageManager;
use Symfony\Component\Filesystem\Filesystem;

class ImageUploaderService
{
    private const UPLOAD_DIRS = [
        'advertImg' => '/uploads/colivings',
        'avatar' => '/uploads/avatars',
    ];

    private ImageManager $imageManager;
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->imageManager = ImageManager::gd();  // Utilisation du driver GD
        $this->filesystem = new Filesystem();
    }

    public function upload(UploadedFile $file, string $type): string
    {
        // Vérification du type d’image
        if (!array_key_exists($type, self::UPLOAD_DIRS)) {
            throw new BadRequestException('Type d\'image non supporté.');
        }

        $uploadDir = __DIR__ . '/../../public' . self::UPLOAD_DIRS[$type];

        // Création du répertoire si nécessaire
        if (!$this->filesystem->exists($uploadDir)) {
            $this->filesystem->mkdir($uploadDir, 0777);
        }

        // Vérification du type MIME
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file->getClientMimeType(), $allowedMimeTypes, true)) {
            throw new BadRequestException('Type de fichier non autorisé. (jpg, jpeg, png, webp seulement)');
        }

        // Génération d’un nom unique pour le fichier
        $fileName = uniqid() . '.' . $file->guessExtension();
        $file->move($uploadDir, $fileName);

        // Création de la version réduite (400x400) avec préfixe `sm_`
        $this->createSmallImage($uploadDir, $fileName);

        return $fileName;
    }

    private function createSmallImage(string $uploadDir, string $fileName): void
    {
        $originalPath = $uploadDir . '/' . $fileName;
        $smallPath = $uploadDir . '/sm_' . $fileName;

        try {
            // Charger l'image originale
            $image = $this->imageManager->read($originalPath);

            // Obtenir la largeur et la hauteur
            $width = $image->width();
            $height = $image->height();

            // Redimensionner proportionnellement
            if ($width > $height) {
                $image = $image->resize(($width*400)/$height, 400); // Ajuste la hauteur à 400px, garde les proportions
            } else {
                $image = $image->resize(400, ($height*400)/$width); // Ajuste la largeur à 400px, garde les proportions
            }

            // Calculer les positions de crop pour centrer
            $cropX = max(0, ($image->width() - 400) / 2);
            $cropY = max(0, ($image->height() - 400) / 2);

            // Crop au centre pour obtenir un carré 400x400
            $image = $image->crop(400, 400, (int)$cropX, (int)$cropY);

            // Sauvegarder l'image réduite
            $image->save($smallPath);
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la création de l’image réduite : ' . $e->getMessage());
        }
    }
}
