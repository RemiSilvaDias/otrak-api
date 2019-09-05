<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\EpisodeRepository")
 */
class Episode
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\Column(type="integer")
     */
    private $runtime;

    /**
     * @ORM\Column(type="text")
     */
    private $summary;

    /**
     * @ORM\Column(type="datetime")
     */
    private $airstamp;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Season", inversedBy="episodes")
     */
    private $Season;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Following", mappedBy="episode")
     */
    private $followings;

    public function __construct()
    {
        $this->followings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getRuntime(): ?int
    {
        return $this->runtime;
    }

    public function setRuntime(int $runtime): self
    {
        $this->runtime = $runtime;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getAirstamp(): ?\DateTimeInterface
    {
        return $this->airstamp;
    }

    public function setAirstamp(\DateTimeInterface $airstamp): self
    {
        $this->airstamp = $airstamp;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getSeason(): ?Season
    {
        return $this->Season;
    }

    public function setSeason(?Season $Season): self
    {
        $this->Season = $Season;

        return $this;
    }

    /**
     * @return Collection|Following[]
     */
    public function getFollowings(): Collection
    {
        return $this->followings;
    }

    public function addFollowing(Following $following): self
    {
        if (!$this->followings->contains($following)) {
            $this->followings[] = $following;
            $following->setEpisode($this);
        }

        return $this;
    }

    public function removeFollowing(Following $following): self
    {
        if ($this->followings->contains($following)) {
            $this->followings->removeElement($following);
            // set the owning side to null (unless already changed)
            if ($following->getEpisode() === $this) {
                $following->setEpisode(null);
            }
        }

        return $this;
    }
}
