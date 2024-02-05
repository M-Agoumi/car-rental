<?php

namespace App\Repository;

use App\Entity\Car;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 *
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findExistingReservation(Car $car, \DateTimeInterface $startDate, \DateTimeInterface $endDate, ?Reservation $excludeReservation = null)
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.car = :car')
            ->andWhere('r.startDate <= :endDate')
            ->andWhere('r.endDate >= :startDate')
            ->setParameter('car', $car)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if ($excludeReservation) {
            $qb->andWhere('r.id != :id')
                ->setParameter('id', $excludeReservation->getId());
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     *  find the first reservation that is not owned by the authenticate user.
     *
     * @return float|int|mixed|string|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findFirstNotOwned(User $user): mixed
    {
        $db = $this->createQueryBuilder('r');

        return $db->where($db->expr()->neq('r.user', ':user'))
            ->setParameter('user', $user)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
