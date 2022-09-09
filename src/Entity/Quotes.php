<?php

namespace App\Entity;

use App\Repository\QuotesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuotesRepository::class)]
class Quotes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    private ?string $meta = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    //L'utilisateur relié à la citation
    #[ORM\ManyToOne(inversedBy: 'quoting')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userQuoting = null;

    //La catégorie reliée à la citation
    #[ORM\ManyToOne(inversedBy: 'Quotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    //Les likes sont juste une relation ManyToMany avec les utilisateurs
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'Liked')]
    private Collection $likes;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getMeta(): ?string
    {
        return $this->meta;
    }

    public function setMeta(string $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUserQuoting(): ?User
    {
        return $this->userQuoting;
    }

    public function setUserQuoting(?User $userQuoting): self
    {
        $this->userQuoting = $userQuoting;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(User $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->addLiked($this);
        }

        return $this;
    }

    public function removeLike(User $like): self
    {
        if ($this->likes->removeElement($like)) {
            $like->removeLiked($this);
        }

        return $this;
    }
}
