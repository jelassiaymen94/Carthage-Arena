<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304100819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auth_token CHANGE expires_at expires_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY `FK_6117D13BA76ED395`');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              purchase
            ADD
              CONSTRAINT FK_6117D13BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql('ALTER TABLE reclamation_response DROP FOREIGN KEY `FK_B9A282F72D6BA2D9`');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              reclamation_response
            ADD
              CONSTRAINT FK_B9A282F72D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamation (id) ON DELETE CASCADE
        SQL);
        $this->addSql('ALTER TABLE team_membership DROP FOREIGN KEY `FK_B826A040296CD8AE`');
        $this->addSql('ALTER TABLE team_membership DROP FOREIGN KEY `FK_B826A04099E6F5DF`');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              team_membership
            ADD
              CONSTRAINT FK_B826A040296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              team_membership
            ADD
              CONSTRAINT FK_B826A04099E6F5DF FOREIGN KEY (player_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auth_token CHANGE expires_at expires_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BA76ED395');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              purchase
            ADD
              CONSTRAINT `FK_6117D13BA76ED395` FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql('ALTER TABLE reclamation_response DROP FOREIGN KEY FK_B9A282F72D6BA2D9');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              reclamation_response
            ADD
              CONSTRAINT `FK_B9A282F72D6BA2D9` FOREIGN KEY (reclamation_id) REFERENCES reclamation (id)
        SQL);
        $this->addSql('ALTER TABLE team_membership DROP FOREIGN KEY FK_B826A040296CD8AE');
        $this->addSql('ALTER TABLE team_membership DROP FOREIGN KEY FK_B826A04099E6F5DF');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              team_membership
            ADD
              CONSTRAINT `FK_B826A040296CD8AE` FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              team_membership
            ADD
              CONSTRAINT `FK_B826A04099E6F5DF` FOREIGN KEY (player_id) REFERENCES user (id)
        SQL);
    }
}
