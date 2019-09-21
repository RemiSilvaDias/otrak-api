<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *      collectionOperations={
 *          "get"={"access_control"="object.getUser() == user"},
 *      },
 *      itemOperations={
 *          "get"={"access_control"="object.getUser() == user"},
 *          "put"={"method"="PATCH"},
 *          "delete"
 *      },
 *      attributes={"order"={"id": "DESC", "episode"}, "force_eager"=true},
 *      normalizationContext={"groups"={"get_following"}, "enable_max_depth"=true}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\FollowingRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Following
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("get_following")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank
     * @Groups("get_following")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("get_following")
     */
    private $endDate;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Groups("get_following")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="followings")
     * @Assert\NotBlank
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Episode", inversedBy="followings")
     * @ApiSubresource
     * @Groups("get_following")
     */
    private $episode;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Season", inversedBy="followings")
     * @ApiSubresource
     * @Groups("get_following")
     */
    private $season;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Show", inversedBy="followings")
     * @ApiSubresource
     * @Assert\NotBlank
     * @Groups("get_following")
     */
    private $tvShow;

    /**
    * @ORM\PrePersist
    */
    public function setStartDateValue()
    {
        if ($this->startDate == null)
        {
            $this->startDate = new \DateTime();
            
            return $this;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getEpisode(): ?Episode
    {
        return $this->episode;
    }

    public function setEpisode(?Episode $episode): self
    {
        $this->episode = $episode;

        return $this;
    }

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(?Season $season): self
    {
        $this->season = $season;

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
}
