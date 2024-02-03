<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class UserService
{
    public function __construct(
        private EntityManagerInterface      $manager,
        private ValidatorInterface          $validator,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function signUpUser(\stdClass $userData): User | array
    {
        $user = new User();

        $user->setUsername($userData->username ?? '');
        $user->setRawPassword($userData->password ?? '');
        $user->setCreatedAt(new \DateTimeImmutable('now'));

        $errors = $this->validator->validate($user);
        if (count($errors)) {
            $errorData = [];
            foreach ($errors as $error) {
                $path = $error->getPropertyPath() == 'rawPassword' ? 'password' : $error->getPropertyPath();
                $errorData[] = [$path => $error->getMessage()];
            }

            return $errorData;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getRawPassword()));

        // Persist user to the database
        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }
}
