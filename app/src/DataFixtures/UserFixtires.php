<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtires extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
        
    }
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $user = new User();
        $user->setEmail('admin@admin.com');

        $hashed = $this->passwordHasher->hashPassword($user, 'admin');
        $user->setPassword($hashed);
        $user->setRoles(['ROLE_USER']);
        
        $manager->persist($user);

        $manager->flush();
    }
}
