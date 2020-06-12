<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ThreadUserRepository")
 * @ORM\Table(name="thread_users")
 */
class ThreadUser implements Interfaces\ArrayInterface
{
    use Traits\TimestampsTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastSeenAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastActiveAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Thread", inversedBy="threadUsers")
     * @ORM\JoinColumn(nullable=true)
     */
    private $thread;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="threadUsers")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ThreadUserMessage", mappedBy="threadUser", cascade={"persist"})
     */
    private $threadUserMessages;

    public function __construct()
    {
        $this->threadUserMessages = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getThread() . ' @ ' . (string) $this->getUser();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastSeenAt(): ?\DateTimeInterface
    {
        return $this->lastSeenAt;
    }

    public function setLastSeenAt(?\DateTimeInterface $lastSeenAt): self
    {
        $this->lastSeenAt = $lastSeenAt;

        return $this;
    }

    public function getLastActiveAt(): ?\DateTimeInterface
    {
        return $this->lastActiveAt;
    }

    public function setLastActiveAt(?\DateTimeInterface $lastActiveAt): self
    {
        $this->lastActiveAt = $lastActiveAt;

        return $this;
    }

    public function getThread(): ?Thread
    {
        return $this->thread;
    }

    public function setThread(?Thread $thread): self
    {
        $this->thread = $thread;

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

    /**
     * @return Collection|ThreadUserMessage[]
     */
    public function getThreadUserMessages(): Collection
    {
        return $this->threadUserMessages;
    }

    public function addThreadUserMessage(ThreadUserMessage $threadUserMessage): self
    {
        if (!$this->threadUserMessages->contains($threadUserMessage)) {
            $this->threadUserMessages[] = $threadUserMessage;
            $threadUserMessage->setThreadUser($this);
        }

        return $this;
    }

    public function removeThreadUserMessage(ThreadUserMessage $threadUserMessage): self
    {
        if ($this->threadUserMessages->contains($threadUserMessage)) {
            $this->threadUserMessages->removeElement($threadUserMessage);
            if ($threadUserMessage->getThreadMessage() === $this) {
                $threadUserMessage->setThreadMessage(null);
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'created_at' => $this->getCreatedAt()
                ? $this->getCreatedAt()->format(DATE_ATOM)
                : null,
        ];
    }
}
