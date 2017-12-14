<?php

/*
 * This file is part of the ForciRememberMeBundle package.
 *
 * (c) Martin Kirilov <wucdbm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Forci\Bundle\RememberMeBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Forci\Bundle\RememberMeBundle\Entity\RememberMeToken;
use Forci\Bundle\RememberMeBundle\Entity\Session;
use Forci\Bundle\RememberMeBundle\Filter\RememberMeTokenFilter;
use Wucdbm\Bundle\QuickUIBundle\Repository\QuickUIRepositoryTrait;

class RememberMeTokenRepository extends EntityRepository {

    use QuickUIRepositoryTrait;

    /**
     * @param string $series
     *
     * @return RememberMeToken
     */
    public function findOneBySeries(string $series) {
        $builder = $this->getQueryBuilder()
            ->andWhere('t.series = :series')
            ->setParameter('series', $series);
        $query = $builder->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param int $id
     *
     * @return RememberMeToken
     */
    public function findOneById(int $id) {
        $builder = $this->getQueryBuilder()
            ->andWhere('t.id = :id')
            ->setParameter('id', $id);
        $query = $builder->getQuery();

        return $query->getOneOrNullResult();
    }

    public function filter(RememberMeTokenFilter $filter) {
        $builder = $this->getQueryBuilder();

        if ($userId = $filter->getUserId()) {
            $builder->andWhere('t.userId = :userId')
                ->setParameter('userId', $userId);
        }

        if ($area = $filter->getArea()) {
            $builder->andWhere('t.area LIKE :area')
                ->setParameter('area', '%'.$area.'%');
        }

        if ($username = $filter->getUsername()) {
            $builder->andWhere('t.username LIKE :username')
                ->setParameter('username', '%'.$username.'%');
        }

        $dateMin = $filter->getDateMin();
        $dateMax = $filter->getDateMax();
        if ($dateMin && $dateMax) {
            $builder->andWhere('DATE(t.dateCreated) >= :dateCreatedMin')
                ->setParameter('dateCreatedMin', $dateMin->format('Y-m-d'))
                ->andWhere('DATE(t.dateCreated) <= :dateCreatedMax')
                ->setParameter('dateCreatedMax', $dateMax->format('Y-m-d'));
        }

        $builder->orderBy('t.dateCreated', 'DESC');

        return $this->returnFilteredEntities($builder, $filter, 't.series');
    }

    public function updateToken(string $series, string $tokenValue, \DateTime $lastUsed): int {
        $builder = $this->getQueryBuilder()
            ->update($this->getEntityName(), 't')
            ->set('t.value', ':value')
            ->setParameter('value', $tokenValue)
            ->set('t.lastUsed', ':lastUsed')
            ->setParameter('lastUsed', $lastUsed->format('Y-m-d H:i:s'))
            ->where('t.series = :series')
            ->setParameter('series', $series);

        $query = $builder->getQuery();

        return $query->execute();
    }

    protected function getQueryBuilder(): QueryBuilder {
        return $this->createQueryBuilder('t')
            ->addSelect('s')->leftJoin('t.sessions', 's');
    }

    public function save(RememberMeToken $token) {
        $em = $this->getEntityManager();

        /** @var Session $session */
        foreach ($token->getSessions() as $session) {
            $em->persist($session);
        }

        $em->persist($token);
        $em->flush();
    }

    public function remove(RememberMeToken $token) {
        $em = $this->getEntityManager();
        foreach ($token->getSessions() as $session) {
            $em->remove($session);
        }
        $em->remove($token);
        $em->flush($token);
    }
}
