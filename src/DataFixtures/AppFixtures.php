<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }


    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail( 'userAngel@local.host');
        $user->setPassword($this->passwordHasher->hashPassword($user,"user"));

        $manager->persist($user);

        for ($i=0; $i < 5; $i++ ) {
            $user = new User();
            $user->setEmail($i . 'user@local.host');
            $user->setPassword($this->passwordHasher->hashPassword($user,"user"));

            $manager->persist($user);
        }

        $admin = new User();
        $admin->setEmail('admin@local.host');
        $admin->setPassword($this->passwordHasher->hashPassword($admin,"admin"));
        $admin->setRoles(["ROLE_ADMIN"]);


        $manager->persist($admin);

        $manager->flush();
    }
}
