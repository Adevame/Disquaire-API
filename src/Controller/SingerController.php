<?php

namespace App\Controller;

use App\Repository\SingerRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class SingerController extends AbstractController
{
    #[Route('/api/singers', name: 'singer', methods: ['GET'])]
    public function getAllSongs(SingerRepository $singerRepo, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllSinger-" . $page . "-" . $limit;

        $jsonSingerList = $cache->get($idCache, function (ItemInterface $item) use ($singerRepo, $page, $limit, $serializer) {
            $context = SerializationContext::create()->setGroups(['getSingers']);
            $item->tag('singerCache')
                ->expiresAfter(360);
            $singerList = $singerRepo->findAllWithPagination($page, $limit);
            return $serializer->serialize($singerList, 'json', $context);
        });
        return new JsonResponse($jsonSingerList, Response::HTTP_OK, [], true);
    }
}
