<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ThreadRepository")
 * @ORM\Table(name="threads")
 */
class Thread implements Interfaces\StatusInterface, Interfaces\ArrayInterface
{
    use Traits\TimestampsTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ThreadUser", mappedBy="thread")
     */
    private $threadUsers;

    public function __construct()
    {
        $this->threadUsers = new ArrayCollection();
    }

    public function __toString()
    {
        $threadUserNames = [];
        $threadUsers = $this->getThreadUsers();
        foreach ($threadUsers as $threadUser) {
            $threadUserNames[] = $threadUser->getUser();
        }

        if ($threadUserNames) {
            return implode(', ', $threadUserNames);
        }

        return $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|ThreadUser[]
     */
    public function getThreadUsers(): Collection
    {
        return $this->threadUsers;
    }

    public function addThreadUser(ThreadUser $threadUser): self
    {
        if (!$this->threadUsers->contains($threadUser)) {
            $this->threadUsers[] = $threadUser;
            $threadUser->setThread($this);
        }

        return $this;
    }

    public function removeThreadUser(ThreadUser $threadUser): self
    {
        if ($this->threadUsers->contains($threadUser)) {
            $this->threadUsers->removeElement($threadUser);
            if ($threadUser->getThread() === $this) {
                $threadUser->setThread(null);
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
