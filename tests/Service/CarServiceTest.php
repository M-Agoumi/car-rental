<?php

namespace App\Tests\Service;

use App\Entity\Car;
use App\Repository\CarRepository;
use App\Service\CarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CarServiceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private CarRepository $carRepository;

    private CarService $carService;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = $this->getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->validator = $container->get(ValidatorInterface::class);
        $this->carRepository = $container->get(CarRepository::class);
        $this->carService = new CarService($this->entityManager, $this->validator, $this->carRepository);
    }

    public function testGetCars(): void
    {
        $cars = $this->carService->getCars(1,10);

        // Example assertion
        $this->assertIsArray($cars);
    }

    public function testGetCar(): void
    {
        $car = $this->carRepository->findOneBy(['isPublished' => true]);
        if (!$car)
            $this->markTestSkipped('No car found in the database.');
        $carFromService = $this->carService->getCar($car->getId());

        $this->assertEquals($car, $carFromService);
    }

    public function testCreateCar(): void
    {
        $errors = $this->carService->createCar('Test CAR', 4, 'A nice test car', false);
        $this->assertEmpty($errors);
    }

    public function testUpdateCar(): void
    {
        $car = $this->carRepository->findOneBy([]);

        $errors = $this->carService->updateCar($car, 'Test updated', 4,'A nice test car', false);

        // Example assertion
        $this->assertEmpty($errors);
    }

    public function testDeleteCar(): void
    {
        // Arrange
        $car = $this->createTestCar(); // Create a test car entity
        $id = $car->getId();
        // Act
        $this->carService->deleteCar($car);

        // Assert
        $deletedCar = $this->carRepository->find($id);
        $this->assertNull($deletedCar, 'The car should be deleted from the database.');
    }

    private function createTestCar(): Car
    {
        // Create a test car entity for testing
        $car = new Car();
        $car->setName('Test Car');
        $car->setSeats(4);
        $car->setIsPublished(true);
        $car->setCreatedAt(new \DateTimeImmutable('now'));

        $this->entityManager->persist($car);
        $this->entityManager->flush();

        return $car;
    }
}
