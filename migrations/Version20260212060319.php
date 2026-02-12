<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212060319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tournoi (id BINARY(16) NOT NULL, nom VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, nb_equipes_max INT NOT NULL, prize_pool INT NOT NULL, status VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, team_ids JSON NOT NULL, winner_id BINARY(16) DEFAULT NULL, referee_id BINARY(16) DEFAULT NULL, INDEX IDX_18AFD9DF5DFCD4B8 (winner_id), INDEX IDX_18AFD9DF4A087CA2 (referee_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE tournoi ADD CONSTRAINT FK_18AFD9DF5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE tournoi ADD CONSTRAINT FK_18AFD9DF4A087CA2 FOREIGN KEY (referee_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tournoi DROP FOREIGN KEY FK_18AFD9DF5DFCD4B8');
        $this->addSql('ALTER TABLE tournoi DROP FOREIGN KEY FK_18AFD9DF4A087CA2');
        $this->addSql('DROP TABLE tournoi');
    }
}
