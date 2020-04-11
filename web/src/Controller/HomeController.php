<?php

namespace App\Controller;

use App\Form\ContactFormType;
use App\Manager\UserActionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class HomeController.
 */
class HomeController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('contents/home/index.html.twig');
    }

    /**
     * @Route("/privacy", name="privacy")
     */
    public function privacy()
    {
        return $this->render('contents/home/privacy.html.twig');
    }

    /**
     * @Route("/terms", name="terms")
     */
    public function terms()
    {
        return $this->render('contents/home/terms.html.twig');
    }

    /**
     * @Route("/about", name="about")
     */
    public function about()
    {
        return $this->render('contents/home/about.html.twig');
    }

    /**
     * @Route("/help", name="help")
     */
    public function help()
    {
        return $this->render('contents/home/help.html.twig');
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(Request $request, UserActionManager $userActionManager)
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $emailSubject = $this->translator->trans('contact.subject', [
                'app_name' => $this->params->get('app.name'),
            ], 'emails');
            $email = (new TemplatedEmail())
                ->subject($emailSubject)
                ->from(new Address($data['email'], $data['name']))
                ->to($this->params->get('app.mailer_to'))
                ->htmlTemplate('emails/contact.html.twig')
                ->context($data)
            ;
            $this->mailer->send($email);

            $this->userActionManager->add(
                'contact',
                'A user has sent a contact message',
                $data
            );

            $this->addFlash(
                'success',
                $this->translator->trans('home.contact.flash.success')
            );

            return $this->redirectToRoute('contact');
        }

        return $this->render('contents/home/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
