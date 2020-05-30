<?php

namespace App\Controller;

use App\Entity\Thread;
use App\Entity\ThreadUserMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class MessagingController.
 */
class MessagingController extends AbstractController
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
     * @Route("/messaging/{id}", name="messaging.thread")
     *
     * @param mixed $thread_id
     * @param mixed $id
     */
    public function thread($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $thread = $this->em
            ->getRepository(Thread::class)
            ->getByIdAndUser($id, $this->getUser())
        ;
        if (!$thread) {
            throw $this->createNotFoundException($this->translator->trans('thread_not_found', [], 'messaging'));
        }

        $threadUserMessages = $this->em
            ->getRepository(ThreadUserMessage::class)
            ->createQueryBuilder('tum')
            ->leftJoin('tum.threadUser', 'tu')
            ->where('tu.thread = :thread')
            ->orderBy('tum.createdAt', 'DESC')
            ->setParameter('thread', $thread)
            ->getQuery()
            ->getResult()
        ;

        return $this->render('contents/messaging/index.html.twig', [
            'thread' => $thread,
            'thread_user_messages' => $threadUserMessages,
            'threads' => $this->_getThreads(),
        ]);
    }

    private function _getThreads()
    {
        $user = $this->getUser();

        $threadUserMessageRepository = $this->em
            ->getRepository(ThreadUserMessage::class)
        ;

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

            // TODO: we could probably cache that, since it's not going to change anyway?

            $threadUsers = $thread->getThreadUsers();
            foreach ($threadUsers as $threadUser) {
                if ($user === $threadUser->getUser()) {
                    continue;
                }

                $userNames[] = $threadUser->getUser()->getName();
            }

            $threadUserMessage = $threadUserMessageRepository
                ->createQueryBuilder('tum')
                ->leftJoin('tum.threadUser', 'tu')
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

        return $threadsArray;
    }
}
