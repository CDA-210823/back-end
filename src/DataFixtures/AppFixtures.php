<?php

namespace App\DataFixtures;

use App\Entity\Command;
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
        $user->setEmail('user@local.host');
        $user->setPassword($this->passwordHasher->hashPassword($user,"user"));

        $admin = new User();
        $admin->setEmail('admin@local.host');
        $admin->setPassword($this->passwordHasher->hashPassword($admin,"admin"));
        $admin->setRoles(["ROLE_ADMIN"]);


        $manager->persist($user);
        $manager->persist($admin);

        $command = new Command();
        $command->setNumber(1);
        $command->setDate(new \DateTime('now'));
        $command->setStatus('En cours');
        $command->setTotalPrice(100.0);

        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'user@local.host']);
        $command->setUser($user);

        $manager->persist($command);
        $manager->flush();
    }
}
