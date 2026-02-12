<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to manually create the license table
 */
final class Version20260212112107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create License table';
    }

    public function up(Schema $schema): void
    {
        // Create license table if it doesn't exist
        $this->addSql('CREATE TABLE IF NOT EXISTS license (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', assigned_to_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', license_code VARCHAR(50) NOT NULL, is_used TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_LICENSE_CODE (license_code), UNIQUE INDEX UNIQ_5768F4198CAA4D24 (assigned_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Add foreign key constraint
        $this->addSql('ALTER TABLE license ADD CONSTRAINT FK_5768F4198CAA4D24 FOREIGN KEY (assigned_to_id) REFERENCES `user` (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE license');
    }
}
