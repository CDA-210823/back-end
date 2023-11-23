<?php

namespace App\Controller;

use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Repository\ProductRepository;
use App\Service\ImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/api/image')]
class ImageController extends AbstractController
{
    private SerializerInterface $serializer;
    private ImageRepository $imageRepository;
    private ImageService $imageService;
    private SluggerInterface $slugger;
    private ParameterBagInterface $parameterBag;
    private ProductRepository $productRepository;

    /**
     * @param SerializerInterface $serializer
     * @param ImageRepository $imageRepository
     * @param ImageService $imageService
     * @param SluggerInterface $slugger
     * @param ParameterBagInterface $parameterBag
     * @param ProductRepository $productRepository
     */
    public function __construct(SerializerInterface $serializer, ImageRepository $imageRepository, ImageService $imageService, SluggerInterface $slugger, ParameterBagInterface $parameterBag, ProductRepository $productRepository)
    {
        $this->serializer = $serializer;
        $this->imageRepository = $imageRepository;
        $this->imageService = $imageService;
        $this->slugger = $slugger;
        $this->parameterBag = $parameterBag;
        $this->productRepository = $productRepository;
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
    public function add(Request $request): JsonResponse
    {
        $image = new Image();
        if ($this->productRepository->find($request->get('idProduct'))) {
            $image->setProduct($this->productRepository->find($request->get('idProduct')));
            if ($this->imageService->uploadImage($request->files->get('file'), $this->slugger, $image, $this->parameterBag)){
                $this->imageRepository->save($image,true);
                return new JsonResponse(['message' => 'Image added to DB'], Response::HTTP_CREATED);
            }
        }

        return new JsonResponse
        (
            ['message' => 'Fail to add image to DB please try again or check if u have the right format'],
            Response::HTTP_BAD_REQUEST
        );

    }

    #[Route('/edit', name: 'app_image_edit', methods: ["POST"])]
    public function edit(Request $request): JsonResponse
    {
        if ($this->imageRepository->find($request->get('idImage'))){
            $image = $this->imageRepository->find($request->get('idImage'));
            unlink('../upload/'.$image->getName().'.'.$image->getExt());
            if($this->imageService->uploadImage($request->files->get('file'), $this->slugger, $image, $this->parameterBag)) {
                $this->imageRepository->save($image,true);
                return new JsonResponse(['message' => "L'image à bien été modifié"], Response::HTTP_OK);
            }
        }
        return new JsonResponse(['message' => "L'image n'a pas été trouvé ou n'existe plus"], Response::HTTP_NOT_FOUND);
    }

    #[Route('/delete/{id}', name: 'app_image_delete', methods: ["DELETE"])]
    public function delete(int $id): JsonResponse
    {
        $image = $this->imageRepository->find($id);
        if ($image){
            unlink('../upload/'.$image->getName().'.'.$image->getExt());
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

}
