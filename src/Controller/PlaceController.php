<?php

namespace App\Controller;

use App\Entity\Place;
use App\Repository\CoachRepository;
use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\Serializer\SerializerInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Message;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

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

        /**
     * Retourne la liste des lieux paginés
     *
     * @param PlaceRepository $repository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/places', name: 'places.getAll', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Erreur vous n\'avez pas accès à ceci !')]
    public function getAllPlaces(PlaceRepository $repository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache) : JsonResponse
    {
        $idCache = 'getAllPlaces';
        $jsonPlaces = $cache->get($idCache, function (ItemInterface $item) use ($request, $serializer, $repository){
            $item->tag("placeCache");
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 5);
            $places = $repository->findAllCustom($page, $limit);
            $context = SerializationContext::create()->setGroups(['getPlace']);
            return $serializer->serialize($places, 'json', $context);
        });
        //$jsonPlaces = $serializer->serialize($places, 'json',['groups' => 'getPlace']);
        return new JsonResponse($jsonPlaces, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Retourne un lieu par son id
     *
     * @param Place $place
     * @param PlaceRepository $repository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/places/{idPlace}', name: 'places.get', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Erreur vous n\'avez pas accès à ceci !')]
    #[ParamConverter("place", options:['id' => 'idPlace'], class: "App\Entity\Place")]
    public function getPlace(Place $place, PlaceRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache) : JsonResponse
    {
        $idCache = 'getPlace';
        $jsonPlaces = $cache->get($idCache, function (ItemInterface $item) use ($place, $serializer){
            $item->tag("placeCache");
            $context = SerializationContext::create()->setGroups(['getPlace']);
            return $serializer->serialize($place, 'json', $context);
        });
        //$jsonPlaces = $serializer->serialize($place, 'json',['groups' => 'getPlace']);
        return new JsonResponse($jsonPlaces, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Supprime un lieux par son id
     *
     * @param Place $place
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/places/{idPlace}', name: 'places.delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Erreur vous n\'avez pas accès à ceci !')]
    #[ParamConverter("place", options:['id' => 'idPlace'], class: "App\Entity\Place")]
    public function deletePlace(Place $place, EntityManagerInterface $entityManager, SerializerInterface $serializer, TagAwareCacheInterface $cache) : JsonResponse
    {
        $cache->invalidateTags(["placeCache"]);
        $entityManager->remove($place);
        $entityManager->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Creer un lieux
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param SerializerInterface $serializer
     * @param CoachRepository $coachRepository
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/places', name: 'places.create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Erreur vous n\'avez pas accès à ceci !')]
    public function createPlace(Request $request, EntityManagerInterface $entityManager,UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, CoachRepository $coachRepository, ValidatorInterface $validator, TagAwareCacheInterface $cache) : JsonResponse
    {
        $cache->invalidateTags(["placeCache"]);
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
        $context = SerializationContext::create()->setGroups(['getPlace']);
        $jsonPlace = $serializer->serialize($place, "json", $context);
        return new JsonResponse($jsonPlace, JsonResponse::HTTP_CREATED, ['Location' => $location], true);
    }

    /**
     * Modifie un lieu par son id
     *
     * @param Place $place
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param SerializerInterface $serializer
     * @param CoachRepository $CoachRepository
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/places/{id}', name: 'places.update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Erreur vous n\'avez pas accès à ceci !')]
    public function updatePlace(Place $place, Request $request, EntityManagerInterface $entityManager,UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, CoachRepository $CoachRepository, ValidatorInterface $validator, TagAwareCacheInterface $cache) : JsonResponse
    {
        $cache->invalidateTags(["placeCache"]);
        //$place = $serializer->deserialize($request->getContent(), Place::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $place]);
        $updatedPlace = $serializer->deserialize($request->getContent(), Place::class, 'json');

        $place->setPlaceName($updatedPlace->getPlaceName() ? $updatedPlace->getPlaceName() : $place->getPlaceName() );
        $place->setPlaceAddress($updatedPlace->getPlaceAddress() ? $updatedPlace->getPlaceAddress() : $place->getPlaceAddress() );
        $place->setPlaceCity($updatedPlace->getPlaceCity() ? $updatedPlace->getPlaceCity() : $place->getPlaceCity() );
        $place->setPlaceType($updatedPlace->getPlaceType() ? $updatedPlace->getPlaceType() : $place->getPlaceType() );
        $place->setPlaceRate($updatedPlace->getPlaceRate() ? $updatedPlace->getPlaceRate() : $place->getPlaceRate() );
        $place->setCoach($updatedPlace->getCoach() ? $updatedPlace->getCoach() : $place->getCoach() );
        $place->setPlaceRate($updatedPlace->getPlaceRate() ? $updatedPlace->getPlaceRate() : $place->getPlaceRate() );
        $place->setDept($updatedPlace->getDept() ? $updatedPlace->getDept() : $place->getDept() );
        $place->setStatus('ON');

        $location = $urlGenerator->generate('places.get', ['idPlace' => $place->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $errors = $validator->validate($place);
        if ($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($place);
        $entityManager->flush();
        $context = SerializationContext::create()->setGroups(['getPlace']);
        $jsonPlace = $serializer->serialize($place, "json", $context);
        return new JsonResponse($jsonPlace, JsonResponse::HTTP_CREATED, ['Location' => $location], true);
    }
}