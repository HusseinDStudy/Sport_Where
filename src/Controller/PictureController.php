<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class PictureController extends AbstractController
{
    #[Route('api/pictures/{idPicture}', name: 'picture.get', methods:['GET'])]
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
    public function getPicture(int $idPicture, SerializerInterface $serializer,Request $request, PictureRepository $pictureRepository, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $picture = $pictureRepository->find($idPicture);
        $relativePath = $picture->getPublicPath() . "/" . $picture->getRealPath();
        $location = $request->getUriForPath('/');
        $location = $location . str_replace("/assets", "assets", $relativePath);
        if($picture){
           return new JsonResponse($serializer->serialize($picture, 'json', ["groups" => 'getPicture']), JsonResponse::HTTP_OK, ["Location" => $location], true);
        }
        return new JsonResponse(null, JsonResponse::HTTP_NOT_FOUND);
        
    }

    #[Route('api/pictures', name: 'pictures.create', methods:['POST'])]
    /**
     * Creer une image
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    public function createPicture(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {

        $picture = new Picture();
        $picture->setPublicPath("/assets/pictures");
        $picture->setStatus('ON');
        $files = $request->files->get('file');
        $picture->setFile($files);
        $picture->setMimeType($files->getClientMimeType());
        $picture->setRealName($files->getClientOriginalName());
        $entityManager->persist($picture);
        $entityManager->flush();

        $location = $urlGenerator->generate('picture.get', ['idPicture' => $picture->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $jsonPicture = $serializer->serialize($picture, "json", ['groups' => 'getPicture']);
        return new JsonResponse($jsonPicture, JsonResponse::HTTP_CREATED, ['Location' => $location], true);
    }
}
