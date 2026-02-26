<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260226015913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // skin columns (type, stock, api_provider, delivery_method) already exist in DB
        // Only update user_skin status default
        $this->addSql('ALTER TABLE user_skin CHANGE status status VARCHAR(255) DEFAULT \'active\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_skin CHANGE status status VARCHAR(255) NOT NULL');
    }
}
