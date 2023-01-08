<?php

namespace App\Repository;

use App\Entity\User;
use App\Security\Oauth\LaravelPassportResourceOwner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOrCreateFromLaravelPassport(LaravelPassportResourceOwner $resourceOwner): User
    {
        $em = $this->getEntityManager();

        $user = $this->createQueryBuilder('u')
            ->where('u.oauth_id = :oauth_id')
            ->setParameter('oauth_id', $resourceOwner->getId())
            ->getQuery()
            ->getOneOrNullResult();

        if (!$user) {
            $user = new User();
            $user->setOauthId($resourceOwner->getId());
            $em->persist($user);
        }

        $user->setEmail($resourceOwner->getEmail());
        $user->setName($resourceOwner->getName());
        $user->setAvatar($resourceOwner->getAvatar());
        $user->setAccess($resourceOwner->getAccess());
        $em->flush();

        return $user;
    }
}
