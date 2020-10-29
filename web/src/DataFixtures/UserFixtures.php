<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserTfaMethod;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserFixtures.
 */
class UserFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var array
     */
    private $users;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;

        $users = [];

        $finder = new Finder();
        $finder->files()->name('*.php')->in(__DIR__ . '/data/users');
        foreach ($finder as $file) {
            $data = include $file;
            foreach ($data as $entry) {
                $users[] = $entry;
            }
        }

        $this->users = $users;
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->users as $userData) {
            $user = new User();
            $user
                ->setName($userData['name'])
                ->setFirstName($userData['first_name'])
                ->setLastName($userData['last_name'])
                ->setUsername($userData['username'])
                ->setEmail($userData['email'])
                ->setRoles($userData['roles'])
                ->setTfaEnabled(isset($userData['tfa_enabled']) && $userData['tfa_enabled'])
                ->setEmailConfirmCode(md5(random_bytes(32)))
                ->setEmailConfirmedAt(new \DateTime())
            ;
            $password = $this->passwordEncoder->encodePassword(
                $user,
                $userData['password']
            );
            $user->setPassword($password);

            $manager->persist($user);

            if (
                isset($userData['tfa_methods']) &&
                is_array($userData['tfa_methods'])
            ) {
                foreach ($userData['tfa_methods'] as $tfaMethod) {
                    $userTfaMethod = new UserTfaMethod();
                    $userTfaMethod
                        ->setEnabled(true)
                        ->setMethod($tfaMethod)
                    ;

                    $user->addUserTfaMethod($userTfaMethod);
                }
            }
        }

        $manager->flush();
    }
}
