<?php

namespace App\Controller;

use App\Entity\Thread;
use App\Entity\ThreadUserMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class MessagingController.
 */
class MessagingController extends AbstractUsersController
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ParameterBagInterface
     */
    protected $params;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * AbstractUsersController constructor.
     */
    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
    }

    /**
     * @Route("/messaging", name="messaging")
     */
    public function index(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('contents/messaging/index.html.twig', [
            'thread' => null,
            'threads' => $this->_getThreads(),
        ]);
    }

    /**
     * @Route("/messaging/{thread_id}", name="messaging.thread")
     *
     * @param mixed $thread_id
     */
    public function thread($thread_id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $thread = null;

        // TODO

        return $this->render('contents/messaging/index.html.twig', [
            'thread' => $thread,
            'threads' => $this->_getThreads(),
        ]);
    }

    private function _getThreads()
    {
        $user = $this->getUser();

        $threadsArray = [];
        $threads = $this->em
            ->getRepository(Thread::class)
            ->createQueryBuilder('t')
            ->leftJoin('t.threadUsers', 'tu')
            ->leftJoin('tu.threadUserMessages', 'tum')
            ->where('tu.user = :user')
            ->orderBy('tum.createdAt', 'DESC')
            ->setParameter('user', $this->getUser())
            ->getQuery()
            ->getResult()
        ;
        foreach ($threads as $thread) {
            $lastMessage = null;
            $lastMessageTime = null;
            $userNames = [];

            $threadUsers = $thread->getThreadUsers();
            foreach ($threadUsers as $threadUser) {
                $userNames[] = $threadUser->getUser()->getName();
            }

            $threadUserMessage = $this->em
                ->getRepository(ThreadUserMessage::class)
                ->createQueryBuilder('tum')
                ->leftJoin('tum.threadUsers', 'tu')
                ->where('tu.thread = :thread')
                ->setParameter('thread', $thread)
                ->getQuery()
                ->getOneOrNullResult()
            ;

            if ($threadUserMessage) {
                $lastMessage = $threadUserMessage->getBody();
                $lastMessageTime = $threadUserMessage->getCreatedAt();
            }

            $threadsArray[] = [
                'id' => $thread->getId(),
                'title' => implode(', ', $userNames),
                'last_message' => $lastMessage,
                'last_message_time' => $lastMessageTime,
            ];
        }
    }
}
