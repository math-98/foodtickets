<?php

namespace App\Repository;

use App\Entity\Contract;
use App\Entity\ContractIncome;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContractIncome>
 *
 * @method ContractIncome|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContractIncome|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContractIncome[]    findAll()
 * @method ContractIncome[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContractIncomeRepository extends ServiceEntityRepository
{
    private ContractRepository $contractRepository;

    public function __construct(ManagerRegistry $registry, ContractRepository $contractRepository)
    {
        parent::__construct($registry, ContractIncome::class);

        $this->contractRepository = $contractRepository;
    }

    public function getContractIncomeByMonth(Contract $contract, \DateTime $month): ?ContractIncome
    {
        return $this->findOneBy([
            'contract' => $contract,
            'period' => $month->format('Y-m'),
        ]);
    }

    /**
     * @return ContractIncome[]
     */
    public function findUserIncomes(User $user): array
    {
        $contractIds = $this->contractRepository
            ->createQueryBuilder('c')
            ->select('c.id')
            ->where('c.user = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getArrayResult();

        return $this->createQueryBuilder('i')
            ->where('i.contract IN (:contractIds)')
            ->setParameter('contractIds', $contractIds)
            ->getQuery()
            ->getResult();
    }

    public function save(ContractIncome $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ContractIncome $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
