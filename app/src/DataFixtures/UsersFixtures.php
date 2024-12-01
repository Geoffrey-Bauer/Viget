<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un utilisateur admin
        $adminUser = new User();
        $adminUser->setEmail('admin@viget.com');
        $adminUser->setFirstname('Admin');
        $adminUser->setLastname('User');
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'password123')); // Remplacez par un mot de passe sécurisé
        $adminUser->setRoles(['ROLE_ADMIN']); // Attribution du rôle admin

        // Persist l'utilisateur dans la base de données
        $manager->persist($adminUser);

        // Flush les données
        $manager->flush();
    }
}