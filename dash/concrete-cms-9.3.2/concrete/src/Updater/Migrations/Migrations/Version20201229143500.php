<?php

namespace Concrete\Core\Updater\Migrations\Migrations;

use Concrete\Core\Updater\Migrations\AbstractMigration;
use Concrete\Core\Updater\Migrations\RepeatableMigrationInterface;
use Doctrine\DBAL\Schema\Schema;

final class Version20201229143500 extends AbstractMigration implements RepeatableMigrationInterface
{

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Updater\Migrations\AbstractMigration::upgradeSchema()
     */
    public function upgradeSchema(Schema $schema)
    {
        // add new multilingual tables.
        if (!$schema->hasTable('UserPrivateMessagesAttachments')) {
            $mpr = $schema->createTable('UserPrivateMessagesAttachments');
            $mpr->addColumn('msgID', 'integer', ['notnull' => true, 'unsigned' => true, 'default' => 0]);
            $mpr->addColumn('fID', 'integer', ['notnull' => true, 'unsigned' => true, 'default' => 0]);
            $mpr->setPrimaryKey(['msgID', 'fID']);
        }
    }

    public function upgradeDatabase()
    {
    }
}