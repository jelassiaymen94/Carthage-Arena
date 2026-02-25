<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260224144500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update existing skin records with valid enum value and fix column constraints';
    }

    public function up(Schema $schema): void
    {
        // Set all existing skins to digital if type is null or empty
        $this->addSql("UPDATE skin SET type = 'digital' WHERE type IS NULL OR type = ''");
        
        // Make type non-nullable with default
        $this->addSql('ALTER TABLE skin MODIFY type VARCHAR(255) NOT NULL DEFAULT \'digital\' COMMENT \'(DC2Type:SkinType)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE skin MODIFY type VARCHAR(255) DEFAULT \'digital\' COMMENT \'(DC2Type:SkinType)\'');
    }
}