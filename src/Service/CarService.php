<?php

namespace App\Service;

use App\Entity\Car;
use App\Repository\CarRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class CarService
{
    public function __construct(
        private EntityManagerInterface $manager,
        private ValidatorInterface     $validator,
        private CarRepository          $carRepository
    )
    {
    }

    public function getCars(int $page, int $elementsByPage): array
    {
        return $this->carRepository->findBy(['isPublished' => true], [], $elementsByPage, ($page - 1) * $elementsByPage);
    }

    public function getCar(int $id): ?Car
    {
        $car = $this->carRepository->find($id);
        if ($car && $car->getIsPublished()) {
            return $car;
        }

        return NULL;
    }

    public function createCar(string $name, ?int $seats, string $description, bool $isPublished): array
    {
        $car = new Car();

        $car->setName($name);
        $car->setSeats($seats);
        $car->setDescription($description);
        $car->setIsPublished($isPublished);
        $car->setCreatedAt(new DateTimeImmutable('now'));

        return $this->validate($car);
    }

    public function updateCar(Car $car, ?string $name, ?int $seats, ?string $description, ?bool $isPublished): array
    {
        $car->setName($name ?? $car->getName());
        $car->setDescription($description ?? $car->getDescription());
        $car->setSeats($seats ?? $car->getSeats());
        $car->setIsPublished($isPublished ?? $car->getIsPublished());

        return $this->validate($car);
    }

    /**
     * @param Car $car
     * @return array
     */
    private function validate(Car $car): array
    {
        $errors = $this->validator->validate($car);
        if (count($errors) > 0) {
            $errorData = [];
            foreach ($errors as $error) {
                $errorData[] = [$error->getPropertyPath() => $error->getMessage()];
            }

            return $errorData;
        }
        $this->manager->persist($car);
        $this->manager->flush();

        return [];
    }

    public function deleteCar(Car $car): void
    {
        $this->manager->remove($car);
        $this->manager->flush();
    }
}
