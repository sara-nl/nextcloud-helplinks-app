<?php
namespace OCA\HelpLinks\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class SubLinkMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'helplinks_sublinks', SubLink::class);
    }

    public function findBySection(int $sectionId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('section_id', $qb->createNamedParameter($sectionId, IQueryBuilder::PARAM_INT)))
            ->orderBy('sort_order', 'ASC');
        return $this->findEntities($qb);
    }

    public function deleteBySection(int $sectionId): void {
        $qb = $this->db->getQueryBuilder();
        $qb->delete($this->getTableName())
            ->where($qb->expr()->eq('section_id', $qb->createNamedParameter($sectionId, IQueryBuilder::PARAM_INT)));
        $qb->execute();
    }
}