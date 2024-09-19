<?php

namespace App\Controller;

use App\Entity\Song;
use App\Repository\DiscRepository;
use App\Repository\SingerRepository;
use App\Repository\SongRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class SongController extends AbstractController
{
    #[Route('/api/songs', name: 'song', methods: ['GET'])]
    public function getAllSongs(SongRepository $songRepo, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1); // on définit la page courante
        $limit = $request->get('limit', 5); // on définit le nombre de résultats par page

        $idCache = "getAllSongs-" . $page . "-" . $limit; // on construit l'id du cache avec les paramètres de pagination

        $jsonSongList = $cache->get($idCache, function (ItemInterface $item) use ($songRepo, $page, $limit, $serializer) {
            $context = SerializationContext::create()->setGroups(['getSongs']); // on définit le groupe de données à sérialiser
            $item->tag('songCache') // on tag le cache avec le nom du groupe de données sérialisé
                ->expiresAfter(360);
            // met en cache le résultat de la requête pour 360 secondes
            $songList = $songRepo->findAllWithPagination($page, $limit);
            return $serializer->serialize($songList, 'json', $context);
            // on renvoie le résultat sérialisé dans le format JSON avec le groupe de données sérialisé
        });
        return new JsonResponse($jsonSongList, Response::HTTP_OK, [], true); // on renvoie une réponse JSON avec le code HTTP 200 et le résultat sérialisé
    }

    #[Route('/api/songs/{id}', name: 'detailSong', methods: ['GET'])]
    public function getDetailSong(Song $song, SerializerInterface $serializer): JsonResponse
    {

        $jsonSong = $serializer->serialize($song, 'json');
        return new JsonResponse($jsonSong, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/songs/{id}', name: 'deleteSong', methods: ['DELETE'])]
    public function deleteSong(Song $song, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($song);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/songs', name: 'createSong', methods: ['POST'])]
    public function createSong(SingerRepository $singerRepository, DiscRepository $discRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $song = $serializer->deserialize($request->getContent(), Song::class, 'json');

        $content = $request->toArray();

        $idSinger = $content['idSinger'] ?? -1;
        $idDisc = $content['idDisc'] ?? -1;

        $song->setSinger($singerRepository->find($idSinger));
        $song->setDisc($discRepository->find($idDisc));


        $em->persist($song);
        $em->flush();

        $jsonSong = $serializer->serialize($song, 'json');

        $location = $urlGenerator->generate('detailSong', ['id' => $song->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonSong, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('/api/songs/{id}', name: 'updateSong', methods: ['PUT'])]
    public function updateSong(Request $request, SerializerInterface $serializer, Song $currentSong, EntityManagerInterface $em, SingerRepository $singerRepository): JsonResponse
    {
        $newSong = $serializer->deserialize($request->getContent(), Song::class, 'json');
        $currentSong->setTitle($newSong->getTitle());
        $currentSong->setDuration($newSong->getDuration());

        // $errors = $validator->validate($currentSong);
        // if ($errors->count() > 0) {
        //     return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        // }

        $content = $request->toArray();
        $idSinger = $content['idSinger'] ?? -1;

        $currentSong->setSinger($singerRepository->find($idSinger));

        $em->persist($currentSong);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
