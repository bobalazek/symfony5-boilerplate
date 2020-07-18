<?php

namespace App\Command;

use App\Entity\Thread;
use App\Entity\ThreadUser;
use App\Entity\ThreadUserMessage;
use App\Manager\EmailManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronMessagingNewMessageEmail extends Command
{
    protected static $defaultName = 'app:cron:messaging:new-message-email';

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var EmailManager
     */
    private $emailManager;

    public function __construct(
        EntityManagerInterface $em,
        EmailManager $emailManager
    ) {
        $this->em = $em;
        $this->emailManager = $emailManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Send new message emails to other users in thread, if they were inactive for a while')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $threads = $this->em
            ->getRepository(Thread::class)
            ->createQueryBuilder('t')
            ->leftJoin('t.threadUsers', 'tu')
            ->orderBy('t.lastNewMessageEmailCheckedAt', 'ASC')
            ->addOrderBy('t.id', 'ASC')
            ->setMaxResults(32)
            ->getQuery()
            ->getResult()
        ;

        $finalThreadUsers = []; // List of thread users to whom the email will be sent
        foreach ($threads as $thread) {
            $threadUsers = $thread->getThreadUsers();
            $newestThreadUserMessage = $this->em
                ->getRepository(ThreadUserMessage::class)
                ->createQueryBuilder('tum')
                ->where('tum.threadUser IN (:threadUsers)')
                ->orderBy('tum.createdAt', 'DESC')
                ->setMaxResults(1)
                ->setParameter('threadUsers', $threadUsers)
                ->getQuery()
                ->getOneOrNullResult()
            ;

            $newestMessageCreatedAt = $newestThreadUserMessage->getCreatedAt();

            foreach ($threadUsers as $threadUser) {
                // Ignore the threadUser who is the owner of the newestThreadUserMessage
                if ($newestThreadUserMessage->getThreadUser() === $threadUser) {
                    continue;
                }

                $now = new \DateTime();
                $lastSeenAt = $threadUser->getLastSeenAt();
                $lastNewMessageEmailSentAt = $threadUser->getLastNewMessageEmailSentAt();
                $hasBeenSeenMoreThanAnHourAgo = false;
                $hasAlreadyBeenSent = true;

                if ($lastSeenAt) {
                    $lastHour = (clone $now)->sub(new DateInterval('PT1H'));
                    $hasBeenSeenMoreThanAnHourAgo = $lastSeenAt < $lastHour;
                    $hasAlreadyBeenSent = $lastSeenAt < $lastNewMessageEmailSentAt;
                }

                if (
                    !$hasAlreadyBeenSent &&
                    (
                        !$lastSeenAt ||
                        $hasBeenSeenMoreThanAnHourAgo
                    )
                ) {
                    $finalThreadUsers[] = $threadUser;
                }
            }

            foreach ($finalThreadUsers as $finalThreadUser) {
                $finalThreadUser->setLastNewMessageEmailSentAt(new \DateTime());
                $this->em->persist($finalThreadUser);

                $this->emailManager->sendMessagingNewMessage(
                    $finalThreadUser,
                    $newestThreadUserMessage
                );

                $output->writeln('Sending email to user ' . $finalThreadUser->getUser()->getUsername());
            }

            $thread->setLastNewMessageEmailCheckedAt(new \DateTime());
            $this->em->persist($thread);

            $this->em->flush();
        }

        $output->writeln('<info>New message emails successfully sent!</info>');

        return Command::SUCCESS;
    }
}
