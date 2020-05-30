<?php

namespace App\Controller;

use App\Entity\Thread;
use App\Entity\ThreadUser;
use App\Entity\ThreadUserMessage;
use App\Entity\User;
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
            'threads' => $this->_getThreads($this->getUser()),
        ]);
    }

    /**
     * @Route("/messaging/threads/{id}", name="messaging.threads.detail")
     *
     * @param mixed $id
     * @param mixed $id
     */
    public function threadsDetail($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $thread = $this->em
            ->getRepository(Thread::class)
            ->getByIdAndUser($id, $user)
        ;
        if (!$thread) {
            throw $this->createNotFoundException($this->translator->trans('thread_not_found', [], 'messaging'));
        }

        if (
            $request->isMethod('POST') &&
            'message' === $request->request->get('action')
        ) {
            $text = $request->request->get('text');
            if (!$text) {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('thread.flash.message_text_can_not_be_empty', [], 'messaging')
                );

                return $this->redirectToRoute('messaging.threads.detail', [
                    'id' => $thread->getId(),
                ]);
            }

            $threadUser = $this->em
                ->getRepository(ThreadUser::class)
                ->findOneBy([
                    'thread' => $thread,
                    'user' => $user,
                ])
            ;
            if (!$threadUser) {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('thread.flash.thread_user_not_found', [], 'messaging')
                );

                return $this->redirectToRoute('messaging.threads.detail', [
                    'id' => $thread->getId(),
                ]);
            }

            $threadUserMessage = new ThreadUserMessage();
            $threadUserMessage
                ->setBody($text)
                ->setThreadUser($threadUser)
            ;

            $threadUser->addThreadUserMessage($threadUserMessage);

            $this->em->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('thread.flash.message.success', [], 'messaging')
            );

            return $this->redirectToRoute('messaging.threads.detail', [
                'id' => $thread->getId(),
            ]);
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

        $threadUserMessages = array_reverse($threadUserMessages);

        return $this->render('contents/messaging/index.html.twig', [
            'thread' => $thread,
            'thread_user_messages' => $threadUserMessages,
            'threads' => $this->_getThreads($user),
        ]);
    }

    private function _getThreads(User $user)
    {
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
            $lastMessageDatetime = null;
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
                ->orderBy('tum.createdAt', 'DESC')
                ->setParameter('thread', $thread)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult()
            ;

            if ($threadUserMessage) {
                $lastMessageUser = $threadUserMessage->getThreadUser()->getUser();
                $lastMessagePrefix = $lastMessageUser === $user
                    ? $this->translator->trans('You')
                    : $lastMessageUser->getName();
                $lastMessage = $lastMessagePrefix . ': ' . $threadUserMessage->getBody();
                $lastMessageDatetime = $threadUserMessage->getCreatedAt();
            }

            $threadsArray[] = [
                'id' => $thread->getId(),
                'title' => implode(', ', $userNames),
                'last_message' => $lastMessage,
                'last_message_datetime' => $lastMessageDatetime,
            ];
        }

        return $threadsArray;
    }
}
