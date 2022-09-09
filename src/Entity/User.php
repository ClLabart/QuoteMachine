<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    //Les citations reliées à l'utilisateur
    #[ORM\OneToMany(mappedBy: 'userQuoting', targetEntity: Quotes::class)]
    private Collection $quoting;

    //Les likes sont juste une relation ManyToMany avec les citations
    #[ORM\ManyToMany(targetEntity: Quotes::class, inversedBy: 'likes')]
    private Collection $Liked;

    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Category::class, orphanRemoval: true)]
    private Collection $category;

    public function __construct()
    {
        $this->quoting = new ArrayCollection();
        $this->Liked = new ArrayCollection();
        $this->category = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // garanti que chaque utilisateur possède au moins le rôle user
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection<int, quotes>
     */
    public function getQuoting(): Collection
    {
        return $this->quoting;
    }

    public function addQuoting(quotes $quoting): self
    {
        if (!$this->quoting->contains($quoting)) {
            $this->quoting->add($quoting);
            $quoting->setUserQuoting($this);
        }

        return $this;
    }

    public function removeQuoting(quotes $quoting): self
    {
        if ($this->quoting->removeElement($quoting)) {
            // set the owning side to null (unless already changed)
            if ($quoting->getUserQuoting() === $this) {
                $quoting->setUserQuoting(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Quotes>
     */
    public function getLiked(): Collection
    {
        return $this->Liked;
    }

    public function addLiked(Quotes $liked): self
    {
        if (!$this->Liked->contains($liked)) {
            $this->Liked->add($liked);
        }

        return $this;
    }

    public function removeLiked(Quotes $liked): self
    {
        $this->Liked->removeElement($liked);

        return $this;
    }

    /**
     * @return Collection<int, category>
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(category $category): self
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
            $category->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCategory(category $category): self
    {
        if ($this->category->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getCreatedBy() === $this) {
                $category->setCreatedBy(null);
            }
        }

        return $this;
    }
}
