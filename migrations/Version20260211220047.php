<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211220047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add license_id column to user table for referee support';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD license_id VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP license_id');
    }
}
