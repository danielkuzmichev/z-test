<?php

namespace App\DataFixtures;

use App\Service\Token\RedisTokenManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private RedisTokenManager $tokenManager;
    private Connection $connection;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        RedisTokenManager $tokenManager,
        Connection $connection,
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->tokenManager = $tokenManager;
        $this->connection = $connection;
    }

    public function load(ObjectManager $manager): void
    {
        $email = 'admin@example.com';
        $plainPassword = 'password';
        $roles = json_encode(['ROLE_ADMIN']);

        $user = new \App\Entity\User();
        $user->setEmail($email);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);

        $this->connection->executeStatement(
            'INSERT INTO "user" (id, email, roles, password) VALUES (?, ?, ?, ?)
             ON CONFLICT (id) DO UPDATE SET email = EXCLUDED.email, roles = EXCLUDED.roles, password = EXCLUDED.password',
            [1, $email, $roles, $hashedPassword]
        );

        $this->tokenManager->generateToken(1);
    }
}
