<?php

namespace App\Controller;

use App\Service\ImageUploaderService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/api/upload', name: 'upload_image', methods: ['POST'])]
class ImageUploadController
{
    public function __construct(private ImageUploaderService $imageUploader) {}

    public function __invoke(Request $request): Response
    {
        $file = $request->files->get('toUploadImgs_');
        if (!$file) {
            return new JsonResponse(['error' => 'Aucun fichier envoyé.'], Response::HTTP_BAD_REQUEST);
        }

        $type = $request->get('type', 'advertImg');

        try {
            $fileName = $this->imageUploader->upload($file, $type);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['url' => $fileName], Response::HTTP_OK);
    }
}
