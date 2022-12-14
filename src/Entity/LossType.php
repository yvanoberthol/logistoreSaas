<?php

namespace App\Entity;

use App\Repository\LossTypeRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=LossTypeRepository::class)
 */
class LossType
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $addDate;

    /**
     * @ORM\OneToMany(targetEntity=Loss::class, mappedBy="type")
     */
    private $losses;

    /**
     * @ORM\Column(type="boolean")
     */
    private $updatable = true;

    /**
     * @ORM\ManyToOne(targetEntity=Store::class, inversedBy="lossTypes")
     */
    private $store;

    public function __construct()
    {
        $this->losses = new ArrayCollection();
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

    public function getAddDate(): ?DateTimeInterface
    {
        return $this->addDate;
    }

    public function setAddDate(DateTimeInterface $addDate): self
    {
        $this->addDate = $addDate;

        return $this;
    }

    /**
     * @return Collection|Loss[]
     */
    public function getLosses(): Collection
    {
        return $this->losses;
    }

    /**
     * @ORM\PrePersist
     */
    public function setDate(): void {
        $this->addDate = new DateTime();
    }

    public function getUpdatable(): ?bool
    {
        return $this->updatable;
    }

    public function setUpdatable(bool $updatable): self
    {
        $this->updatable = $updatable;

        return $this;
    }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): self
    {
        $this->store = $store;

        return $this;
    }
}
