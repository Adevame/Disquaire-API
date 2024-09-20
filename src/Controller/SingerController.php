<?php

namespace App\Controller;

use App\Entity\Singer;
use App\Repository\SingerRepository;
use App\Repository\SongRepository;
use App\Service\VersioningService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class SingerController extends AbstractController
{
    #[Route('/api/singers', name: 'singer', methods: ['GET'])]
    public function getAllSingers(SingerRepository $singerRepo, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache, VersioningService $versioningService): JsonResponse
    {
        $version = $versioningService->getVersion();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllSinger-" . $page . "-" . $limit;

        $jsonSingerList = $cache->get($idCache, function (ItemInterface $item) use ($singerRepo, $page, $limit, $serializer, $version) {
            $context = SerializationContext::create()->setGroups(['getSingers']);
            $context->setVersion($version);
            $item->tag('singerCache')
                ->expiresAfter(360);
            $singerList = $singerRepo->findAllWithPagination($page, $limit);
            return $serializer->serialize($singerList, 'json', $context);
        });
        $responseData = ['singers' => json_decode($jsonSingerList)];
        return new JsonResponse(json_encode($responseData), Response::HTTP_OK, [], true);
    }

    #[Route('/api/singers/{id}', name: 'detailSinger', methods: ['GET'])]
    public function getDetailSinger(Singer $singer, SerializerInterface $serializer, VersioningService $versioningService): JsonResponse
    {
        $version = $versioningService->getVersion();
        $context = SerializationContext::create()->setGroups(['getSingers']);
        $context->setVersion($version);
        $jsonSinger = $serializer->serialize($singer, 'json', $context);
        return new JsonResponse($jsonSinger, Response::HTTP_OK, [], true);
    }

    #[Route('/api/singers/{id}', name: 'deleteSinger', methods: ['DELETE'])]
    public function deleteSinger(Singer $singer, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($singer);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/singers', name: 'createSinger', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous devez être administrateur pour effectuer cette action.')]
    public function createSinger(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getSingers']);

        $singer = $serializer->deserialize($request->getContent(), Singer::class, 'json');

        $errors = $validator->validate($singer);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($singer);
        $em->flush();

        $jsonSinger = $serializer->serialize($singer, 'json', $context);

        $location = $urlGenerator->generate('detailSinger', ['id' => $singer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonSinger, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('/api/singers/{id}', name: 'updateSinger', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous devez être administrateur pour effectuer cette action.')]
    public function updateSinger(
        Request $request,
        SerializerInterface $serializer,
        Singer $currentSinger,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $newSinger = $serializer->deserialize($request->getContent(), Singer::class, 'json');
        $currentSinger->setFullName($newSinger->getTitle());

        $errors = $validator->validate($currentSinger);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($currentSinger);
        $em->flush();

        $cache->invalidateTags(["singerCache"]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
