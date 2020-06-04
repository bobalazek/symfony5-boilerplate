<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
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
                ->setUsername($userData['username'])
                ->setEmail($userData['email'])
                ->setRoles($userData['roles'])
                ->setEmailConfirmCode(md5(random_bytes(32)))
                ->setEmailConfirmedAt(new \DateTime())
            ;
            $password = $this->passwordEncoder->encodePassword(
                $user,
                $userData['password']
            );
            $user->setPassword($password);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
