<?php

namespace App\Manager\Oauth;

/**
 * Class OauthUser.
 */
class OauthUser
{
    /**
     * @return string
     */
    private $id;

    /**
     * @return string
     */
    private $email;

    /**
     * @return string
     */
    private $name;

    /**
     * @return string
     */
    private $firstName;

    /**
     * @return string
     */
    private $lastName;

    /**
     * @return array
     */
    private $rawData;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

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

    public function setName(string $name): self
    {
        $this->name = $name;

        $nameExploded = explode(' ', $name);
        if (count($nameExploded) >= 2) {
            if (!$this->getFirstName()) {
                $this->setFirstName($nameExploded[0]);
            }

            if (!$this->getLastName()) {
                $this->setLastName(end($nameExploded));
            }
        }

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    public function setRawData(array $rawData): self
    {
        $this->rawData = $rawData;

        return $this;
    }
}
