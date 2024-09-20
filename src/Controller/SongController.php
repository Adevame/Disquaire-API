<?php

namespace App\Controller;

use App\Entity\Song;
use App\Repository\DiscRepository;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class SongController extends AbstractController
{
    /**
     * @OA\Response(
     * response=200,
     * description="Retourne la liste des chansons",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref=@Model(type=Song::class, groups={"getSong"}))
     * )
     * )
     * @OA\Parameter(
     * name="page",
     * in="query",
     * description="La page que l'on veut récupérer",
     * @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     * name="limit",
     * in="query",
     * description="Le nombre d'éléments que l'on veut récupérer",
     * @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Song")
     *
     * @param SongRepository $songRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/songs', name: 'song', methods: ['GET'])]
    public function getAllSongs(SongRepository $songRepo, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache, VersioningService $versioningService): JsonResponse
    {
        $version = $versioningService->getVersion(); // on récupère la version de l'API

        $page = $request->get('page', 1); // on définit la page courante
        $limit = $request->get('limit', 5); // on définit le nombre de résultats par page

        $idCache = "getAllSongs-" . $page . "-" . $limit; // on construit l'id du cache avec les paramètres de pagination

        $jsonSongList = $cache->get($idCache, function (ItemInterface $item) use ($songRepo, $page, $limit, $serializer, $version) {
            $context = SerializationContext::create()->setGroups(['getSong']); // on définit le groupe de données à sérialiser
            $context->setVersion($version); // on définit la version du format sérialisé
            $item->tag('songCache') // on tag le cache avec le nom du groupe de données sérialisé
                ->expiresAfter(360);
            // met en cache le résultat de la requête pour 360 secondes
            $songList = $songRepo->findAllWithPagination($page, $limit);
            return $serializer->serialize($songList, 'json', $context);
            // on renvoie le résultat sérialisé dans le format JSON avec le groupe de données sérialisé
        });
        $responseData = ['songs' => json_decode($jsonSongList)];
        return new JsonResponse(json_encode($responseData), Response::HTTP_OK, [], true); // on renvoie une réponse JSON avec le code HTTP 200 et le résultat sérialisé
    }

    #[Route('/api/songs/{id}', name: 'detailSong', methods: ['GET'])]
    public function getDetailSong(Song $song, SerializerInterface $serializer, VersioningService $versioningService): JsonResponse
    {
        $version = $versioningService->getVersion();
        $context = SerializationContext::create()->setGroups(['getSong']);
        $context->setVersion($version);
        $jsonSong = $serializer->serialize($song, 'json', $context);
        return new JsonResponse($jsonSong, Response::HTTP_OK, [], true);
    }

    #[Route('/api/songs/{id}', name: 'deleteSong', methods: ['DELETE'])]
    public function deleteSong(Song $song, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($song);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/songs', name: 'createSong', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous devez être administrateur pour effectuer cette action.')]
    public function createSong(SingerRepository $singerRepository, DiscRepository $discRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getSongs']);

        $song = $serializer->deserialize($request->getContent(), Song::class, 'json');

        $content = $request->toArray();
        $idSinger = $content['idSinger'] ?? -1;
        $idDisc = $content['idDisc'] ?? -1;

        $song->setSinger($singerRepository->find($idSinger));
        $song->setDisc($discRepository->find($idDisc));

        $errors = $validator->validate($song);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($song);
        $em->flush();

        $jsonSong = $serializer->serialize($song, 'json', $context);

        $location = $urlGenerator->generate('detailSong', ['id' => $song->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonSong, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('/api/songs/{id}', name: 'updateSong', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous devez être administrateur pour effectuer cette action.')]
    public function updateSong(
        Request $request,
        SerializerInterface $serializer,
        Song $currentSong,
        EntityManagerInterface $em,
        SingerRepository $singerRepository,
        DiscRepository $discRepository,
        ValidatorInterface $validator,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $newSong = $serializer->deserialize($request->getContent(), Song::class, 'json');
        $currentSong->setTitle($newSong->getTitle());
        $currentSong->setDuration($newSong->getDuration());

        $content = $request->toArray();
        $idSinger = $content['idSinger'] ?? -1;
        $idDisc = $content['idDisc'] ?? -1;

        $currentSong->setSinger($singerRepository->find($idSinger));
        $currentSong->setDisc($discRepository->find($idDisc));

        $errors = $validator->validate($currentSong);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($currentSong);
        $em->flush();

        $cache->invalidateTags(["songCache"]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
