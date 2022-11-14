<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
//use Symfony\Component\Serializer\Serializer;
//use Symfony\Component\Serializer\SerializerInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class PictureController extends AbstractController
{
    /**
     * Retourne une image par son id
     *
     * @param integer $idPicture
     * @param SerializerInterface $serializer
     * @param Request $request
     * @param PictureRepository $pictureRepository
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    #[Route('api/pictures/{idPicture}', name: 'picture.get', methods:['GET'])]
    #[IsGranted('ROLE_USER', message: 'Erreur vous n\'avez pas accès à ceci !')]
    public function getPicture(int $idPicture, SerializerInterface $serializer,Request $request, PictureRepository $pictureRepository, UrlGeneratorInterface $urlGenerator, TagAwareCacheInterface $cache): JsonResponse
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
            //return $serializer->serialize($place, 'json',['groups' => 'getPlace']);
        });
        if($jsonPicture){
           return new JsonResponse($jsonPicture, JsonResponse::HTTP_OK, ["Location" => $location], true);
        }
        return new JsonResponse($jsonPicture, JsonResponse::HTTP_NOT_FOUND);
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
    #[Route('api/pictures', name: 'pictures.create', methods:['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Erreur vous n\'avez pas accès à ceci !')]
    public function createPicture(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, TagAwareCacheInterface $cache): JsonResponse
    {
        $cache->invalidateTags(["pictureCache"]);
        $picture = new Picture();
        $picture->setPublicPath("/images/pictures");
        $picture->setStatus('ON');
        $files = $request->files->get('file');
        $picture->setFile($files);
        $picture->setMimeType($files->getClientMimeType());
        $picture->setRealName($files->getClientOriginalName());
        $entityManager->persist($picture);
        $entityManager->flush();

        $location = $urlGenerator->generate('picture.get', ['idPicture' => $picture->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $context = SerializationContext::create()->setGroups(['getPicture']);
        $jsonPicture = $serializer->serialize($picture, "json", $context);
        return new JsonResponse($jsonPicture, JsonResponse::HTTP_CREATED, ['Location' => $location], true);
    }
}
