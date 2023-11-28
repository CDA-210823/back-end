<?php

namespace App\Controller;

use App\Entity\CartProduct;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CartProductController extends AbstractController
{
    private SerializerInterface $serializer;
    private ProductRepository $productRepository;
    private EntityManagerInterface $em;
    private CartRepository $cartRepository;

    /**
     * @param SerializerInterface $serializer
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $em
     * @param CartRepository $cartRepository
     */
    public function __construct
    (
        SerializerInterface $serializer,
        ProductRepository $productRepository,
        EntityManagerInterface $em,
        CartRepository $cartRepository,
    )
    {
        $this->serializer = $serializer;
        $this->productRepository = $productRepository;
        $this->em = $em;
        $this->cartRepository = $cartRepository;
    }

    #[Route('/add', name: 'app_productcart_add' ,methods: 'POST')]
    public function add(Request $request): JsonResponse
    {
        $content = $request->toArray();

        $productCart = $this->serializer->deserialize($request->getContent(), CartProduct::class, 'json');

        $product = $this->productRepository->find($content['productId']);
        $product->addCartProduct($productCart);

        $cart = $this->cartRepository->find($content['cartId']);
        $cart->addCartProduct($productCart);

        $this->em->persist($productCart);
        $this->em->persist($product);
        $this->em->persist($cart);
        $this->em->flush();

        return new JsonResponse
        ($this->serializer->serialize($productCart, 'json', ["groups" => "product"]), Response::HTTP_OK, [], true);
    }
}
