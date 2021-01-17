<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ThreadRepository")
 * @ORM\Table(name="threads")
 */
class Thread implements Interfaces\ArrayInterface, TimestampableInterface
{
    use TimestampableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastNewMessageEmailCheckedAt;

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
        return 'Thread #' . $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastNewMessageEmailCheckedAt(): ?\DateTimeInterface
    {
        return $this->lastNewMessageEmailCheckedAt;
    }

    public function setLastNewMessageEmailCheckedAt(?\DateTimeInterface $lastNewMessageEmailCheckedAt): self
    {
        $this->lastNewMessageEmailCheckedAt = $lastNewMessageEmailCheckedAt;

        return $this;
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

    public function getChannel()
    {
        return md5(json_encode($this->toArray()));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
        ];
    }
}
