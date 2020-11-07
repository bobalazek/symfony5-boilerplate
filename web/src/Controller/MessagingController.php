<?php

namespace App\Controller;

use App\Entity\Thread;
use App\Entity\ThreadUser;
use App\Entity\ThreadUserMessage;
use App\Entity\User;
use App\Message\ThreadUserMessage as ThreadUserMessageMessage;
use App\Repository\ORM\ThreadRepository;
use App\Repository\ORM\ThreadUserMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
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

        $search = $request->get('search');

        return $this->render('contents/messaging/index.html.twig', [
            'thread' => null,
            'threads' => $this->_getThreads(
                $this->getUser(),
                $search
            ),
            'search' => $search,
        ]);
    }

    /**
     * @Route("/messaging/threads/{id}", name="messaging.threads.detail")
     *
     * @param mixed $id
     */
    public function threadsDetail($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        /** @var ThreadRepository $threadRepository */
        $threadRepository = $this->em->getRepository(Thread::class);

        /** @var Thread|null $thread */
        $thread = $threadRepository->getByIdAndUser($id, $user);
        if (!$thread) {
            throw $this->createNotFoundException($this->translator->trans('thread_not_found', [], 'messaging'));
        }

        /** @var ThreadUser|null $threadUser */
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

        $threadUser->setLastSeenAt(new \DateTime());

        // Needs to be set this way, because the the thread search is a GET method,
        // and it seems that when using $request->get('action'),
        // it prefers the query, not post data.
        $action = $request->isMethod('POST')
            ? $request->request->get('action')
            : $request->query->get('action');

        if (
            $request->isMethod('POST') &&
            'message' === $action
        ) {
            $text = $request->get('text');
            if (!$text) {
                $error = $this->translator->trans(
                    'thread.flash.message_text_can_not_be_empty',
                    [],
                    'messaging'
                );

                if ($request->isXmlHttpRequest()) {
                    return $this->json([
                        'success' => false,
                        'error' => [
                            'message' => $error,
                        ],
                    ]);
                }

                $this->addFlash(
                    'danger',
                    $error
                );

                return $this->redirectToRoute('messaging.threads.detail', [
                    'id' => $thread->getId(),
                ]);
            }

            $threadUser->setLastActiveAt(new \DateTime());

            $threadUserMessage = new ThreadUserMessage();
            $threadUserMessage
                ->setBody($text)
                ->setThreadUser($threadUser)
            ;

            $threadUser->addThreadUserMessage($threadUserMessage);

            $this->em->flush();

            $this->dispatchMessage(
                new ThreadUserMessageMessage($threadUserMessage->getId())
            );

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'data' => $threadUserMessage->toArray(),
                ]);
            }

            $this->addFlash(
                'success',
                $this->translator->trans('thread.flash.message.success', [], 'messaging')
            );

            return $this->redirectToRoute('messaging.threads.detail', [
                'id' => $thread->getId(),
            ]);
        }

        $this->em->flush();

        $limit = 20;
        $after = (int) $request->get('after');
        $before = (int) $request->get('before');

        /** @var QueryBuilder $threadUserMessagesQueryBuilder */
        $threadUserMessagesQueryBuilder = $this->em
            ->getRepository(ThreadUserMessage::class)
            ->createQueryBuilder('tum')
            ->leftJoin('tum.threadUser', 'tu')
            ->where('tu.thread = :thread')
            ->orderBy('tum.createdAt', 'DESC')
            ->setParameter('thread', $thread)
            ->setMaxResults($limit)
            ->setFirstResult(0)
        ;

        if ($after) {
            $threadUserMessagesQueryBuilder
                ->andWhere('tum.id > :after')
                ->setParameter('after', $after)
            ;
        }

        if ($before) {
            $threadUserMessagesQueryBuilder
                ->andWhere('tum.id < :before')
                ->setParameter('before', $before)
            ;
        }

        /** @var ThreadUserMessage[] $threadUserMessages */
        $threadUserMessages = $threadUserMessagesQueryBuilder
            ->getQuery()
            ->getResult()
        ;

        $threadUserMessages = array_reverse($threadUserMessages);
        $threadUserMessagesCount = count($threadUserMessages);

        $search = $request->get('search');

        return $this->render('contents/messaging/index.html.twig', [
            'thread' => $thread,
            'thread_user_messages' => $threadUserMessages,
            'thread_user_messages_count' => $threadUserMessagesCount,
            'thread_user_messages_has_more' => $threadUserMessagesCount === $limit,
            'threads' => $this->_getThreads(
                $user,
                $search
            ),
            'search' => $search,
        ]);
    }

    private function _getThreads(User $user, ?string $search)
    {
        $threadsArray = [];

        if ($search) {
            $search = strtolower($search);
        }

        // TODO: implement pagination

        /** @var ThreadUserMessageRepository $threadUserMessageRepository */
        $threadUserMessageRepository = $this->em->getRepository(ThreadUserMessage::class);

        /** @var QueryBuilder $threadsQueryBuilder */
        $threadsQueryBuilder = $this->em
            ->getRepository(Thread::class)
            ->createQueryBuilder('t')
            ->leftJoin('t.threadUsers', 'tu')
            ->leftJoin('tu.threadUserMessages', 'tum')
            ->where('tu.user = :user')
            ->orderBy('tum.createdAt', 'DESC')
            ->setParameter('user', $this->getUser())
        ;

        // TODO: figure why it doesn't work
        /*
        if ($search) {
            $threadsQueryBuilder
                ->leftJoin('tu.user', 'u')
                ->andWhere($threadsQueryBuilder->expr()->orX(
                    //$threadsQueryBuilder->expr()->like('u.name', ':search'),
                    $threadsQueryBuilder->expr()->like("CONCAT(u.firstName, ' ', u.lastName)", ':search'),
                    $threadsQueryBuilder->expr()->like('u.username', ':search')
                ))
                ->setParameter('search', '%' . $search . '%')
            ;
        }
        */

        /** @var Thread[] $threads */
        $threads = $threadsQueryBuilder
            ->getQuery()
            ->getResult()
        ;
        foreach ($threads as $thread) {
            $lastMessage = null;
            $lastMessageDatetime = null;
            $userNames = [];
            $matches = !$search; // Temporary solution for search

            $threadUsers = $thread->getThreadUsers();
            foreach ($threadUsers as $threadUser) {
                $threadUserUser = $threadUser->getUser();
                if ($user === $threadUserUser) {
                    continue;
                }

                $userNames[] = $threadUser->getUser()->getFullName();

                // Just a temporary solution for the search
                $threadUserUserName = strtolower($threadUserUser->getFullName());
                $threadUserUserUsername = strtolower($threadUserUser->getUsername()); // yo dawg, I heard you like ...

                if (
                    $search && (
                        false !== strpos($threadUserUserName, $search) ||
                        false !== strpos($threadUserUserUsername, $search)
                    )
                ) {
                    $matches = true;
                }
            }

            if (!$matches) {
                continue;
            }

            // TODO: do that probably outside the loop & map it?
            /** @var ThreadUserMessage|null $threadUserMessage */
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
                    : $lastMessageUser->getFullName();
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
