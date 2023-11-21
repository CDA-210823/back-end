<?php

namespace App\Controller;

use App\Entity\Command;
use App\Repository\CommandRepository;
use App\Service\CommandService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/command')]
class CommandController extends AbstractController
{
    private $entityManager;
    private $commandRepository;
    private $commandService;

    public function __construct(EntityManagerInterface $entityManager, CommandRepository $commandRepository, CommandService $commandService)
    {
        $this->entityManager = $entityManager;
        $this->commandRepository = $commandRepository;
        $this->commandService = $commandService;
    }

    #[Route('/', name: "api_command_index", methods: ['GET'])]
    public function index(SerializerInterface $serializer): JsonResponse
    {
        $commands = $this->commandRepository->findAll();
        $jsonContent = $serializer->serialize($commands, 'json');

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: "api_command_show", methods: ['GET'])]
    public function show(Command $command, SerializerInterface $serializer): JsonResponse
    {
        $jsonContent = $serializer->serialize($command, 'json');

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

    #[Route('/new', name: "api_command_new", methods: ['POST'])]
    public function new(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $data = $request->getContent();
        $command = $serializer->deserialize($data, Command::class, 'json');

        $this->commandService->manageCommand($command);

        $this->entityManager->persist($command);
        $this->entityManager->flush();

        $jsonContent = $serializer->serialize($command, 'json');

        return new JsonResponse($jsonContent, Response::HTTP_CREATED, [], true);
    }

    #[Route('/update/{id}', name: "api_command_update", methods: ['PUT'])]
    public function update(Request $request, Command $command, SerializerInterface $serializer): JsonResponse
    {
        $data = $request->getContent();
        $updatedCommand = $serializer->deserialize($data, Command::class, 'json');

        $command->setNumber($updatedCommand->getNumber());
        if ($updatedCommand->getStatus() !== null) {
            $command->setStatus($updatedCommand->getStatus());
        }

        $command->setTotalPrice($updatedCommand->getTotalPrice());
        $command->getCommandProducts()->clear();
        foreach ($updatedCommand->getCommandProducts() as $updatedCommandProduct) {
            $command->addCommandProduct($updatedCommandProduct);
        }

        $this->entityManager->flush();
        $jsonContent = $serializer->serialize($command, 'json');

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }



    #[Route('/delete/{id}', name: "api_command_delete", methods: ['DELETE'])]
    public function delete(Command $command): JsonResponse
    {
        $this->entityManager->remove($command);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
