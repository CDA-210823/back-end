<?php
	namespace App\Controller;
	use App\Entity\Opinion;
	use App\Repository\OpinionRepository;
    use App\Repository\ProductRepository;
    use App\Service\ValidatorErrorService;
	use Doctrine\ORM\EntityManagerInterface;
	use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
	use Symfony\Component\HttpFoundation\JsonResponse;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Routing\Annotation\Route;
	use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
	use Symfony\Component\Serializer\SerializerInterface;

	#[Route('/api/opinion', name: 'api_address')]
	class OpinionController extends AbstractController
	{
		private SerializerInterface $serializer;
		private OpinionRepository $opinionRepository;
		private ValidatorErrorService $validatorService;
		private EntityManagerInterface $em;
		public function __construct(SerializerInterface $serializer, OpinionRepository $opinionRepository, ValidatorErrorService $validatorService, EntityManagerInterface $em) {
			$this->serializer = $serializer;
			$this->opinionRepository = $opinionRepository;
			$this->validatorService = $validatorService;
			$this->em = $em;
		}
		#[Route('/', name: 'app_opinions', methods: ['GET'])]
		public function index(): JsonResponse
		{
			$opinions = $this->opinionRepository->findAll();
			$jsonOpinions = $this->serializer->serialize($opinions, 'json', ['groups' => 'opinion:list']);
			return new JsonResponse($jsonOpinions, Response::HTTP_OK, [], true);
		}
		#[Route('/new/{id}', name: 'app_opinion_new', methods: ['POST'])]
		public function new(Request $request, int $id, ProductRepository $productRepository): JsonResponse
		{
            $product = $productRepository->find($id);
            if ($product) {
                $opinion = $this->serializer->deserialize($request->getContent(), Opinion::class, 'json');
                $opinion->setProduct($product);
                $errors = $this->validatorService->getErrors($opinion);
                if (count($errors) > 0) {
                    return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
                }
                $this->em->persist($opinion);
                $this->em->flush();
                $jsonOpinion = $this->serializer->serialize($opinion, 'json', ['groups' => 'opinion:list']);
                return new JsonResponse($jsonOpinion, Response::HTTP_CREATED, [], true);
            }
            return new JsonResponse([
                'message' =>"Erreur lors de l'ajout"
            ]);

		}
		#[Route('/edit/{id}', name: 'app_opinion_edit', methods: ['PUT'])]
		public function edit(Request $request, Opinion $opinion = null): JsonResponse
		{
			if ($opinion instanceof Opinion) {
				$updatedOpinion = $this->serializer->deserialize($request->getContent(), Opinion::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $opinion]);
				$errors = $this->validatorService->getErrors($opinion);
				if (count($errors) > 0) {
					return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
				}
				$this->em->persist($updatedOpinion);
				$this->em->flush();
				$jsonOpinion = $this->serializer->serialize($updatedOpinion, 'json', ['groups' => 'opinion:list']);
				return new JsonResponse($jsonOpinion, Response::HTTP_OK, [], true);
			}
			return new JsonResponse(['message' => "L'avis n'a pas été trouvé"], Response::HTTP_NOT_FOUND);
		}
		#[Route('/delete/{id}', name: 'app_opinion_delete', methods: ['DELETE'])]
		public function delete(int $id): JsonResponse
		{
			$opinion = $this->opinionRepository->find($id);
			if ($opinion instanceof Opinion) {
				$this->em->remove($opinion);
				$this->em->flush();
				return new JsonResponse(null, Response::HTTP_NO_CONTENT);
			}
			return new JsonResponse(['message' => "L'avis n'a pas été trouvé"], Response::HTTP_NOT_FOUND);
		}
	}
