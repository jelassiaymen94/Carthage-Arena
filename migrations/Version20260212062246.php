<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212062246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer_reclam (id BINARY(16) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, reclamation_id BINARY(16) NOT NULL, admin_id BINARY(16) NOT NULL, INDEX IDX_73B8E2592D6BA2D9 (reclamation_id), INDEX IDX_73B8E259642B8210 (admin_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE game (id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, image_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_232B318C5E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE match_game (id BINARY(16) NOT NULL, round INT NOT NULL, status VARCHAR(255) NOT NULL, scheduled_at DATETIME DEFAULT NULL, started_at DATETIME DEFAULT NULL, completed_at DATETIME DEFAULT NULL, score JSON DEFAULT NULL, tournoi_id BINARY(16) NOT NULL, team1_id BINARY(16) NOT NULL, team2_id BINARY(16) NOT NULL, winner_id BINARY(16) DEFAULT NULL, INDEX IDX_424480FEF607770A (tournoi_id), INDEX IDX_424480FEE72BCFA4 (team1_id), INDEX IDX_424480FEF59E604A (team2_id), INDEX IDX_424480FE5DFCD4B8 (winner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reclamation (id BINARY(16) NOT NULL, subject VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, priority VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, author_id BINARY(16) NOT NULL, INDEX IDX_CE606404F675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE skin (id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, price INT NOT NULL, rarity VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, game_id BINARY(16) NOT NULL, INDEX IDX_279681EE48FD905 (game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE answer_reclam ADD CONSTRAINT FK_73B8E2592D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamation (id)');
        $this->addSql('ALTER TABLE answer_reclam ADD CONSTRAINT FK_73B8E259642B8210 FOREIGN KEY (admin_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE match_game ADD CONSTRAINT FK_424480FEF607770A FOREIGN KEY (tournoi_id) REFERENCES tournoi (id)');
        $this->addSql('ALTER TABLE match_game ADD CONSTRAINT FK_424480FEE72BCFA4 FOREIGN KEY (team1_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE match_game ADD CONSTRAINT FK_424480FEF59E604A FOREIGN KEY (team2_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE match_game ADD CONSTRAINT FK_424480FE5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404F675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE skin ADD CONSTRAINT FK_279681EE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE tournoi ADD game_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE tournoi ADD CONSTRAINT FK_18AFD9DFE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('CREATE INDEX IDX_18AFD9DFE48FD905 ON tournoi (game_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer_reclam DROP FOREIGN KEY FK_73B8E2592D6BA2D9');
        $this->addSql('ALTER TABLE answer_reclam DROP FOREIGN KEY FK_73B8E259642B8210');
        $this->addSql('ALTER TABLE match_game DROP FOREIGN KEY FK_424480FEF607770A');
        $this->addSql('ALTER TABLE match_game DROP FOREIGN KEY FK_424480FEE72BCFA4');
        $this->addSql('ALTER TABLE match_game DROP FOREIGN KEY FK_424480FEF59E604A');
        $this->addSql('ALTER TABLE match_game DROP FOREIGN KEY FK_424480FE5DFCD4B8');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404F675F31B');
        $this->addSql('ALTER TABLE skin DROP FOREIGN KEY FK_279681EE48FD905');
        $this->addSql('DROP TABLE answer_reclam');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE match_game');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE skin');
        $this->addSql('ALTER TABLE tournoi DROP FOREIGN KEY FK_18AFD9DFE48FD905');
        $this->addSql('DROP INDEX IDX_18AFD9DFE48FD905 ON tournoi');
        $this->addSql('ALTER TABLE tournoi DROP game_id');
    }
}
