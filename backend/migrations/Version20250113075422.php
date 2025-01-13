<?php

declare(strict_types=1);

namespace migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250113075422 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

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

        $this->connection->executeQuery('PRAGMA journal_mode = WAL');
        $this->connection->executeQuery('PRAGMA synchronous = NORMAL');
        $this->connection->executeQuery('PRAGMA locking_mode = NORMAL');
    }

    public function down(Schema $schema): void
    {
    }
}
