<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212063434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tournoi_team (tournoi_id BINARY(16) NOT NULL, team_id BINARY(16) NOT NULL, INDEX IDX_99034A9BF607770A (tournoi_id), INDEX IDX_99034A9B296CD8AE (team_id), PRIMARY KEY (tournoi_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE tournoi_team ADD CONSTRAINT FK_99034A9BF607770A FOREIGN KEY (tournoi_id) REFERENCES tournoi (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournoi_team ADD CONSTRAINT FK_99034A9B296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournoi DROP team_ids, CHANGE game_id game_id BINARY(16) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tournoi_team DROP FOREIGN KEY FK_99034A9BF607770A');
        $this->addSql('ALTER TABLE tournoi_team DROP FOREIGN KEY FK_99034A9B296CD8AE');
        $this->addSql('DROP TABLE tournoi_team');
        $this->addSql('ALTER TABLE tournoi ADD team_ids JSON NOT NULL, CHANGE game_id game_id BINARY(16) NOT NULL');
    }
}
