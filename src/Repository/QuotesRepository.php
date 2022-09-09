<?php

namespace App\Repository;

use App\Entity\Quotes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quotes>
 *
 * @method Quotes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quotes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quotes[]    findAll()
 * @method Quotes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuotesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quotes::class);
    }

    public function add(Quotes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Quotes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // Recherche d'une citation contenant la avant ou après la valeur recherchée
   /**
    * @return Quotes[] Returns an array of Quotes objects
    */
   public function findBySearchField($value): array
   {
       return $this->createQueryBuilder('q')
           ->Where("q.content LIKE :val ")
           ->orWhere("q.meta LIKE :val")
           ->setParameter('val', '%'.$value.'%')
           ->orderBy('q.id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
   }

//    public function findOneBySomeField($value): ?Quotes
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
