<?php

namespace App\Entity\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait ImageFileTrait.
 */
trait ImageFileTrait
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @Assert\Image(
     *     maxSize="4M"
     * )
     */
    protected $imageFile;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $imageFileKey;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $imageFileUrl;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): self
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    public function getImageFileKey(): ?string
    {
        return $this->imageFileKey;
    }

    public function setImageFileKey(?string $imageFileKey): self
    {
        $this->imageFileKey = $imageFileKey;

        return $this;
    }

    public function getImageFileUrl(): ?string
    {
        return $this->imageFileUrl;
    }

    public function setImageFileUrl(?string $imageFileUrl): self
    {
        $this->imageFileUrl = $imageFileUrl;

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

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'description' => $this->getDescription(),
            'image_file_url' => $this->getImageFileUrl(),
            'created_at' => $this->getCreatedAt()
                ? $this->getCreatedAt()->format(DATE_ATOM)
                : null,
        ];
    }

    public function __toString()
    {
        return $this->getImageFileUrl();
    }
}
