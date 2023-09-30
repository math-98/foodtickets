<?php

namespace App\Security\Oauth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class AuthentikProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected string $base_auth_uri;

    /**
     * Constructs an OAuth 2.0 service provider.
     *
     * @throws \Exception
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        if (empty($options['base_auth_uri'])) {
            throw new \Exception('The "base_auth_uri" option must be defined.');
        }

        parent::__construct($options, $collaborators);
    }

    public function getAuthUri(): string
    {
        $uri = $this->base_auth_uri;
        if (str_ends_with($uri, '/')) {
            $uri = substr($uri, 0, -1);
        }

        return $uri;
    }

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->getAuthUri().'/application/o/authorize/';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * Eg. https://oauth.service.com/token
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->getAuthUri().'/application/o/token/';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->getAuthUri().'/application/o/userinfo/';
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details of the resource owner, rather than all the available scopes.
     */
    protected function getDefaultScopes(): array
    {
        return [];
    }

    /**
     * Checks a provider response for errors.
     *
     * @param array|string $data Parsed response data
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException($data['message'] ?? $response->getReasonPhrase(), $response->getStatusCode(), $response->getBody());
        }
    }

    /**
     * Generates a resource owner object from a successful resource owner details request.
     */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        return new AuthentikResourceOwner($response);
    }
}
