<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *      collectionOperations={"get"},
 *      itemOperations={
 *          "get",
 *      },
 *      normalizationContext={"groups"={"get_show"}, "enable_max_depth"=true},
 *      attributes={
 *          "force_eager"=false,
 *      },
 * )
 * 
 * @ORM\Entity(repositoryClass="App\Repository\ShowRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="tvshow")
 */
class Show
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get_following", "get_episodes", "get_seasons", "get_show"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get_episodes", "get_seasons", "get_show", "get_following"})
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Groups({"get_episodes", "get_seasons", "get_show"})
     */
    private $summary;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"get_episodes", "get_seasons", "get_show", "get_following"})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"get_episodes", "get_seasons", "get_show", "get_following"})
     */
    private $poster;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("get_show")
     */
    private $website;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get_episodes", "get_seasons", "get_show", "get_following"})
     */
    private $rating;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("get_show")
     */
    private $language;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get_episodes", "get_seasons", "get_show"})
     */
    private $slug;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("get_show")
     */
    private $runtime;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"get_episodes", "get_seasons", "get_following", "get_show"})
     * @Assert\NotBlank
     */
    private $id_tvmaze;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"get_episodes", "get_seasons", "get_show"})
     */
    private $id_imdb;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"get_episodes", "get_seasons", "get_show"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"get_episodes", "get_seasons", "get_show"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("get_show")
     */
    private $apiUpdate;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Genre", inversedBy="shows")
     * @Groups({"get_show", "get_seasons", "get_following"})
     */
    private $genre;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Type", inversedBy="shows")
     * @Groups({"get_show", "get_seasons", "get_following"})
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Network", inversedBy="shows")
     * @Groups("get_show")
     */
    private $network;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Season", mappedBy="tvShow")
     * @ApiSubresource
     * @Groups({"get_seasons", "get_show"})
     */
    private $seasons;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Following", mappedBy="tvShow")
     * @Groups("get_show")
     */
    private $followings;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"get_episodes", "get_seasons", "get_show"})
     */
    private $id_tvdb;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("get_show")
     */
    private $premiered;

    /**
    * @ORM\PrePersist
    */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    /**
    * @ORM\PreUpdate
    */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTime();

        return $this;
    }

    public function __construct()
    {
        $this->genre = new ArrayCollection();
        $this->seasons = new ArrayCollection();
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

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function setPoster(string $poster): self
    {
        $this->poster = $poster;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

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

    public function getIdTvmaze(): ?int
    {
        return $this->id_tvmaze;
    }

    public function setIdTvmaze(int $id_tvmaze): self
    {
        $this->id_tvmaze = $id_tvmaze;

        return $this;
    }

    public function getIdImdb(): ?string
    {
        return $this->id_imdb;
    }

    public function setIdImdb(?string $id_imdb): self
    {
        $this->id_imdb = $id_imdb;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getApiUpdate(): ?int
    {
        return $this->apiUpdate;
    }

    public function setApiUpdate(?int $apiUpdate): self
    {
        $this->apiUpdate = $apiUpdate;

        return $this;
    }

    /**
     * @return Collection|Genre[]
     */
    public function getGenre(): Collection
    {
        return $this->genre;
    }

    public function addGenre(Genre $genre): self
    {
        if (!$this->genre->contains($genre)) {
            $this->genre[] = $genre;
        }

        return $this;
    }

    public function removeGenre(Genre $genre): self
    {
        if ($this->genre->contains($genre)) {
            $this->genre->removeElement($genre);
        }

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNetwork(): ?Network
    {
        return $this->network;
    }

    public function setNetwork(?Network $network): self
    {
        $this->network = $network;

        return $this;
    }

    /**
     * @return Collection|Season[]
     */
    public function getSeasons(): Collection
    {
        return $this->seasons;
    }

    public function addSeason(Season $season): self
    {
        if (!$this->seasons->contains($season)) {
            $this->seasons[] = $season;
            $season->setTvShow($this);
        }

        return $this;
    }

    public function removeSeason(Season $season): self
    {
        if ($this->seasons->contains($season)) {
            $this->seasons->removeElement($season);
            // set the owning side to null (unless already changed)
            if ($season->getTvShow() === $this) {
                $season->setTvShow(null);
            }
        }

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
            $following->setTvShow($this);
        }

        return $this;
    }

    public function removeFollowing(Following $following): self
    {
        if ($this->followings->contains($following)) {
            $this->followings->removeElement($following);
            // set the owning side to null (unless already changed)
            if ($following->getTvShow() === $this) {
                $following->setTvShow(null);
            }
        }

        return $this;
    }

    public function getIdTvdb(): ?int
    {
        return $this->id_tvdb;
    }

    public function setIdTvdb(?int $id_tvdb): self
    {
        $this->id_tvdb = $id_tvdb;

        return $this;
    }

    public function getPremiered(): ?string
    {
        return $this->premiered;
    }

    public function setPremiered(?string $premiered): self
    {
        $this->premiered = $premiered;

        return $this;
    }
}
