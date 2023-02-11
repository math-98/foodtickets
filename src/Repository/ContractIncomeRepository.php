<?php

namespace App\Repository;

use App\Entity\Contract;
use App\Entity\ContractIncome;
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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContractIncome::class);
    }

    public function getContractIncomeByMonth(Contract $contract, \DateTime $month): ?ContractIncome
    {
        return $this->findOneBy([
            'contract' => $contract,
            'period' => $month->format('Y-m'),
        ]);
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
