<?php

namespace App\Repository;

use App\Entity\TextAnalyses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TextAnalyses>
 *
 * @method TextAnalyses|null find($id, $lockMode = null, $lockVersion = null)
 * @method TextAnalyses|null findOneBy(array $criteria, array $orderBy = null)
 * @method TextAnalyses[]    findAll()
 * @method TextAnalyses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TextAnalysesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TextAnalyses::class);
    }

    public function findByUserId(int $userId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return TextAnalyses[] Returns an array of TextAnalyses objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TextAnalyses
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
