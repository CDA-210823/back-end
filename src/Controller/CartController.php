<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/cart')]
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
    public function new(Request $request, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($this->getUser());
        if (!$this->cartRepository->getCartByUser($user->getId())){
            $cart = $this->serializer->deserialize($request->getContent(), Cart::class, 'json');
            $cart->setUser($user);

            $this->cartRepository->save($cart, true);

            return new JsonResponse
            ($this->serializer->serialize($cart, 'json', ['groups'=>'cart']), Response::HTTP_OK, [], true);
        }

        return new JsonResponse(['message' => 'u already have a cart'], Response::HTTP_BAD_REQUEST);

    }




    #[Route('/addProduct', name: 'app_cart_add_product_to_cart', methods: ['POST'])]
    public function addProductToCart
    (Request $request, ProductRepository $productRepository, UserRepository $userRepository): JsonResponse
    {

        $content = $request->toArray();
        $product = $productRepository->find($content['idProduct']);
        $cart = $this->cartRepository->find($content['idCart']);
        if ($cart && $cart->getUser() === $this->getUser() && $product){
            $cart->addProduct($product);
            return new JsonResponse(['message' => 'Produit ajouter au panier avec succès'], Response::HTTP_OK);
        }
        return new JsonResponse
        (
            ['message' => "Le produit que vous souhaiter ajouter n'existe plus ou n'est plus disponible"],
            Response::HTTP_OK
        );
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
