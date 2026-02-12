<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212102745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reclamation_response (id BINARY(16) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, is_admin_response TINYINT NOT NULL, reclamation_id BINARY(16) NOT NULL, author_id BINARY(16) NOT NULL, INDEX IDX_B9A282F72D6BA2D9 (reclamation_id), INDEX IDX_B9A282F7F675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE reclamation_response ADD CONSTRAINT FK_B9A282F72D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamation (id)');
        $this->addSql('ALTER TABLE reclamation_response ADD CONSTRAINT FK_B9A282F7F675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE answer_reclam DROP FOREIGN KEY `FK_73B8E2592D6BA2D9`');
        $this->addSql('ALTER TABLE answer_reclam DROP FOREIGN KEY `FK_73B8E259642B8210`');
        $this->addSql('DROP TABLE answer_reclam');
        $this->addSql('DROP TABLE product');
        $this->addSql('ALTER TABLE reclamation ADD category VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE skin DROP game');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer_reclam (id BINARY(16) NOT NULL, message LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, reclamation_id BINARY(16) NOT NULL, admin_id BINARY(16) NOT NULL, INDEX IDX_73B8E259642B8210 (admin_id), INDEX IDX_73B8E2592D6BA2D9 (reclamation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, product_type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, price_points INT NOT NULL, available TINYINT DEFAULT 1 NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE answer_reclam ADD CONSTRAINT `FK_73B8E2592D6BA2D9` FOREIGN KEY (reclamation_id) REFERENCES reclamation (id)');
        $this->addSql('ALTER TABLE answer_reclam ADD CONSTRAINT `FK_73B8E259642B8210` FOREIGN KEY (admin_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reclamation_response DROP FOREIGN KEY FK_B9A282F72D6BA2D9');
        $this->addSql('ALTER TABLE reclamation_response DROP FOREIGN KEY FK_B9A282F7F675F31B');
        $this->addSql('DROP TABLE reclamation_response');
        $this->addSql('ALTER TABLE reclamation DROP category');
        $this->addSql('ALTER TABLE skin ADD game VARCHAR(255) DEFAULT \'League of Legends\' NOT NULL');
    }
}
