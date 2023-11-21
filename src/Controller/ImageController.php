<?php

namespace App\Controller;

use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/api/image')]
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

    #[Route('/', name: 'app_image_all', methods: ['GET'])]
    public function getAll(): JsonResponse
    {

        return new JsonResponse
        (
            $this->serializer->serialize
            ($this->imageRepository->findAll(), 'json', ['groups'=>'image']), Response::HTTP_OK, [], true
        );
    }

    #[Route('/add', name: 'app_image_new', methods: ["POST"])]
    public function add
    (
        Request $request,
        SluggerInterface $slugger,
        ParameterBagInterface $parameterBag,
        ProductRepository $productRepository,
    ): JsonResponse
    {
        $file = $this->serializer->deserialize($request->getContent(), Image::class, 'json');

        $content = $request->toArray();
        if ($productRepository->find($content['idProduct'])) {
            $file->setProduct($productRepository->find($content['idProduct']));
        }
        $this->uploadImage($content['file'], $slugger, $file, $parameterBag);

        return new JsonResponse(['message' => 'Image added to DB'], Response::HTTP_CREATED);
    }


    #[Route('/edit/{id}', name: 'app_image_edit', methods: ["PUT"])]
    public function edit
    (Image $image, Request $request, SluggerInterface $slugger, ParameterBagInterface $parameterBag): JsonResponse
    {
        if ($image){
            $parameterBag->remove($image->getName());
            $file = $this->serializer->deserialize($request->getContent(), Image::class, 'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $image]
            );

            $content = $request->toArray();
            $this->uploadImage($content['file'], $slugger, $file, $parameterBag);

            return new JsonResponse(['message' => "L'image à bien été modifié"], Response::HTTP_OK);
        }
        return new JsonResponse(['message' => "L'image n'a pas été trouvé ou n'existe plus"], Response::HTTP_NOT_FOUND);
    }

    #[Route('/delete/{id}', name: 'app_image_delete', methods: ["DELETE"])]
    public function delete(int $id, ParameterBagInterface $parameterBag): JsonResponse
    {
        $image = $this->imageRepository->find($id);
        if ($image){
            $parameterBag->remove($image->getName());
            $this->imageRepository->remove($image, true);
            return new JsonResponse(['message' => "L'image' à bien été supprimé"], Response::HTTP_OK);
        }

        return new JsonResponse
        (['message' => "L'image' n'existe pas ou à déjà été supprimé"],Response::HTTP_BAD_REQUEST);
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


    public function uploadImage
    (
        $imageName,
        SluggerInterface $slugger,
        Image $imageEntity,
        ParameterBagInterface $container
    )
    : void
    {
        $file = $imageName->getData();

        if ($file) {
            $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFileName = $slugger->slug($originalFileName);
            $ext = $file->guessExtension();
            $newFileName = $safeFileName . '-' . uniqid() . $ext;
            $imageEntity->setName($newFileName);
            $imageEntity->setPath('/upload/'.$imageEntity->getName());


            if (!$ext) {
                $ext = 'bin';
            }

            $file->move($container->get('upload.directory'), $newFileName . '.' . $ext);
            $this->imageRepository->save($imageEntity, true);
        }
    }
}
