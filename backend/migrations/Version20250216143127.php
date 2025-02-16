<?php

declare(strict_types=1);

namespace migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250216143127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SQLitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SQLitePlatform'."
        );

        $this->connection->executeQuery('DROP TABLE IF EXISTS search');
        $this->connection->executeQuery('CREATE VIRTUAL TABLE IF NOT EXISTS search USING fts5(id, title, body, columnsize=0, detail=column)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SQLitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SQLitePlatform'."
        );

        $this->connection->executeQuery('DROP TABLE search');
        $this->connection->executeQuery('CREATE VIRTUAL TABLE IF NOT EXISTS search USING fts5(id, title, body)');
    }
}
