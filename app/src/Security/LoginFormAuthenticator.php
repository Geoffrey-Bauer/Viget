<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', ''); // Récupérer l'email avec le bon nom
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email, function($userIdentifier) {
                /** @var User|null */
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userIdentifier]);
                if (!$user) {
                    throw new CustomUserMessageAccountStatusException('Email could not be found.');
                }

                // Logguez l'état de l'utilisateur pour le débogage
                error_log("Authenticating user: " . $user->getEmail() . ", isBanned: " . ($user->isBanned() ? 'true' : 'false'));

                // Vérifier si l'utilisateur est banni
                if ($user->isBanned()) {
                    throw new CustomUserMessageAccountStatusException('Votre compte a été banni.');
                }

                return $user; // L'utilisateur est retourné ici s'il n'est pas banni.
            }),
            new PasswordCredentials($request->request->get('password', '')), // Récupérer le mot de passe avec le bon nom
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Vérifiez si l'utilisateur est banni
        /** @var User $user */
        $user = $token->getUser();

        if ($user instanceof User && $user->isBanned()) {
            // Redirigez vers la page d'accueil si l'utilisateur est banni
            return new RedirectResponse($this->urlGenerator->generate('app_home'));
        }

        // Redirigez vers la dernière page visitée ou vers la page d'accueil
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_home')); // Remplacez 'app_home' par votre route d'accueil
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($exception instanceof CustomUserMessageAccountStatusException) {
            error_log("Authentication failure: " . $exception->getMessage()); // Log l'erreur
            return new RedirectResponse($this->urlGenerator->generate('app_banned'));
        }

        if ($request->hasSession()) {
            // Enregistrer l'erreur d'authentification dans la session pour affichage ultérieur
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }

        return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}