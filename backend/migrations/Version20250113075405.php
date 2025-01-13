<?php

declare(strict_types=1);

namespace migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250113075405 extends AbstractMigration
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

        $this->addSql('CREATE TABLE post (id VARCHAR(255) NOT NULL COLLATE "BINARY", created_at DATE DEFAULT NULL, last_modified DATE NOT NULL, viewed_at DATETIME DEFAULT NULL, post_type VARCHAR(255) NOT NULL COLLATE "BINARY", title VARCHAR(255) DEFAULT NULL COLLATE "BINARY", "like" BOOLEAN NOT NULL, deleted_at DATE DEFAULT NULL, html CLOB DEFAULT NULL COLLATE "BINARY", PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D458B3022 ON post (post_type)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SQLitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SQLitePlatform'."
        );

        $this->addSql('DROP TABLE post');
    }
}
