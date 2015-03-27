<?php namespace Wms\Admin\DataGrid\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;

class QueryBuilderService {

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var String
     */
    private $entity;

    public function __construct(EntityManager $entityManager, $entityNamespace) {
        $this->setQueryBuilder($entityManager->getRepository($entityNamespace)->createQueryBuilder('main'));
        $this->setEntity($entityNamespace);
    }
    public function setPage($pageNumber, $itemsPerPage = 30) {
        $offset = ($pageNumber == 0) ? 0 : ($pageNumber - 1) * $itemsPerPage;
        $this->getQueryBuilder()->setMaxResults($itemsPerPage);
        $this->getQueryBuilder()->setFirstResult($offset);
    }

    public function getResult() {
        $result = $this->getQueryBuilder()->getQuery()->execute();
        return $result;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @return String
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param String $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }


}