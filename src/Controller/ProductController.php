<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\ImageService;
use App\Service\ValidatorErrorService;
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


#[Route('/api/product')]
class ProductController extends AbstractController
{
    private SerializerInterface $serializer;
    private ProductRepository $productRepository;
    private EntityManagerInterface $em;

    /**
     * @param SerializerInterface $serializer
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(SerializerInterface $serializer, ProductRepository $productRepository, EntityManagerInterface $em)
    {
        $this->serializer = $serializer;
        $this->productRepository = $productRepository;
        $this->em = $em;
    }

    #[Route("/", name: 'app_product_getall', methods: ['GET'])]
    public function getAll():JsonResponse
    {
        return new JsonResponse(
            $this->serializer->serialize(
                $this->productRepository->findAll(),
                'json',
                ['groups'=>'product']),
                Response::HTTP_OK,
                [],
                true);
    }

    #[Route("/new", name: 'app_product_new', methods: ['POST'])]
    public function new
    (
        Request $request,
        ValidatorErrorService $validatorService,
        ImageService $imageService,
        SluggerInterface $slugger,
        ParameterBagInterface $container,
        CategoryRepository $categoryRepository,
    ): JsonResponse
    {
        $category = $categoryRepository->find($request->get('category'));
        $product= (new Product())
            ->setPrice($request->get('price'))
            ->setStock($request->get('stock'))
            ->setName($request->get('name'))
            ->setDescription($request->get('description'))
            ->setCategory($category);


        $image = new Image();
        if ($imageService->uploadImage($request->files->get('image'), $slugger, $image, $container)){
            $product->addImageProduct($image);
        }

        $errors = $validatorService->getErrors($product);
        if (count($errors) > 0) {
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }
        $this->em->persist($product);
        $this->em->persist($image);
        $this->em->flush();

        return new JsonResponse(["message" => "product added"]);
    }

    #[Route('/edit/{id}', name: 'app_product_edit', methods: ["PUT"])]
    public function edit
    (Request $request,ValidatorErrorService $validator, Product $product = null): JsonResponse
    {
        if ($product instanceof Product) {
            $updatedProduct = $this->serializer->deserialize(
                $request->getContent(),
                Product::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $product]
            );
            $errors = $validator->getErrors($product);
            if (count($errors) > 0){
                return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
            }

            $this->em->persist($updatedProduct);
            $this->em->flush();
            return new JsonResponse(['message' => 'Produit mis à jour'], Response::HTTP_OK);
        }
        return new JsonResponse(['message' => "Le produit n'a pas été trouvé"], Response::HTTP_NOT_FOUND);
    }

    #[Route('/show/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if ($product) {
            return new JsonResponse($this->serializer->serialize($product, 'json'), Response::HTTP_OK, [], true);
        }

        return new JsonResponse(["message" => "Le produit n'a pas été trouvé"], Response::HTTP_NOT_FOUND);
    }

    #[Route('/delete/{id}', name: 'app_product_delete', methods: ["DELETE"])]
    public function delete(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if ($product) {
            $this->em->remove($product);
            $this->em->flush();

            return new JsonResponse(['message' => "Le produit a bien été supprimé", Response::HTTP_OK]);
        }
        return new JsonResponse
        (['message' => "Le produit n'a pas été trouvé"], Response::HTTP_BAD_REQUEST);
    }

}
