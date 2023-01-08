<?php

namespace App\Security;

use App\Repository\UserRepository;
use App\Security\Oauth\LaravelPassportResourceOwner;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class LaravelPassportAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    private RouterInterface $router;
    private ClientRegistry $clientRegistry;
    private UserRepository $userRepository;

    public function __construct(
        RouterInterface $router,
        ClientRegistry $clientRegistry,
        UserRepository $userRepository
    ) {
        $this->router = $router;
        $this->clientRegistry = $clientRegistry;
        $this->userRepository = $userRepository;
    }

    public function supports(Request $request): bool
    {
        return 'oauth_check' === $request->attributes->get('_route');
    }

    public function start(Request $request, AuthenticationException $authException = null): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('laravel_passport');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                $passportUser = $client->fetchUserFromToken($accessToken);
                if (!$passportUser instanceof LaravelPassportResourceOwner) {
                    throw new \LogicException('Passport user must be an instance of LaravelPassportResourceOwner');
                }

                return $this->userRepository->findOrCreateFromLaravelPassport($passportUser);
            })
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }

        return new RedirectResponse($this->router->generate('app_login'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('app_home'));
    }
}
