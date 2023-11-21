<?php
namespace App\Controller;
use App\Entity\Address;
use App\Repository\AddressRepository;
use App\Service\ValidatorErrorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
#[Route('/api/address', name: 'api_address')]
class AddressController extends AbstractController
{
	private SerializerInterface $serializer;
	private AddressRepository $addressRepository;
	private ValidatorErrorService $validatorService;
	private EntityManagerInterface $em;
	public function __construct(SerializerInterface $serializer, AddressRepository $addressRepository, ValidatorErrorService $validatorService, EntityManagerInterface $em) {
		$this->serializer = $serializer;
		$this->addressRepository = $addressRepository;
		$this->validatorService = $validatorService;
		$this->em = $em;
	}
	#[Route('/', name: 'app_addresses', methods: ['GET'])]
    public function index(): JsonResponse
    {
		$addresses = $this->addressRepository->findAll();
		$jsonAddresses = $this->serializer->serialize($addresses, 'json', ['groups' => 'address:list']);
		return new JsonResponse($jsonAddresses, Response::HTTP_OK, [], true);
    }
	#[Route('/new', name: 'app_address_new', methods: ['POST'])]
	public function new(Request $request): JsonResponse
	{
		$address = $this->serializer->deserialize($request->getContent(), Address::class, 'json');
		$errors = $this->validatorService->getErrors($address);
		if (count($errors) > 0) {
			return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
		}
		$this->em->persist($address);
		$this->em->flush();
		$jsonAddress = $this->serializer->serialize($address, 'json', ['groups' => 'address:list']);
		return new JsonResponse($jsonAddress, Response::HTTP_CREATED, [], true);
	}
	#[Route('/edit/{id}', name: 'app_address_edit', methods: ['PUT'])]
	public function edit(Request $request, Address $address = null): JsonResponse
	{
		if ($address instanceof Address) {
			$updatedAddress = $this->serializer->deserialize($request->getContent(), Address::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $address]);
			$errors = $this->validatorService->getErrors($address);
			if (count($errors) > 0) {
				return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
			}
			$this->em->persist($updatedAddress);
			$this->em->flush();
			$jsonAddress = $this->serializer->serialize($updatedAddress, 'json', ['groups' => 'address:list']);
			return new JsonResponse($jsonAddress, Response::HTTP_OK, [], true);
		}
		return new JsonResponse(['message' => "L'adresse n'a pas été trouvé"], Response::HTTP_NOT_FOUND);
	}
	#[Route('/delete/{id}', name: 'app_address_delete', methods: ['DELETE'])]
	public function delete(int $id): JsonResponse
	{
		$address = $this->addressRepository->find($id);
		if ($address instanceof Address) {
			$this->em->remove($address);
			$this->em->flush();
			return new JsonResponse(null, Response::HTTP_NO_CONTENT);
		}
		return new JsonResponse(['message' => "L'adresse n'a pas été"], Response::HTTP_NOT_FOUND);
	}
}
