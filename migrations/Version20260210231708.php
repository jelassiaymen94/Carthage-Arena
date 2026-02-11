<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210231708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        // 1. Drop all tables to ensure clean state
        $this->addSql('DROP TABLE IF EXISTS team_membership');
        $this->addSql('DROP TABLE IF EXISTS team');
        $this->addSql('DROP TABLE IF EXISTS profile');
        $this->addSql('DROP TABLE IF EXISTS `user`');

        // 2. Create User table
        $this->addSql('CREATE TABLE `user` (id BINARY(16) NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(50) NOT NULL, nickname VARCHAR(50) DEFAULT NULL, password VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, roles JSON NOT NULL, created_at DATETIME NOT NULL, balance INT NOT NULL, UNIQUE INDEX UNIQ_EMAIL (email), UNIQUE INDEX UNIQ_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        // 3. Create other tables
        $this->addSql('CREATE TABLE profile (id BINARY(16) NOT NULL, bio VARCHAR(500) DEFAULT NULL, avatar_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id BINARY(16) NOT NULL, UNIQUE INDEX UNIQ_8157AA0FA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE team (id BINARY(16) NOT NULL, name VARCHAR(100) NOT NULL, tag VARCHAR(5) NOT NULL, description VARCHAR(500) DEFAULT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, invite_code VARCHAR(10) NOT NULL, captain_id BINARY(16) NOT NULL, UNIQUE INDEX UNIQ_C4E0A61F5E237E06 (name), UNIQUE INDEX UNIQ_C4E0A61F389B783 (tag), UNIQUE INDEX UNIQ_C4E0A61F6F21F112 (invite_code), INDEX IDX_C4E0A61F3346729B (captain_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE team_membership (id BINARY(16) NOT NULL, role VARCHAR(255) NOT NULL, joined_at DATETIME NOT NULL, team_id BINARY(16) NOT NULL, player_id BINARY(16) NOT NULL, INDEX IDX_B826A040296CD8AE (team_id), INDEX IDX_B826A04099E6F5DF (player_id), UNIQUE INDEX UNIQ_TEAM_PLAYER (team_id, player_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        // 4. Add Foreign Keys
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F3346729B FOREIGN KEY (captain_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE team_membership ADD CONSTRAINT FK_B826A040296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE team_membership ADD CONSTRAINT FK_B826A04099E6F5DF FOREIGN KEY (player_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0FA76ED395');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F3346729B');
        $this->addSql('ALTER TABLE team_membership DROP FOREIGN KEY FK_B826A040296CD8AE');
        $this->addSql('ALTER TABLE team_membership DROP FOREIGN KEY FK_B826A04099E6F5DF');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE team_membership');
        $this->addSql('ALTER TABLE `user` ADD avatar VARCHAR(255) DEFAULT NULL, ADD google_id VARCHAR(255) DEFAULT NULL, ADD facebook_id VARCHAR(255) DEFAULT NULL, ADD instagram_id VARCHAR(255) DEFAULT NULL, ADD clerk_id VARCHAR(255) DEFAULT NULL, DROP nickname, DROP status, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE password password VARCHAR(255) DEFAULT NULL');
    }
}
