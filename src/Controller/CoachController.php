<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Repository\CoachRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class CoachController extends AbstractController
{
    #[Route('/coach', name: 'app_coach')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CoachController.php',
        ]);
    }

    #[Route('/api/coachs', name: 'coach.getAll', methods: ['GET'])]
    public function getAllCoachs(CoachRepository $repository, SerializerInterface $serializer) : JsonResponse
    {
        $coach = $repository->findAll();
        $jsonCoachs = $serializer->serialize($coach, 'json',['groups' => 'getPlace']);
        return new JsonResponse($jsonCoachs, Response::HTTP_OK, [], true);
    }

    #[Route('/api/coachs/{idCoach}', name: 'coach.get', methods: ['GET'])]
    #[ParamConverter("coach", options:['id' => 'idCoach'], class: "App\Entity\Coach")]
    public function getCoach(
        Coach $coach,
        CoachRepository $repository,
        SerializerInterface $serializer
    ) : JsonResponse
    {
        $jsonPlaces = $serializer->serialize($coach, 'json',['groups' => 'getCoach']);

        return new JsonResponse($jsonPlaces, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/coachs/{idCoach}', name: 'coach.delete', methods: ['DELETE'])]
    #[ParamConverter("coach", options:['id' => 'idCoach'], class: "App\Entity\Coach")]
    public function deleteCoach(Coach $coach, EntityManagerInterface $entityManager) : JsonResponse
    {
        $entityManager->remove($coach);
        $entityManager->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/coachs', name: 'coach.create', methods: ['POST'])]
    public function createCoach(Request $request, EntityManagerInterface $entityManager,UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, CoachRepository $coachRepository) : JsonResponse
    {

        $coach = $serializer->deserialize($request->getContent(), Coach::class, 'json');
        $coach->setStatu('ON');

        $entityManager->persist($coach);
        $entityManager->flush();

        $location = $urlGenerator->generate('coach.get', ['idCoach' => $coach->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $jsonPlace = $serializer->serialize($coach, "json", ['groups' => 'getCoach']);
        return new JsonResponse($jsonPlace, JsonResponse::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('/api/coachs/{id}', name: 'coach.update', methods: ['PUT'])]
    public function updateCoach(Coach $coach, Request $request, EntityManagerInterface $entityManager,UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer){
        $coach = $serializer->deserialize($request->getContent(), Coach::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $coach]);
        $coach->setStatu('ON');
        
        $location = $urlGenerator->generate('coach.get', ['idCoach' => $coach->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $entityManager->persist($coach);
        $entityManager->flush();
        $jsonPlace = $serializer->serialize($coach, "json", ['getCoach']);
        return new JsonResponse($jsonPlace, JsonResponse::HTTP_CREATED, ['Location' => $location], true);
    }
}
