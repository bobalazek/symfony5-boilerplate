<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\SoftDeletableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletableTrait;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * We need the \Serializable interface, because the user is also in the abstracttoken
 *   that needs to be serialized, but because we have a File ($imageFile),
 *   that can't be serialized.
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users")
 * @Vich\Uploadable()
 * @UniqueEntity(
 *   fields={"username"},
 *   message="There is already an account with this username",
 *   groups={"register", "settings"}
 * )
 * @UniqueEntity(
 *   fields={"email"},
 *   message="There is already an account with this email",
 *   groups={"register", "settings"}
 * )
 */
class User implements UserInterface, EquatableInterface, \Serializable, Interfaces\ArrayInterface, TimestampableInterface, SoftDeletableInterface
{
    use TimestampableTrait;
    use SoftDeletableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(groups={"register", "settings"})
     * @Assert\Length(min=4, max=255, groups={"register", "settings"})
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Email(groups={"register", "password_reset_request", "settings"})
     * @Assert\NotBlank(groups={"register", "password_reset_request", "settings"})
     * @Assert\Length(min=5, max=255, groups={"register", "settings"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"register", "settings"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $emailConfirmCode;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\NotBlank(groups={"register", "password_reset", "settings.password"})
     * @Assert\Length(min=6, max=4096, groups={"register", "password_reset", "settings.password"})
     */
    private $plainPassword;

    /**
     * @SecurityAssert\UserPassword(
     *     message="Wrong value for your current password",
     *     groups={"settings.password"}
     * )
     */
    private $oldPlainPassword;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $newEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $newEmailConfirmCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $deletionConfirmCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $passwordResetCode;

    /**
     * @Vich\UploadableField(
     *   mapping="user_image",
     *   fileNameProperty="embeddedFile.name",
     *   size="embeddedFile.size",
     *   mimeType="embeddedFile.mimeType",
     *   originalName="embeddedFile.originalName",
     *   dimensions="embeddedFile.dimensions"
     * )
     * @Assert\Image(
     *     maxSize="4M",
     *     allowLandscape=false,
     *     allowLandscapeMessage="Only squared images are allowed",
     *     allowPortrait=false,
     *     allowPortraitMessage="Only squared images are allowed",
     *     groups={"settings.image"}
     * )
     */
    private $imageFile;

    /**
     * @ORM\Embedded(class="Vich\UploaderBundle\Entity\File")
     *
     * @var EmbeddedFile
     */
    private $embeddedFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatarImage;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $bio;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     * @Assert\Country()
     */
    private $countryCode;

    /**
     * @ORM\Column(type="boolean")
     */
    private $locked = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lockedReason;

    /**
     * @ORM\Column(type="boolean")
     */
    private $private = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $tfaEnabled = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $emailConfirmedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastPasswordResetRequestedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastNewEmailConfirmationEmailSentAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastDeletionConfirmationEmailSentAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserAction", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userActions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserExport", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userExports;

    /**
     * This user blocks whom?
     *
     * @ORM\OneToMany(targetEntity="App\Entity\UserBlock", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userBlocks;

    /**
     * This user is blocked by whom?
     *
     * @ORM\OneToMany(targetEntity="App\Entity\UserBlock", mappedBy="userBlocked", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userBlocked;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserFollower", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userFollowers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserFollower", mappedBy="userFollowing", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userFollowing;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserNotification", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userNotifications;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserPoint", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userPoints;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserDevice", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userDevices;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserOauthProvider", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userOauthProviders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserTfaMethod", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userTfaMethods;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserTfaEmail", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userTfaEmails;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserTfaRecoveryCode", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userTfaRecoveryCodes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ThreadUser", mappedBy="user")
     */
    private $threadUsers;

    public function __construct()
    {
        $this->embeddedFile = new EmbeddedFile();
        $this->userActions = new ArrayCollection();
        $this->userExports = new ArrayCollection();
        $this->userBlocks = new ArrayCollection();
        $this->userBlocked = new ArrayCollection();
        $this->userFollowers = new ArrayCollection();
        $this->userNotifications = new ArrayCollection();
        $this->userPoints = new ArrayCollection();
        $this->userDevices = new ArrayCollection();
        $this->userOauthProviders = new ArrayCollection();
        $this->userTfaMethods = new ArrayCollection();
        $this->userTfaEmails = new ArrayCollection();
        $this->userTfaRecoveryCodes = new ArrayCollection();
        $this->threadUsers = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getUsername() . ' (' . $this->getEmail() . ')';
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmailConfirmCode(): ?string
    {
        return $this->emailConfirmCode;
    }

    public function setEmailConfirmCode(?string $emailConfirmCode): self
    {
        $this->emailConfirmCode = $emailConfirmCode;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles());
    }

    public function isSuperAdmin(): bool
    {
        return in_array('ROLE_SUPER_ADMIN', $this->getRoles());
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getOldPlainPassword(): ?string
    {
        return $this->oldPlainPassword;
    }

    public function setOldPlainPassword(?string $oldPlainPassword): self
    {
        $this->oldPlainPassword = $oldPlainPassword;

        return $this;
    }

    public function getNewEmail(): ?string
    {
        return $this->newEmail;
    }

    public function setNewEmail(?string $newEmail): self
    {
        $this->newEmail = $newEmail;

        return $this;
    }

    public function getNewEmailConfirmCode(): ?string
    {
        return $this->newEmailConfirmCode;
    }

    public function setNewEmailConfirmCode(?string $newEmailConfirmCode): self
    {
        $this->newEmailConfirmCode = $newEmailConfirmCode;

        return $this;
    }

    public function getDeletionConfirmCode(): ?string
    {
        return $this->deletionConfirmCode;
    }

    public function setDeletionConfirmCode(?string $deletionConfirmCode): self
    {
        $this->deletionConfirmCode = $deletionConfirmCode;

        return $this;
    }

    public function getPasswordResetCode(): ?string
    {
        return $this->passwordResetCode;
    }

    public function setPasswordResetCode(?string $passwordResetCode): self
    {
        $this->passwordResetCode = $passwordResetCode;

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

    public function getEmbeddedFile(): ?EmbeddedFile
    {
        return $this->embeddedFile;
    }

    public function setEmbeddedFile(?EmbeddedFile $embeddedFile): self
    {
        $this->embeddedFile = $embeddedFile;

        return $this;
    }

    public function getAvatarImage(): ?string
    {
        return $this->avatarImage;
    }

    public function setAvatarImage(?string $avatarImage): self
    {
        $this->avatarImage = $avatarImage;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getLocked(): bool
    {
        return $this->locked;
    }

    public function isLocked(): bool
    {
        return $this->getLocked();
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    public function getLockedReason(): ?string
    {
        return $this->lockedReason;
    }

    public function setLockedReason(?string $lockedReason): self
    {
        $this->lockedReason = $lockedReason;

        return $this;
    }

    public function getPrivate(): bool
    {
        return $this->private;
    }

    public function isPrivate(): bool
    {
        return $this->getPrivate();
    }

    public function setPrivate(bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    public function getTfaEnabled(): bool
    {
        return $this->tfaEnabled;
    }

    public function isTfaEnabled(): bool
    {
        return $this->getTfaEnabled();
    }

    public function setTfaEnabled(bool $tfaEnabled): self
    {
        $this->tfaEnabled = $tfaEnabled;

        return $this;
    }

    public function getEmailConfirmedAt(): ?\DateTimeInterface
    {
        return $this->emailConfirmedAt;
    }

    public function setEmailConfirmedAt(?\DateTimeInterface $emailConfirmedAt): self
    {
        $this->emailConfirmedAt = $emailConfirmedAt;

        return $this;
    }

    public function getLastPasswordResetRequestedAt(): ?\DateTimeInterface
    {
        return $this->lastPasswordResetRequestedAt;
    }

    public function setLastPasswordResetRequestedAt(?\DateTimeInterface $lastPasswordResetRequestedAt): self
    {
        $this->lastPasswordResetRequestedAt = $lastPasswordResetRequestedAt;

        return $this;
    }

    public function getLastNewEmailConfirmationEmailSentAt(): ?\DateTimeInterface
    {
        return $this->lastNewEmailConfirmationEmailSentAt;
    }

    public function setLastNewEmailConfirmationEmailSentAt(?\DateTimeInterface $lastNewEmailConfirmationEmailSentAt): self
    {
        $this->lastNewEmailConfirmationEmailSentAt = $lastNewEmailConfirmationEmailSentAt;

        return $this;
    }

    public function getLastDeletionConfirmationEmailSentAt(): ?\DateTimeInterface
    {
        return $this->lastDeletionConfirmationEmailSentAt;
    }

    public function setLastDeletionConfirmationEmailSentAt(?\DateTimeInterface $lastDeletionConfirmationEmailSentAt): self
    {
        $this->lastDeletionConfirmationEmailSentAt = $lastDeletionConfirmationEmailSentAt;

        return $this;
    }

    /**
     * @return Collection|UserAction[]
     */
    public function getUserActions(): Collection
    {
        return $this->userActions;
    }

    public function addUserAction(UserAction $userAction): self
    {
        if (!$this->userActions->contains($userAction)) {
            $this->userActions[] = $userAction;
            $userAction->setUser($this);
        }

        return $this;
    }

    public function removeUserAction(UserAction $userAction): self
    {
        if ($this->userActions->contains($userAction)) {
            $this->userActions->removeElement($userAction);
            if ($userAction->getUser() === $this) {
                $userAction->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserExport[]
     */
    public function getUserExports(): Collection
    {
        return $this->userExports;
    }

    public function addUserExport(UserExport $userExport): self
    {
        if (!$this->userExports->contains($userExport)) {
            $this->userExports[] = $userExport;
            $userExport->setUser($this);
        }

        return $this;
    }

    public function removeUserExport(UserExport $userExport): self
    {
        if ($this->userExports->contains($userExport)) {
            $this->userExports->removeElement($userExport);
            if ($userExport->getUser() === $this) {
                $userExport->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // The bcrypt and argon2i algorithms don't require a separate salt.
        // You *may* need a real salt if you choose a different encoder.

        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
        $this->oldPlainPassword = null;
    }

    /**
     * @see EquatableInterface
     */
    public function isEqualTo(UserInterface $user)
    {
        /** @var User $user */
        if ($this->isLocked() !== $user->isLocked()) {
            return false;
        }

        return true;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->roles,
        ]);
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->roles) = unserialize($serialized);
    }

    /**
     * @return Collection|UserBlock[]
     */
    public function getUserBlocks(): Collection
    {
        return $this->userBlocks;
    }

    public function addUserBlock(UserBlock $userBlock): self
    {
        if (!$this->userBlocks->contains($userBlock)) {
            $this->userBlocks[] = $userBlock;
            $userBlock->setUser($this);
        }

        return $this;
    }

    public function removeUserBlock(UserBlock $userBlock): self
    {
        if ($this->userBlocks->contains($userBlock)) {
            $this->userBlocks->removeElement($userBlock);
            if ($userBlock->getUser() === $this) {
                $userBlock->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserBlock[]
     */
    public function getUserBlocked(): Collection
    {
        return $this->userBlocked;
    }

    public function addUserBlocked(UserBlock $userBlocked): self
    {
        if (!$this->userBlocked->contains($userBlocked)) {
            $this->userBlocked[] = $userBlocked;
            $userBlocked->setUserBlocked($this);
        }

        return $this;
    }

    public function removeUserBlocked(UserBlock $userBlocked): self
    {
        if ($this->userFollowing->contains($userBlocked)) {
            $this->userFollowing->removeElement($userBlocked);
            if ($userBlocked->getUserBlocked() === $this) {
                $userBlocked->setUserBlocked(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserFollower[]
     */
    public function getUserFollowers(): Collection
    {
        return $this->userFollowers;
    }

    public function addUserFollower(UserFollower $userFollower): self
    {
        if (!$this->userFollowers->contains($userFollower)) {
            $this->userFollowers[] = $userFollower;
            $userFollower->setUser($this);
        }

        return $this;
    }

    public function removeUserFollower(UserFollower $userFollower): self
    {
        if ($this->userFollowers->contains($userFollower)) {
            $this->userFollowers->removeElement($userFollower);
            if ($userFollower->getUser() === $this) {
                $userFollower->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserFollower[]
     */
    public function getUserFollowing(): Collection
    {
        return $this->userFollowing;
    }

    public function addUserFollowing(UserFollower $userFollowing): self
    {
        if (!$this->userFollowing->contains($userFollowing)) {
            $this->userFollowing[] = $userFollowing;
            $userFollowing->setUserFollowing($this);
        }

        return $this;
    }

    public function removeUserFollowing(UserFollower $userFollowing): self
    {
        if ($this->userFollowing->contains($userFollowing)) {
            $this->userFollowing->removeElement($userFollowing);
            if ($userFollowing->getUserFollowing() === $this) {
                $userFollowing->setUserFollowing(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserNotification[]
     */
    public function getUserNotifications(): Collection
    {
        return $this->userNotifications;
    }

    public function addUserNotification(UserNotification $userNotification): self
    {
        if (!$this->userNotifications->contains($userNotification)) {
            $this->userNotifications[] = $userNotification;
            $userNotification->setUser($this);
        }

        return $this;
    }

    public function removeUserNotification(UserNotification $userNotification): self
    {
        if ($this->userNotifications->contains($userNotification)) {
            $this->userNotifications->removeElement($userNotification);
            if ($userNotification->getUser() === $this) {
                $userNotification->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserPoint[]
     */
    public function getUserPoints(): Collection
    {
        return $this->userPoints;
    }

    public function addUserPoint(UserPoint $userPoint): self
    {
        if (!$this->userPoints->contains($userPoint)) {
            $this->userPoints[] = $userPoint;
            $userPoint->setUser($this);
        }

        return $this;
    }

    public function removeUserPoint(UserPoint $userPoint): self
    {
        if ($this->userPoints->contains($userPoint)) {
            $this->userPoints->removeElement($userPoint);
            if ($userPoint->getUser() === $this) {
                $userPoint->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserDevice[]
     */
    public function getUserDevices(): Collection
    {
        return $this->userDevices;
    }

    public function addUserDevice(UserDevice $userDevice): self
    {
        if (!$this->userDevices->contains($userDevice)) {
            $this->userDevices[] = $userDevice;
            $userDevice->setUser($this);
        }

        return $this;
    }

    public function removeUserDevice(UserDevice $userDevice): self
    {
        if ($this->userDevices->contains($userDevice)) {
            $this->userDevices->removeElement($userDevice);
            if ($userDevice->getUser() === $this) {
                $userDevice->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserOauthProvider[]
     */
    public function getUserOauthProviders(): Collection
    {
        return $this->userOauthProviders;
    }

    public function addUserOauthProvider(UserOauthProvider $userOauthProvider): self
    {
        if (!$this->userOauthProviders->contains($userOauthProvider)) {
            $this->userOauthProviders[] = $userOauthProvider;
            $userOauthProvider->setUser($this);
        }

        return $this;
    }

    public function removeUserOauthProvider(UserOauthProvider $userOauthProvider): self
    {
        if ($this->userOauthProviders->contains($userOauthProvider)) {
            $this->userOauthProviders->removeElement($userOauthProvider);
            if ($userOauthProvider->getUser() === $this) {
                $userOauthProvider->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserTfaMethod[]
     */
    public function getUserTfaMethods(): Collection
    {
        return $this->userTfaMethods;
    }

    public function addUserTfaMethod(UserTfaMethod $userTfaMethod): self
    {
        if (!$this->userTfaMethods->contains($userTfaMethod)) {
            $this->userTfaMethods[] = $userTfaMethod;
            $userTfaMethod->setUser($this);
        }

        return $this;
    }

    public function removeUserTfaMethod(UserTfaMethod $userTfaMethod): self
    {
        if ($this->userTfaMethods->contains($userTfaMethod)) {
            $this->userTfaMethods->removeElement($userTfaMethod);
            if ($userTfaMethod->getUser() === $this) {
                $userTfaMethod->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserTfaEmail[]
     */
    public function getUserTfaEmails(): Collection
    {
        return $this->userTfaEmails;
    }

    public function addUserTfaEmail(UserTfaEmail $userTfaEmail): self
    {
        if (!$this->userTfaEmails->contains($userTfaEmail)) {
            $this->userTfaEmails[] = $userTfaEmail;
            $userTfaEmail->setUser($this);
        }

        return $this;
    }

    public function removeUserTfaEmail(UserTfaEmail $userTfaEmail): self
    {
        if ($this->userTfaEmails->contains($userTfaEmail)) {
            $this->userTfaEmails->removeElement($userTfaEmail);
            if ($userTfaEmail->getUser() === $this) {
                $userTfaEmail->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserTfaRecoveryCode[]
     */
    public function getUserTfaRecoveryCodes(): Collection
    {
        return $this->userTfaRecoveryCodes;
    }

    public function addUserTfaRecoveryCode(UserTfaRecoveryCode $userTfaRecoveryCode): self
    {
        if (!$this->userTfaRecoveryCodes->contains($userTfaRecoveryCode)) {
            $this->userTfaRecoveryCodes[] = $userTfaRecoveryCode;
            $userTfaRecoveryCode->setUser($this);
        }

        return $this;
    }

    public function removeUserTfaRecoveryCode(UserTfaRecoveryCode $userTfaRecoveryCode): self
    {
        if ($this->userTfaRecoveryCodes->contains($userTfaRecoveryCode)) {
            $this->userTfaRecoveryCodes->removeElement($userTfaRecoveryCode);
            if ($userTfaRecoveryCode->getUser() === $this) {
                $userTfaRecoveryCode->setUser(null);
            }
        }

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
            $threadUser->setUser($this);
        }

        return $this;
    }

    public function removeThreadUser(ThreadUser $threadUser): self
    {
        if ($this->threadUsers->contains($threadUser)) {
            $this->threadUsers->removeElement($threadUser);
            if ($threadUser->getUser() === $this) {
                $threadUser->setUser(null);
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'roles' => $this->getRoles(),
            'new_email' => $this->getNewEmail(),
            'bio' => $this->getBio(),
            'city' => $this->getCity(),
            'country_code' => $this->getCountryCode(),
            'locked' => $this->isLocked(),
            'locked_reason' => $this->getLockedReason(),
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
        ];
    }
}
