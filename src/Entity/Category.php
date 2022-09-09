<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[Vich\Uploadable]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    //Les citations reliées à la catégorie
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Quotes::class)]
    private Collection $Quotes;

    //L'utilisateur qui créer la catégorie
    #[ORM\ManyToOne(inversedBy: 'category')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'category', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'string')]
    private ?string $imageName = null;

    #[ORM\Column(type: 'integer')]
    private ?int $imageSize = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;
    
    public function __construct()
    {
        $this->Quotes = new ArrayCollection();
        $this->updatedAt = new \DateTime();
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
    
    /**
     * @return Collection<int, Quotes>
     */
    public function getQuotes(): Collection
    {
        return $this->Quotes;
    }
    
    public function addQuote(Quotes $quote): self
    {
        if (!$this->Quotes->contains($quote)) {
            $this->Quotes->add($quote);
            $quote->setCategory($this);
        }
        
        return $this;
    }
    
    public function removeQuote(Quotes $quote): self
    {
        if ($this->Quotes->removeElement($quote)) {
            // set the owning side to null (unless already changed)
            if ($quote->getCategory() === $this) {
                $quote->setCategory(null);
            }
        }
        
        return $this;
    }
    
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }
    
    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;
        
        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
    
        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }
    
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }
    
    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }
    
    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
}