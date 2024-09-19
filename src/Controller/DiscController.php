<?php

namespace App\Controller;

use App\Repository\DiscRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class DiscController extends AbstractController
{
    #[Route('/api/discs', name: 'disc', methods: ['GET'])]
    public function getAllDiscs(DiscRepository $discRepo, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);

        $idCache = "getAllDisc-" . $page . "-" . $limit;

        $jsonDiscList = $cache->get($idCache, function (ItemInterface $item) use ($discRepo, $page, $limit, $serializer) {
            $context = SerializationContext::create()->setGroups(['getDiscs']);
            $item->tag('discCache')
                ->expiresAfter(360);
            $discList = $discRepo->findAllWithPagination($page, $limit);
            return $serializer->serialize($discList, 'json', $context);
        });
        return new JsonResponse($jsonDiscList, Response::HTTP_OK, [], true);
    }
}
