<?php
namespace OCA\HelpLinks\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class SectionMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'helplinks_sections', Section::class);
    }

    public function findAll(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->orderBy('sort_order', 'ASC');
        return $this->findEntities($qb);
    }

    public function find(int $id): Section {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
        return $this->findEntity($qb);
    }

    public function updateSortOrder(int $id, int $sortOrder): void {
        $qb = $this->db->getQueryBuilder();
        $qb->update($this->getTableName())
            ->set('sort_order', $qb->createNamedParameter($sortOrder, IQueryBuilder::PARAM_INT))
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
        $qb->execute();
    }
}