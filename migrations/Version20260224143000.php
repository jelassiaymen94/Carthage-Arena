<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260224143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add type, stock, apiProvider, deliveryMethod to skin table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE skin ADD type VARCHAR(255) NOT NULL DEFAULT \'digital\' COMMENT \'(DC2Type:SkinType)\'');
        $this->addSql('ALTER TABLE skin ADD stock INT DEFAULT NULL');
        $this->addSql('ALTER TABLE skin ADD api_provider VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE skin ADD delivery_method VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE skin DROP COLUMN type');
        $this->addSql('ALTER TABLE skin DROP COLUMN stock');
        $this->addSql('ALTER TABLE skin DROP COLUMN api_provider');
        $this->addSql('ALTER TABLE skin DROP COLUMN delivery_method');
    }
}