<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/category')]
class CategoryController extends AbstractController
{
    private $entityManager;
    private $categoryRepository;

    public function __construct(EntityManagerInterface $entityManager, CategoryRepository $categoryRepository)
    {
        $this->entityManager = $entityManager;
        $this->categoryRepository = $categoryRepository;
    }

    #[Route('/', name: "api_category_index", methods: ['GET'])]
    public function getAllCategory(SerializerInterface $serializer): JsonResponse
    {
        $categories = $this->categoryRepository->findAll();
        $jsonContent = $serializer->serialize($categories, 'json');

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: "api_category_show", methods: ['GET'])]
    public function getCategory(Category $category, SerializerInterface $serializer): JsonResponse
    {
        $jsonContent = $serializer->serialize($category, 'json');

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

    #[Route('/new', name: "api_category_new", methods: ['POST'])]
    public function new(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $data = $request->getContent();
        $category = $serializer->deserialize($data, Category::class, 'json');

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $jsonContent = $serializer->serialize($category, 'json');

        return new JsonResponse($jsonContent, Response::HTTP_CREATED, [], true);
    }

    #[Route('/update/{id}', name: "api_category_update", methods: ['PUT'])]
    public function update(Request $request, Category $category, SerializerInterface $serializer): JsonResponse
    {
        $data = $request->getContent();
        $updatedCategory = $serializer->deserialize($data, Category::class, 'json');
        $category->setName($updatedCategory->getName());

        foreach ($updatedCategory->getProducts() as $updatedProduct) {
            $category->addProduct($updatedProduct);
        }

        $this->entityManager->flush();
        $jsonContent = $serializer->serialize($category, 'json');

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

    #[Route('/delete/{id}', name: "api_category_delete", methods: ['DELETE'])]
    public function delete(Category $category): JsonResponse
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
