<?php

namespace App\Controller;

use App\Entity\Place;
use App\Repository\CoachRepository;
use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PlaceController extends AbstractController
{
    #[Route('/place', name: 'app_place')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PlaceController.php',
        ]);
    }
/*
    #[Route('/api/places', name: 'places.getAll')]
    public function getAllPlaces(PlaceRepository $repository, SerializerInterface $serializer) : JsonResponse
    {
        $places = $repository->findAll();
        $jsonPlaces = $serializer->serialize($places, 'json');
        return new JsonResponse($jsonPlaces, Response::HTTP_OK, [], true);
    }

    #[Route('/api/places/{idPlace}', name: 'places.get', methods: ['GET'])]
    public function getPlace(int $idPlace, PlaceRepository $repository, SerializerInterface $serializer) : JsonResponse
    {
        $place = $repository->find($idPlace);

        $jsonPlaces = $serializer->serialize($place, 'json');

        return $place ? new JsonResponse($jsonPlaces, Response::HTTP_OK, [], true) : new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);
    }
*/
    #[Route('/api/places/{idPlace}', name: 'places.get', methods: ['GET'])]
    #[ParamConverter("place", options:['id' => 'idPlace'], class: "App\Entity\Place")]
    public function getPlace(Place $place, PlaceRepository $repository, SerializerInterface $serializer) : JsonResponse
    {
        $jsonPlaces = $serializer->serialize($place, 'json',['groups' => 'getPlace']);

        return new JsonResponse($jsonPlaces, Response::HTTP_OK, ['accept' => 'json'], true);
    }


    #[Route('/api/places/{idPlace}', name: 'places.delete', methods: ['DELETE'])]
    #[ParamConverter("place", options:['id' => 'idPlace'], class: "App\Entity\Place")]
    public function deletePlace(Place $place, EntityManagerInterface $entityManager, SerializerInterface $serializer) : JsonResponse
    {
        $entityManager->remove($place);
        $entityManager->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/places', name: 'places.create', methods: ['POST'])]
    public function createPlace(Request $request, EntityManagerInterface $entityManager,UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, CoachRepository $coachRepository, ValidatorInterface $validator) : JsonResponse
    {

        $place = $serializer->deserialize($request->getContent(), Place::class, 'json');
        $place->setStatus('ON');

        $content = $request->toArray();
        $idCoach = $content["idCoach"];
        $place->setCoach($coachRepository->find($idCoach));

        $errors = $validator->validate($place);
        if ($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $entityManager->persist($place);
        $entityManager->flush();

        $location = $urlGenerator->generate('places.get', ['idPlace' => $place->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $jsonPlace = $serializer->serialize($place, "json", ['groups' => 'getPlace']);
        return new JsonResponse($jsonPlace, JsonResponse::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('/api/places/{id}', name: 'places.update', methods: ['PUT'])]
    public function updatePlace(Place $place, Request $request, EntityManagerInterface $entityManager,UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, CoachRepository $CoachRepository) : JsonResponse
    {

        $place = $serializer->deserialize($request->getContent(), Place::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $place]);
        $place->setStatus('ON');

        $location = $urlGenerator->generate('places.get', ['idPlace' => $place->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $entityManager->persist($place);
        $entityManager->flush();
        $jsonPlace = $serializer->serialize($place, "json", ['getPlace']);
        return new JsonResponse($jsonPlace, JsonResponse::HTTP_CREATED, ['Location' => $location], true);
    }
}