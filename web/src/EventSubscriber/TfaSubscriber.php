<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\AccessMapInterface;

/**
 * Class TfaSubscriber.
 */
class TfaSubscriber implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var AccessMapInterface
     */
    private $accessMap;

    public function __construct(
        Security $security,
        RouterInterface $router/*,
        AccessMapInterface $accessMap*/
    ) {
        $this->security = $security;
        $this->router = $router;
        //$this->accessMap = $accessMap;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();
        if (
            $this->security->getUser() &&
            $request->getSession()->get('tfa_in_progress')
        ) {
            /*
            // TODO: make that work
            $patterns = $this->accessMap->getPatterns($request);
            $roles = $patterns[0];
            $roles = ['ROLE_USER'];

            // Prevent the 2FA gate on pages, that do not require authentication
            if (null === $roles) {
                return;
            }
            */

            $allowedRoutes = [
                null, // ERROR
                'logout',
                'login.tfa',
            ];

            $currentRoute = $request->get('_route');
            if (in_array($currentRoute, $allowedRoutes)) {
                return;
            }

            $url = $this->router->generate('login.tfa');
            $response = new RedirectResponse($url);
            $event->setController(function () use ($response) {
                return $response;
            });
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [['onKernelController', 20]],
        ];
    }
}
