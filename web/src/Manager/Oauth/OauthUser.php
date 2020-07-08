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
