<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225121641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_verified column to user table; existing users are marked as verified to avoid locking them out.';
    }

    public function up(Schema $schema): void
    {
        // Add column with default 0, then set all existing users to verified (1),
        // so pre-existing accounts are not locked out.
        $this->addSql('ALTER TABLE `user` ADD is_verified TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('UPDATE `user` SET is_verified = 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP is_verified');
    }
}
