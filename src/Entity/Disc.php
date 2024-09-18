<?php

namespace App\Entity;

use App\Repository\DiscRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscRepository::class)]
class Disc
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $discName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiscName(): ?string
    {
        return $this->discName;
    }

    public function setDiscName(string $discName): static
    {
        $this->discName = $discName;

        return $this;
    }
}
