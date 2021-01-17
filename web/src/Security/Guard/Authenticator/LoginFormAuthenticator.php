<?php

namespace App\Security\Guard\Authenticator;

use App\Entity\User;
use App\Manager\UserActionManager;
use App\Manager\UserDeviceManager;
use App\Manager\UserTfaManager;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Class LoginFormAuthenticator.
 */
class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var UserActionManager
     */
    private $userActionManager;

    /**
     * @var UserDeviceManager
     */
    private $userDeviceManager;

    /**
     * @var UserTfaManager
     */
    private $userTfaManager;

    public function __construct(
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        UserPasswordEncoderInterface $passwordEncoder,
        UserActionManager $userActionManager,
        UserDeviceManager $userDeviceManager,
        UserTfaManager $userTfaManager
    ) {
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->userActionManager = $userActionManager;
        $this->userDeviceManager = $userDeviceManager;
        $this->userTfaManager = $userTfaManager;
    }

    public function supports(Request $request)
    {
        return 'auth.login' === $request->attributes->get('_route') &&
            $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->loadUserByUsername($credentials['username']);
        if (!$user) {
            throw new CustomUserMessageAuthenticationException('A user with this username or email could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        $user = $token->getUser();
        $method = 'username';

        // If login happened via OAuth
        $route = $request->attributes->get('_route');
        if (
            'oauth.' === substr($route, 0, 6) &&
            '.callback' === substr($route, -9)
        ) {
            $routeExploded = explode('.', $route);
            $method = $routeExploded[1];
        }

        $this->userActionManager->add(
            'login',
            'User has logged in',
            [
                'method' => $method,
            ],
            $user
        );

        $isUserDeviceTrusted = $this->userDeviceManager->isCurrentTrusted($user, $request);

        // Will return false when disabled
        $tfaDefaultMethod = $this->userTfaManager->getDefaultMethod($user);
        if (
            $tfaDefaultMethod &&
            !$isUserDeviceTrusted
        ) {
            $request->getSession()->set('tfa_method', $tfaDefaultMethod);
            $request->getSession()->set('tfa_in_progress', true);
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $credentials = $exception->getToken()->getCredentials();

        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->loadUserByUsername($credentials['username']);

        $this->userActionManager->add(
            'login.fail',
            'A user has tried to login',
            [
                'username' => $credentials['username'],
            ],
            $user
        );

        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }

        $url = $this->getLoginUrl();

        return new RedirectResponse($url);
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('auth.login');
    }
}
