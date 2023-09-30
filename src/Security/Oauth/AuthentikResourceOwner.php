<?php

namespace App\Security\Oauth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class AuthentikResourceOwner implements ResourceOwnerInterface
{
    private array $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Returns the identifier of the authorized resource owner.
     */
    public function getId(): string
    {
        return $this->data['sub'];
    }

    /**
     * Returns the name of the authorized resource owner.
     */
    public function getName(): string
    {
        return $this->data['name'];
    }

    /**
     * Returns the email of the authorized resource owner.
     */
    public function getEmail(): string
    {
        return $this->data['email'];
    }

    /**
     * Return all of the owner details available as an array.
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
