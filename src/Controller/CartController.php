<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class CartController extends AbstractController
{
    private SerializerInterface $serializer;
    private CartRepository $cartRepository;
    private EntityManagerInterface $em;

    /**
     * @param SerializerInterface $serializer
     * @param CartRepository $cartRepository
     * @param EntityManagerInterface $em
     */
    public function __construct
    (SerializerInterface $serializer, CartRepository $cartRepository, EntityManagerInterface $em)
    {
        $this->serializer = $serializer;
        $this->cartRepository = $cartRepository;
        $this->em = $em;
    }

    #[Route('/', name: 'app_cart_all')]
    public function getAll(): JsonResponse
    {

        return new JsonResponse
        (
            $this->serializer->serialize
            ($this->cartRepository->findAll(), 'json', ['groups'=>'cart']), Response::HTTP_OK, [], true
        );
    }

    #[Route('/new', name: 'app_cart_new', methods: ["POST"])]
    public function new(Request $request): JsonResponse
    {


        $cart = $this->serializer->deserialize($request->getContent(), Cart::class, 'json');
        $cart->setUser($this->getUser());

        $this->em->persist($cart);
        $this->em->flush();

        return new JsonResponse
        ($this->serializer->serialize($cart, 'json', ['groups'=>'cart']), Response::HTTP_OK, [], true);
    }

    #[Route('/edit/{id}', name: 'app_cart_edit', methods: ["PUT"])]
    public function edit(Request $request, Cart $cart = null): JsonResponse
    {
        if ($cart instanceof Cart) {
            $updatedCart = $this->serializer->deserialize($request->getContent(),
                Cart::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $cart]
            );

            $this->em->persist($updatedCart);
            $this->em->flush();
            return new JsonResponse(['message' => 'sucessful edited'],Response::HTTP_OK);
        }
        return new JsonResponse(['message' => 'Error cart not found'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/delete/{id}', name: 'app_cart_delete', methods: ["DELETE"])]
    public function delete(int $id): JsonResponse
    {
        $cart = $this->cartRepository->find($id);
        if ($cart){
            $this->em->remove($cart);
            $this->em->flush();

            return new JsonResponse(['message' => "Le panier à bien été supprimé"], Response::HTTP_OK);
        }
        else {
            return new JsonResponse
            (['message' => "Le panier n'existe pas ou à déjà été supprimé"],Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/show/{id}', name: 'app_cart_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $cart = $this->cartRepository->find($id);
        if ($cart) {
            return new JsonResponse
            ($this->serializer->serialize($cart, 'json', ['groups' => 'cart']), Response::HTTP_OK, [], true);
        }

        return new JsonResponse(["message" => "Cart not found"], Response::HTTP_NOT_FOUND, []);
    }

}
