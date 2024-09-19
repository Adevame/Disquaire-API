<?php

namespace App\Entity;

use App\Repository\SongRepository;
use JMS\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\Since;

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
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getSong'])]
    private ?string $duration = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getSong'])]
    private ?string $genre = null;

    #[ORM\ManyToOne(inversedBy: 'songs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Singer $singer = null;

    #[ORM\ManyToOne(inversedBy: 'songs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Disc $disc = null;

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
}
