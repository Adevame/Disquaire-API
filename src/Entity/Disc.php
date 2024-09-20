<?php

namespace App\Entity;

use App\Repository\DiscRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Hateoas\Relation(
 * "self",
 * href = @Hateoas\Route(
 * "detailSinger",
 * parameters = { "id" = "expr(object.getId())" }
 * ),
 * exclusion = @Hateoas\Exclusion(groups="getSingers")
 * )
 *
 * @Hateoas\Relation(
 * "delete",
 * href = @Hateoas\Route(
 * "deleteSinger",
 * parameters = { "id" = "expr(object.getId())" },
 * ),
 * exclusion = @Hateoas\Exclusion(groups="getSingers", excludeIf= "expr(not is_granted('ROLE_ADMIN'))"),
 * )
 *
 * @Hateoas\Relation(
 * "update",
 * href = @Hateoas\Route(
 * "updateSinger",
 * parameters = { "id" = "expr(object.getId())" },
 * ),
 * exclusion = @Hateoas\Exclusion(groups="getSingers", excludeIf= "expr(not is_granted('ROLE_ADMIN'))"),
 * )
 *
 */

#[ORM\Entity(repositoryClass: DiscRepository::class)]
class Disc
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getSong', 'getDiscs', 'getSingers'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getSong', 'getDiscs', 'getSingers'])]
    #[Assert\NotBlank(message: "Le nom du disque est obligatoire")]
    private ?string $discName = null;

    /**
     * @var Collection<int, Song>
     */
    #[ORM\OneToMany(targetEntity: Song::class, mappedBy: 'disc')]
    #[Groups(['getDiscs'])]
    private Collection $songs;

    public function __construct()
    {
        $this->songs = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Song>
     */
    public function getSongs(): Collection
    {
        return $this->songs;
    }

    public function addSong(Song $song): static
    {
        if (!$this->songs->contains($song)) {
            $this->songs->add($song);
            $song->setDisc($this);
        }

        return $this;
    }

    public function removeSong(Song $song): static
    {
        if ($this->songs->removeElement($song)) {
            // set the owning side to null (unless already changed)
            if ($song->getDisc() === $this) {
                $song->setDisc(null);
            }
        }

        return $this;
    }
}
