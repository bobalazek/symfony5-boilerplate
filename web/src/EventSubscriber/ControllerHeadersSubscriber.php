<?php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ControllerHeadersSubscriber.
 */
class ControllerHeadersSubscriber implements EventSubscriberInterface
{
    /**
     * @var ParameterBagInterface
     */
    protected $params;

    public function __construct(
        ParameterBagInterface $params
    ) {
        $this->params = $params;
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $event->getResponse()->headers->set(
            'X-App-Version',
            $this->params->get('app.version')
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [['onKernelResponse', 20]],
        ];
    }
}
