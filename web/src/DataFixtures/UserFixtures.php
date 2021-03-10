<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserTfaMethod;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserFixtures.
 */
class UserFixtures extends Fixture
{
    /**
     * @var array
     */
    private $entries;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;

        $this->entries = include __DIR__ . '/data/users.php';
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->entries as $entry) {
            $entity = new User();
            $entity
                ->setName($entry['name'])
                ->setFirstName($entry['first_name'])
                ->setLastName($entry['last_name'])
                ->setUsername($entry['username'])
                ->setEmail($entry['email'])
                ->setRoles($entry['roles'])
                ->setTfaEnabled(isset($entry['tfa_enabled']) && $entry['tfa_enabled'])
                ->setEmailConfirmCode(md5(random_bytes(32)))
                ->setEmailConfirmedAt(new \DateTime())
            ;
            $password = $this->passwordEncoder->encodePassword(
                $entity,
                $entry['password']
            );
            $entity->setPassword($password);

            $manager->persist($entity);

            if (
                isset($entry['tfa_methods']) &&
                is_array($entry['tfa_methods'])
            ) {
                foreach ($entry['tfa_methods'] as $tfaMethod) {
                    $userTfaMethod = new UserTfaMethod();
                    $userTfaMethod
                        ->setEnabled(true)
                        ->setMethod($tfaMethod)
                    ;

                    $entity->addUserTfaMethod($userTfaMethod);
                }
            }
        }

        $manager->flush();
    }
}
