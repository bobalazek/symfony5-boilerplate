<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class TfaSubscriber.
 */
class TfaSubscriber implements EventSubscriberInterface
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $allowedRoutes;

    public function __construct(
        Security $security,
        RouterInterface $router,
        array $allowedRoutes = []
    ) {
        $this->security = $security;
        $this->router = $router;
        $this->allowedRoutes = $allowedRoutes;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();
        if (
            $this->security->getUser() &&
            $request->getSession()->get('tfa_in_progress')
        ) {
            $currentRoute = $request->get('_route');
            if (in_array($currentRoute, $this->allowedRoutes)) {
                return;
            }

            $url = $this->router->generate('auth.login.tfa');
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
