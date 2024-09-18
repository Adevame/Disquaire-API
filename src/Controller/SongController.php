<?php

namespace App\Controller;

use App\Entity\Song;
use App\Repository\SongRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SongController extends AbstractController
{
    #[Route('/api/songs', name: 'song', methods: ['GET'])]
    public function getAllSongs(SongRepository $songeRepo, SerializerInterface $serializer): JsonResponse
    {
        $songList = $songeRepo->findAll();
        $jsonSongList = $serializer->serialize($songList, 'json');

        return new JsonResponse($jsonSongList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/songs/{id}', name: 'detailSong', methods: ['GET'])]
    public function getDetailSong(Song $song, SerializerInterface $serializer): JsonResponse
    {

        $jsonSong = $serializer->serialize($song, 'json');
        return new JsonResponse($jsonSong, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
