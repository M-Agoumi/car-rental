<?php

namespace App\DataFixtures;

use App\Entity\Car;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly string $env, private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // check if env is a test environment
        if ('test' !== $this->env) {
            $this->printToConsole('This fixture is only for test environment', 'error');
        }

        // create test users
        $this->printToConsole('Creating test users');
        $this->createTestUsers($manager);

        // create test cars
        $this->printToConsole('Creating test cars');
        $this->createTestCars($manager);
    }

    private function printToConsole(string $message, string $status = 'info'): void
    {
        $output = new ConsoleOutput();
        $output->writeln(sprintf('<%s>%s</%s>', $status, $message, $status));
    }

    private function createTestUsers(ObjectManager $manager): void
    {
        // create a user
        $user = new User();
        $user->setUsername('testuser1');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'admin'));
        $user->setCreatedAt(new \DateTimeImmutable('now'));
        $manager->persist($user);
        // create admin user
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setCreatedAt(new \DateTimeImmutable('now'));
        $manager->persist($admin);
        $manager->flush();
    }

    private function createTestCars(ObjectManager $manager): void
    {
        // create cars
        $car1 = new Car();
        $car1->setName('Toyota Corolla');
        $car1->setSeats(5);
        $car1->setCreatedAt(new \DateTimeImmutable('now'));
        $car1->setIsPublished(true);
        $manager->persist($car1);
        $car2 = new Car();
        $car2->setName('Honda Civic');
        $car2->setSeats(4);
        $car2->setCreatedAt(new \DateTimeImmutable('now'));
        $car2->setIsPublished(false);
        $manager->persist($car2);
        $manager->flush();
    }
}
