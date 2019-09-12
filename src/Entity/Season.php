<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *      collectionOperations={"get"},
 *      itemOperations={"get"},
 *      normalizationContext={"groups"={"get_seasons"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\SeasonRepository")
 */
class Season
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get_episodes", "get_seasons"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"get_episodes", "get_seasons"})
     */
    private $number;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"get_episodes", "get_seasons"})
     */
    private $poster;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"get_episodes", "get_seasons"})
     */
    private $episodeCount;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("get_seasons")
     */
    private $premiereDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("get_seasons")
     */
    private $endDate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Episode", mappedBy="Season")
     * @ApiSubresource
     * @Groups("get_seasons")
     */
    private $episodes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Show", inversedBy="seasons")
     * @ApiSubresource
     * @Groups({"get_episodes", "get_seasons"})
     */
    private $tvShow;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Following", mappedBy="season")
     * @Groups("get_seasons")
     */
    private $followings;

    public function __construct()
    {
        $this->episodes = new ArrayCollection();
        $this->followings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function setPoster(?string $poster): self
    {
        $this->poster = $poster;

        return $this;
    }

    public function getEpisodeCount(): ?int
    {
        return $this->episodeCount;
    }

    public function setEpisodeCount(int $episodeCount): self
    {
        $this->episodeCount = $episodeCount;

        return $this;
    }

    public function getPremiereDate(): ?\DateTimeInterface
    {
        return $this->premiereDate;
    }

    public function setPremiereDate(\DateTimeInterface $premiereDate): self
    {
        $this->premiereDate = $premiereDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return Collection|Episode[]
     */
    public function getEpisodes(): Collection
    {
        return $this->episodes;
    }

    public function addEpisode(Episode $episode): self
    {
        if (!$this->episodes->contains($episode)) {
            $this->episodes[] = $episode;
            $episode->setSeason($this);
        }

        return $this;
    }

    public function removeEpisode(Episode $episode): self
    {
        if ($this->episodes->contains($episode)) {
            $this->episodes->removeElement($episode);
            // set the owning side to null (unless already changed)
            if ($episode->getSeason() === $this) {
                $episode->setSeason(null);
            }
        }

        return $this;
    }

    public function getTvShow(): ?Show
    {
        return $this->tvShow;
    }

    public function setTvShow(?Show $tvShow): self
    {
        $this->tvShow = $tvShow;

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
            $following->setSeason($this);
        }

        return $this;
    }

    public function removeFollowing(Following $following): self
    {
        if ($this->followings->contains($following)) {
            $this->followings->removeElement($following);
            // set the owning side to null (unless already changed)
            if ($following->getSeason() === $this) {
                $following->setSeason(null);
            }
        }

        return $this;
    }
}
