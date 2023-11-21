<?php

namespace App\Controller;

use App\Entity\Image;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ImageController extends AbstractController
{
    private SerializerInterface $serializer;
    private ImageRepository $imageRepository;
    private EntityManagerInterface $em;

    /**
     * @param SerializerInterface $serializer
     * @param ImageRepository $imageRepository
     * @param EntityManagerInterface $em
     */
    public function __construct
    (SerializerInterface $serializer, ImageRepository $imageRepository, EntityManagerInterface $em)
    {
        $this->serializer = $serializer;
        $this->imageRepository = $imageRepository;
        $this->em = $em;
    }

    #[Route('/', name: 'app_image_all')]
    public function getAll(): JsonResponse
    {

        return new JsonResponse
        (
            $this->serializer->serialize
            ($this->imageRepository->findAll(), 'json', ['groups'=>'image']), Response::HTTP_OK, [], true
        );
    }

    #[Route('/new', name: 'app_image_new', methods: ["POST"])]
    public function new(Request $request): JsonResponse
    {


        $image = $this->serializer->deserialize($request->getContent(), Image::class, 'json');

        $this->em->persist($image);
        $this->em->flush();

        return new JsonResponse
        ($this->serializer->serialize($image, 'json', ['groups'=>'image']), Response::HTTP_OK, [], true);
    }

    #[Route('/edit/{id}', name: 'app_image_edit', methods: ["PUT"])]
    public function edit(Request $request, Image $image = null): JsonResponse
    {
        if ($image instanceof Image) {
            $updatedImage = $this->serializer->deserialize($request->getContent(),
                Image::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $image]
            );

            $this->em->persist($updatedImage);
            $this->em->flush();
            return new JsonResponse(['message' => 'sucessful edited'],Response::HTTP_OK);
        }
        return new JsonResponse(['message' => 'Error image not found'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/delete/{id}', name: 'app_image_delete', methods: ["DELETE"])]
    public function delete(int $id): JsonResponse
    {
        $image = $this->imageRepository->find($id);
        if ($image){
            $this->em->remove($image);
            $this->em->flush();

            return new JsonResponse(['message' => "L'image' à bien été supprimé"], Response::HTTP_OK);
        }
        else {
            return new JsonResponse
            (['message' => "L'image' n'existe pas ou à déjà été supprimé"],Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/show/{id}', name: 'app_image_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $image = $this->imageRepository->find($id);
        if ($image) {
            return new JsonResponse
            ($this->serializer->serialize($image, 'json', ['groups' => 'image']), Response::HTTP_OK, [], true);
        }

        return new JsonResponse(["message" => "Image not found"], Response::HTTP_NOT_FOUND, []);
    }
}
