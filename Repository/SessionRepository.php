<?php

namespace Forci\Bundle\RememberMeBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Forci\Bundle\RememberMeBundle\Entity\Session;

class SessionRepository extends EntityRepository {

    /**
     * @param int $tokenId
     * @param string $identifier
     * @return Session|null
     */
    public function findOneByTokenIdAndIdentifier(int $tokenId, string $identifier) {
        $builder = $this->createQueryBuilder('s')
            ->leftJoin('s.token', 't')
            ->andWhere('t.id = :tokenId')
            ->setParameter('tokenId', $tokenId)
            ->andWhere('s.identifier = :identifier')
            ->setParameter('identifier', $identifier);

        $query = $builder->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param int $id
     * @return Session|null
     */
    public function findOneById(int $id) {
        $builder = $this->createQueryBuilder('s')
            ->andWhere('s.id = :id')
            ->setParameter('id', $id);

        $query = $builder->getQuery();

        return $query->getOneOrNullResult();
    }

    public function remove(Session $session) {
        $em = $this->getEntityManager();
        $em->remove($session);
        $em->flush($session);
    }

    public function save(Session $session) {
        $em = $this->getEntityManager();
        $em->persist($session);
        $em->flush($session);
    }

}