<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Repository\CoachRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;//TODO: remove and test
use JMS\Serializer\SerializationContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class CoachController extends AbstractController
{

    /**
     * Retourne la liste des coachs
     *
     * @param CoachRepository $repository
     * @param SerializerInterface $serializer
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     */

    #[OA\Tag(name: 'Coach')]
    #[OA\Response(response: '200', description: 'OK', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: Coach::class, groups: ["getCoach"]))))]
    #[OA\Response(response: '401', description: 'Unauthorized', content: new OA\JsonContent(example: ["code" => 401, "message" => "Invalid/Expired JWT Token"]))]
    #[Route('/api/coachs', name: 'coach.getAll', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Erreur vous n\'avez pas accès à ceci !')]
    public function getAllCoachs(CoachRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache) : JsonResponse
    {
        $idCache = 'getAllCoachs';
        $jsonCoachs = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer) {
            $coach = $repository->findAll();
            $item->tag("coachCache");
            $context = SerializationContext::create()->setGroups(['getCoach']);
            return $serializer->serialize($coach, 'json', $context);
        });
        return new JsonResponse($jsonCoachs, Response::HTTP_OK, [], true);
    }

    /**
     * Retourne un coach par son id
     *
     * @param Coach $coach
     * @param CoachRepository $repository
     * @param SerializerInterface $serializer
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     */
    #[OA\Tag(name: 'Coach')]
    #[OA\Response(response: '200', description: 'OK', content: new Model(type: Coach::class, groups: ["getCoach"]))]
    #[OA\Response(response: '401', description: 'Unauthorized', content: new OA\JsonContent(example: ["code" => 401, "message" => "Invalid/Expired JWT Token"]))]
    #[Route('/api/coachs/{idCoach}', name: 'coach.get', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Erreur vous n\'avez pas accès à ceci !')]
    #[ParamConverter("coach", class: "App\Entity\Coach", options: ['id' => 'idCoach'])]
    public function getCoach(Coach $coach, CoachRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache) : JsonResponse
    {
        $idCache = 'getCoach';
        $jsonCoachs = $cache->get($idCache, function (ItemInterface $item) use ($coach, $repository, $serializer) {
            $item->tag("coachCache");
            $context = SerializationContext::create()->setGroups(['getCoach']);
            return $serializer->serialize($coach, 'json', $context);
        });
        return new JsonResponse($jsonCoachs, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Supprime un coach par son id
     *
     * @param Coach $coach
     * @param EntityManagerInterface $entityManager
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     */
    #[OA\Tag(name: 'Coach')]
    #[OA\Response(response: '204', description: 'NO CONTENT', content: null)]
    #[OA\Response(response: '401', description: 'Unauthorized', content: new OA\JsonContent(example: ["code" => 401, "message" => "Invalid/Expired JWT Token"]))]
    #[Route('/api/coachs/{idCoach}', name: 'coach.delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Erreur vous n\'avez pas accès à ceci !')]
    #[ParamConverter("coach", class: "App\Entity\Coach", options: ['id' => 'idCoach'])]
    public function deleteCoach(Coach $coach, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache) : JsonResponse
    {
        $cache->invalidateTags(["coachCache"]);
        $entityManager->remove($coach);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Creer un coach
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param SerializerInterface $serializer
     * @param TagAwareCacheInterface $cache
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[OA\Tag(name: 'Coach')]
    #[OA\RequestBody(required: true, content: new Model(type: Coach::class, groups: ["getCoach"]))]
    #[OA\Response(response: '200', description: 'OK', content: new Model(type: Coach::class, groups: ["getCoach"]))]
    #[OA\Response(response: '401', description: 'Unauthorized', content: new OA\JsonContent(example: ["code" => 401, "message" => "Invalid/Expired JWT Token"]))]
    #[Route('/api/coachs', name: 'coach.create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Erreur vous n\'avez pas accès à ceci !')]
    public function createCoach(Request $request, EntityManagerInterface $entityManager,UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, TagAwareCacheInterface $cache, ValidatorInterface $validator) : JsonResponse
    {
        $cache->invalidateTags(["coachCache"]);
        $coach = $serializer->deserialize($request->getContent(), Coach::class, 'json');
        $coach->setStatus('ON');

        $errors = $validator->validate($coach);
        if ($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }
        $entityManager->persist($coach);
        $entityManager->flush();

        $location = $urlGenerator->generate('coach.get', ['idCoach' => $coach->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $context = SerializationContext::create()->setGroups(['getCoach']);
        $jsonCoachs = $serializer->serialize($coach, "json", $context);
        return new JsonResponse($jsonCoachs, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    /**
     * Modifie un coach par son id
     *
     * @param Coach $coach
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param SerializerInterface $serializer
     * @param TagAwareCacheInterface $cache
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[OA\Tag(name: 'Coach')]
    #[OA\RequestBody(required: true, content: new Model(type: Coach::class, groups: ["getCoach"]))]
    #[OA\Response(response: '200', description: 'OK', content: new Model(type: Coach::class, groups: ["getCoach"]))]
    #[OA\Response(response: '401', description: 'Unauthorized', content: new OA\JsonContent(example: ["code" => 401, "message" => "Invalid/Expired JWT Token"]))]
    #[Route('/api/coachs/{id}', name: 'coach.update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Erreur vous n\'avez pas accès à ceci !')]
    public function updateCoach(Coach $coach, Request $request, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, TagAwareCacheInterface $cache,ValidatorInterface $validator): JsonResponse
    {
        $cache->invalidateTags(["coachCache"]);
        $updatedCoach = $serializer->deserialize($request->getContent(), Coach::class, 'json');
        $coach->setCoachFullName($updatedCoach->getCoachFullName() ? $updatedCoach->getCoachFullName() : $coach->getCoachFullName() );
        $coach->setCoachPhoneNumber($updatedCoach->getCoachPhoneNumber() ? $updatedCoach->getCoachPhoneNumber() : $coach->getCoachPhoneNumber() );

        $coach->setStatus('ON');

        $location = $urlGenerator->generate('coach.get', ['idCoach' => $coach->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $errors = $validator->validate($coach);
        if ($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }
        $entityManager->persist($coach);
        $entityManager->flush();
        $context = SerializationContext::create()->setGroups(['getCoach']);
        $jsonCoach = $serializer->serialize($coach, "json", $context);
        return new JsonResponse($jsonCoach, Response::HTTP_CREATED, ['Location' => $location], true);
    }
}
