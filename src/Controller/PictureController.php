<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;//TODO: remove and test
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class PictureController extends AbstractController
{
    /**
     * Retourne une image par son id
     *
     * @param integer $idPicture
     * @param SerializerInterface $serializer
     * @param Request $request
     * @param PictureRepository $pictureRepository
     * @return JsonResponse
     */
    #[OA\Tag(name: 'Picture')]
    #[OA\Response(response: '200', description: 'OK', content: new Model(type: Picture::class, groups: ['getPicture']))]
    #[OA\Response(response: '401', description: 'Unauthorized', content: new OA\JsonContent(example: ["code" => 401, "message" => "Invalid/Expired JWT Token"]))]
    #[Route('api/pictures/{idPicture}', name: 'picture.get', methods:['GET'])]
    #[IsGranted('ROLE_USER', message: 'Erreur vous n\'avez pas accès à ceci !')]
    public function getPicture(int $idPicture, SerializerInterface $serializer,Request $request, PictureRepository $pictureRepository, TagAwareCacheInterface $cache): JsonResponse
    {
        $picture = $pictureRepository->find($idPicture);
        $relativePath = $picture->getPublicPath() . "/" . $picture->getRealPath();
        $location = $request->getUriForPath('/');
        $location = $location . str_replace("/assets", "assets", $relativePath);

        $idCache = 'getPicture';
        $jsonPicture = $cache->get($idCache, function (ItemInterface $item) use ($picture, $serializer){
            $item->tag("pictureCache");
            if($picture){
                $context = SerializationContext::create()->setGroups(['getPicture']);
                return $serializer->serialize($picture, 'json', $context);
            }else{
                return null;
            }
        });
        if($jsonPicture){
           return new JsonResponse($jsonPicture, Response::HTTP_OK, ["Location" => $location], true);
        }
        return new JsonResponse($jsonPicture, Response::HTTP_NOT_FOUND);
    }

    /**
     * Creer une image
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    #[OA\Tag(name: 'Picture')]
    #[OA\RequestBody(
        required: true,
        content: [new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                properties: [new OA\Property(
                    property: "file", type: "file", format: "binary",
                )]
            )
        )]
    )]
    #[OA\Response(response: '201', description: 'Created', content: new Model(type: Picture::class, groups: ['getPicture']))]
    #[OA\Response(response: '400', description: 'Bad Request', content: new OA\JsonContent(example: ["error" => "Your input is not valide"]))]
    #[OA\Response(response: '401', description: 'Unauthorized', content: new OA\JsonContent(example: ["code" => 401, "message" => "Invalid/Expired JWT Token"]))]
    #[Route('api/pictures', name: 'pictures.create', methods:['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Erreur vous n\'avez pas accès à ceci !')]
    public function createPicture(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, TagAwareCacheInterface $cache): JsonResponse
    {
        $cache->invalidateTags(["pictureCache"]);
        $picture = new Picture();
        $files = $request->files->get('file');
        if (!$files){
            return new JsonResponse($serializer->serialize(["error" => "Your input is not valide"], 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }
        $picture->setFile($files);
        $picture->setMimeType($files->getClientMimeType());
        $picture->setRealName($files->getClientOriginalName());
        $picture->setPublicPath("/images/pictures");
        $picture->setStatus('ON');

        $entityManager->persist($picture);
        $entityManager->flush();
        $location = $urlGenerator->generate('picture.get', ['idPicture' => $picture->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $context = SerializationContext::create()->setGroups(['getPicture']);
        $jsonPicture = $serializer->serialize($picture, "json", $context);
        return new JsonResponse($jsonPicture, Response::HTTP_CREATED, ['Location' => $location], true);
    }
}
