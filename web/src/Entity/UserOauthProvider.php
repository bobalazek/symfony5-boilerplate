<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserOauthProviderRepository")
 * @ORM\Table(name="user_oauth_providers")
 */
class UserOauthProvider implements Interfaces\StatusInterface, Interfaces\ArrayInterface
{
    use Traits\TimestampsTrait;

    const PROVIDER_FACEBOOK = 'facebook';
    const PROVIDER_GOOGLE = 'google';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $provider;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $providerId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userOauthProviders")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    public function __toString()
    {
        return $this->getUser() . ' @ ' . $this->getProvider();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getProviderId(): ?string
    {
        return $this->providerId;
    }

    public function setProviderId(string $providerId): self
    {
        $this->providerId = $providerId;

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
            'provider' => $this->getProvider(),
            'provider_id' => $this->getProviderId(),
            'created_at' => $this->getCreatedAt()
                ? $this->getCreatedAt()->format(DATE_ATOM)
                : null,
        ];
    }
}
