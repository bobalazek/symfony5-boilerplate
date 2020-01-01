<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SessionController.
 */
class SessionController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params
    ) {
        $this->translator = $translator;
        $this->params = $params;
    }

    /**
     * @Route("/session/locale/{locale}", name="session.locale")
     */
    public function locale($locale, Request $request)
    {
        $locales = $this->params->get('app.locales');
        if (in_array($locale, $locales)) {
            $request->getSession()->set('_locale', $locale);
        }

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('home');
    }
}
