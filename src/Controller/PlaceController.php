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
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;//TODO: remove and test
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;


class PlaceController extends AbstractController
{

    /**
     * Retourne la liste des lieux paginés
     *
     * @param PlaceRepository $repository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     */
    #[OA\Tag(name: 'Place')]
    #[OA\Response(response: '200', description: 'OK', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: Place::class, groups: ["getPlace"]))))]
    #[OA\Response(response: '401', description: 'Unauthorized', content: new OA\JsonContent(example: ["code" => 401, "message" => "Invalid/Expired JWT Token"]))]
    #[OA\Parameter(name: 'page', in: 'query', example: '1')]
    #[OA\Parameter(name: 'limit', in: 'query', example: '5')]
    #[Route('/api/places', name: 'places.getAll', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Erreur vous n\'avez pas accès à ceci !')]
    public function getAllPlaces(PlaceRepository $repository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache) : JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $context = SerializationContext::create()->setGroups(['getPlace']);
        if($page==1 and $limit==5){
            $idCache = 'getAllPlaces';
            $jsonPlaces = $cache->get($idCache, function (ItemInterface $item) use ($context, $limit, $page, $request, $serializer, $repository){
                $item->tag("placeCache");
                $places = $repository->findWithPagination($page, $limit);
                return $serializer->serialize($places, 'json', $context);
            });
        }else{
            $places = $repository->findWithPagination($page, $limit);
            $jsonPlaces=$serializer->serialize($places, 'json', $context);
        }
        return new JsonResponse($jsonPlaces, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Retourne la liste des lieux d'un departement demandés et paginés
     *
     * @param PlaceRepository $repository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     */
    #[OA\Tag(name: 'Place')]
    #[OA\Response(response: '200', description: 'OK', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: Place::class, groups: ["getPlace"]))))]
    #[OA\Response(response: '401', description: 'Unauthorized', content: new OA\JsonContent(example: ["code" => 401, "message" => "Invalid/Expired JWT Token"]))]
    #[OA\Response(response: '422', description: 'Unprocessable Content', content: new OA\JsonContent(example: ['message'=>'votre  $dept n\'est pad valide']))]
    #[OA\Parameter(name: 'page', in: 'query', example: '1')]
    #[OA\Parameter(name: 'limit', in: 'query', example: '5')]
    #[OA\Parameter(name: 'dept', in: 'query', example: '69')]
    #[Route('/api/placesDept', name: 'places.getAllByDept', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Erreur vous n\'avez pas accès à ceci !')]
    public function getPlacesbyDept(PlaceRepository $repository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache) : JsonResponse
    {
        //$status = $request->get('status', 'ON');

        //$places = $repository->findWithPagination($page, $limit);
        //$places = $repository->orderByRate();
        //$places = $repository->findPlacesByStatus($status);
        //$places = $repository->findAllCustom($page, $limit);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $dept= $request->get('dept',0);

        if($dept == 0){
            $data=['message'=>'votre  $dept n\'est pad valide'];
            $jsonPlaces = $serializer->serialize($data, 'json');
            return new JsonResponse($jsonPlaces, Response::HTTP_UNPROCESSABLE_ENTITY, ['accept' => 'json'], true);
        }

        $context = SerializationContext::create()->setGroups(['getPlace']);
        if($page==1 and $limit==5 and $dept==0){
            $idCache = 'getAllPlaces';
            $jsonPlaces = $cache->get($idCache, function (ItemInterface $item) use ($context, $dept, $limit, $page, $request, $serializer, $repository){
                $item->tag("placeCache");
                $places = $repository->findAllCustom($page, $limit,$dept);
                return $serializer->serialize($places, 'json', $context);
            });
        }else{
            $places = $repository->findAllCustom($page, $limit,$dept);
            $jsonPlaces=$serializer->serialize($places, 'json', $context);
        }


        return new JsonResponse($jsonPlaces, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Retourne un lieu par son id
     *
     * @param Place $place
     * @param PlaceRepository $repository
     * @param SerializerInterface $serializer
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     */
    #[OA\Tag(name: 'Place')]
    #[OA\Response(response: '200', description: 'OK', content: new Model(type: Place::class, groups: ["getPlace"]))]
    #[OA\Response(response: '401', description: 'Unauthorized', content: new OA\JsonContent(example: ["code" => 401, "message" => "Invalid/Expired JWT Token"]))]
    #[Route('/api/places/{idPlace}', name: 'places.get', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Erreur vous n\'avez pas accès à ceci !')]
    #[ParamConverter("place", class: "App\Entity\Place", options: ['id' => 'idPlace'])]
    public function getPlace(Place $place, PlaceRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache) : JsonResponse
    {
        $idCache = 'getPlace';
        $jsonPlaces = $cache->get($idCache, function (ItemInterface $item) use ($place, $serializer){
            $item->tag("placeCache");
            $context = SerializationContext::create()->setGroups(['getPlace']);
            return $serializer->serialize($place, 'json', $context);
        });
        return new JsonResponse($jsonPlaces, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Supprime un lieux par son id
     *
     * @param Place $place
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     */
    #[OA\Tag(name: 'Place')]
    #[OA\Response(response: '204', description: 'NO CONTENT', content: null)]
    #[OA\Response(response: '401', description: 'Unauthorized', content: new OA\JsonContent(example: ["code" => 401, "message" => "Invalid/Expired JWT Token"]))]
    #[Route('/api/places/{idPlace}', name: 'places.delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Erreur vous n\'avez pas accès à ceci !')]
    #[ParamConverter("place", class: "App\Entity\Place", options: ['id' => 'idPlace'])]
    public function deletePlace(Place $place, EntityManagerInterface $entityManager, SerializerInterface $serializer, TagAwareCacheInterface $cache) : JsonResponse
    {
        $cache->invalidateTags(["placeCache"]);
        $place->setCoach(null);
        $entityManager->remove($place);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
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
    #[OA\Tag(name: 'Place')]
    #[OA\RequestBody(required: true, content: new Model( type: Place::class, groups: ["createUpdatePlace"]))]
    #[OA\Response(response: '200', description: 'OK', content: new Model(type: Place::class, groups: ["getPlace"]))]
    #[OA\Response(response: '401', description: 'Unauthorized', content: new OA\JsonContent(example: ["code" => 401, "message" => "Invalid/Expired JWT Token"]))]
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
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }
        $entityManager->persist($place);
        $entityManager->flush();

        $location = $urlGenerator->generate('places.get', ['idPlace' => $place->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $context = SerializationContext::create()->setGroups(['getPlace']);
        $jsonPlace = $serializer->serialize($place, "json", $context);
        return new JsonResponse($jsonPlace, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    /**
     * Modifie un lieu par son id
     *
     * @param Place $place
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     */
    #[OA\Tag(name: 'Place')]
    #[OA\RequestBody(required: true, content: new Model(type: Place::class, groups: ["createUpdatePlace"]))]
    #[OA\Response(response: '200', description: 'OK', content: new Model(type: Place::class, groups: ["getPlace"]))]
    #[OA\Response(response: '401', description: 'Unauthorized', content: new OA\JsonContent(example: ["code" => 401, "message" => "Invalid/Expired JWT Token"]))]
    #[Route('/api/places/{id}', name: 'places.update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Erreur vous n\'avez pas accès à ceci !')]
    public function updatePlace(Place $place, Request $request, EntityManagerInterface $entityManager,UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, ValidatorInterface $validator, TagAwareCacheInterface $cache) : JsonResponse
    {
        $cache->invalidateTags(["placeCache"]);
        $updatedPlace = $serializer->deserialize($request->getContent(), Place::class, 'json');

        $place->setPlaceName($updatedPlace->getPlaceName() ? $updatedPlace->getPlaceName() : $place->getPlaceName() );
        $place->setPlaceAddress($updatedPlace->getPlaceAddress() ? $updatedPlace->getPlaceAddress() : $place->getPlaceAddress() );
        $place->setPlaceCity($updatedPlace->getPlaceCity() ? $updatedPlace->getPlaceCity() : $place->getPlaceCity() );
        $place->setPlaceType($updatedPlace->getPlaceType() ? $updatedPlace->getPlaceType() : $place->getPlaceType() );
        $place->setPlaceRate($updatedPlace->getPlaceRate() ? $updatedPlace->getPlaceRate() : $place->getPlaceRate() );
        $place->setCoach($updatedPlace->getCoach() ? $updatedPlace->getCoach() : $place->getCoach() );
        $place->setDept($updatedPlace->getDept() ? $updatedPlace->getDept() : $place->getDept() );
        $place->setStatus('ON');

        $location = $urlGenerator->generate('places.get', ['idPlace' => $place->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $errors = $validator->validate($place);
        if ($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($place);
        $entityManager->flush();
        $context = SerializationContext::create()->setGroups(['getPlace']);
        $jsonPlace = $serializer->serialize($place, "json", $context);
        return new JsonResponse($jsonPlace, Response::HTTP_CREATED, ['Location' => $location], true);
    }
}