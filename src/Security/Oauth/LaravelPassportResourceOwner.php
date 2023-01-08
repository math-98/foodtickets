<?php

namespace App\Security\Oauth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class LaravelPassportResourceOwner implements ResourceOwnerInterface
{
    private array $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Returns the identifier of the authorized resource owner.
     */
    public function getId(): int
    {
        return $this->data['id'];
    }

    /**
     * Returns the name of the authorized resource owner.
     */
    public function getName(): string
    {
        return $this->data['name'];
    }

    /**
     * Returns the avatar url of the authorized resource owner.
     */
    public function getAvatar(): string
    {
        return $this->data['avatar'];
    }

    /**
     * Returns the email of the authorized resource owner.
     */
    public function getEmail(): string
    {
        return $this->data['email'];
    }

    /**
     * Returns the access level of the authorized resource owner.
     */
    public function getAccess(): int
    {
        return $this->data['access'];
    }

    /**
     * Return all of the owner details available as an array.
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
