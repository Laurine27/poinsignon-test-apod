<?php

namespace App\Repository;

use App\Entity\NasaPhoto;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NasaPhoto>
 *
 * @method NasaPhoto|null find($id, $lockMode = null, $lockVersion = null)
 * @method NasaPhoto|null findOneBy(array $criteria, array $orderBy = null)
 * @method NasaPhoto[]    findAll()
 * @method NasaPhoto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NasaPhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NasaPhoto::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findByDate(DateTime $date): ?NasaPhoto
    {
        $qb = $this->createQueryBuilder('n');
        $qb->andWhere($qb->expr()->eq('n.date', ':date'))
            ->setParameter('date', $date->format('Y-m-d'));

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findByPreviousDate(DateTime $date): ?NasaPhoto
    {
        $localDate = clone $date;

        $qb = $this->createQueryBuilder('n');
        $qb->andWhere($qb->expr()->eq('n.date', ':date'))
            ->setParameter('date', $localDate->modify('-1 day')->format('Y-m-d'));

        return $qb->getQuery()->getOneOrNullResult();
    }
}
