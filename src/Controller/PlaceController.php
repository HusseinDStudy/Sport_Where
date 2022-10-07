<?php

namespace App\Controller;

use App\Entity\Place;
use App\Repository\PlaceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

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

    #[Route('/api/places', name: 'places.getAll')]
    public function getAllPlaces(PlaceRepository $repository, SerializerInterface $serializer) : JsonResponse
    {
        $places = $repository->findAll();
        $jsonPlaces = $serializer->serialize($places, 'json');
        return new JsonResponse($jsonPlaces, Response::HTTP_OK, [], true);
    }
/*
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
        $jsonPlaces = $serializer->serialize($place, 'json');

        return new JsonResponse($jsonPlaces, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}