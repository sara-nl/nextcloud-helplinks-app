<?php
namespace OCA\HelpLinks\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1000Date20240101000000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('helplinks_sections')) {
            $table = $schema->createTable('helplinks_sections');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('title', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('description', 'text', [
                'notnull' => false,
            ]);
            $table->addColumn('main_link_text', 'string', [
                'notnull' => false,
                'length' => 255,
            ]);
            $table->addColumn('main_link_url', 'text', [
                'notnull' => false,
            ]);
            $table->addColumn('sort_order', 'integer', [
                'notnull' => true,
                'default' => 0,
            ]);
            $table->addColumn('created_at', 'integer', [
                'notnull' => true,
            ]);
            $table->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('helplinks_sublinks')) {
            $table = $schema->createTable('helplinks_sublinks');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('section_id', 'integer', [
                'notnull' => true,
            ]);
            $table->addColumn('text', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('url', 'text', [
                'notnull' => true,
            ]);
            $table->addColumn('sort_order', 'integer', [
                'notnull' => true,
                'default' => 0,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['section_id'], 'helplinks_sublinks_section_id');
        }

        if (!$schema->hasTable('helplinks_settings')) {
            $table = $schema->createTable('helplinks_settings');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('key', 'string', [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('value', 'text', [
                'notnull' => false,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['key'], 'helplinks_settings_key');
        }

        return $schema;
    }
}