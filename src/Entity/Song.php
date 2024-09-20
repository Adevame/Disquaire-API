<?php

namespace App\Entity;

use App\Repository\SongRepository;
use JMS\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\Since;

/**
 * @Hateoas\Relation(
 * "self",
 * href = @Hateoas\Route(
 * "detailSong",
 * parameters = { "id" = "expr(object.getId())" }
 * ),
 * exclusion = @Hateoas\Exclusion(groups="getSong")
 * )
 *
 * @Hateoas\Relation(
 * "delete",
 * href = @Hateoas\Route(
 * "deleteSong",
 * parameters = { "id" = "expr(object.getId())" },
 * ),
 * exclusion = @Hateoas\Exclusion(groups="getSong", excludeIf= "expr(not is_granted('ROLE_ADMIN'))"),
 * )
 *
 * @Hateoas\Relation(
 * "update",
 * href = @Hateoas\Route(
 * "updateSong",
 * parameters = { "id" = "expr(object.getId())" },
 * ),
 * exclusion = @Hateoas\Exclusion(groups="getSong", excludeIf= "expr(not is_granted('ROLE_ADMIN'))"),
 * )
 *
 */
#[ORM\Entity(repositoryClass: SongRepository::class)]
class Song
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getSong', 'getSingers', 'getDiscs'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getSong', 'getSingers', 'getDiscs'])]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length(min: 3, max: 255, minMessage: "Le titre doit contenir au moins {{ limit }} caractères", maxMessage: "Le titre doit contenir au plus {{ limit }} caractères")]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getSong'])]
    #[Assert\NotBlank(message: "La durée est obligatoire")]
    private ?string $duration = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getSong'])]
    #[Assert\NotBlank(message: "Le genre est obligatoire")]
    private ?string $genre = null;

    #[ORM\ManyToOne(inversedBy: 'songs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getSong'])]
    private ?Singer $singer = null;

    #[ORM\ManyToOne(inversedBy: 'songs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getSong'])]
    private ?Disc $disc = null;

    #[ORM\Column]
    #[Groups(['getSong'])]
    #[Assert\NotBlank(message: "L'année de parution est obligatoire")]
    #[Since("1.1")]
    private ?int $publishedYear = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getSinger(): ?Singer
    {
        return $this->singer;
    }

    public function setSinger(?Singer $singer): static
    {
        $this->singer = $singer;

        return $this;
    }

    public function getDisc(): ?Disc
    {
        return $this->disc;
    }

    public function setDisc(?Disc $disc): static
    {
        $this->disc = $disc;

        return $this;
    }

    public function getPublishedYear(): ?int
    {
        return $this->publishedYear;
    }

    public function setPublishedYear(int $publishedYear): static
    {
        $this->publishedYear = $publishedYear;

        return $this;
    }
}
