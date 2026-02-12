<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212112632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game (id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, image_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_232B318C5E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE match_game (id BINARY(16) NOT NULL, round INT NOT NULL, status VARCHAR(255) NOT NULL, scheduled_at DATETIME DEFAULT NULL, started_at DATETIME DEFAULT NULL, completed_at DATETIME DEFAULT NULL, score JSON DEFAULT NULL, tournoi_id BINARY(16) NOT NULL, team1_id BINARY(16) DEFAULT NULL, team2_id BINARY(16) DEFAULT NULL, winner_id BINARY(16) DEFAULT NULL, INDEX IDX_424480FEF607770A (tournoi_id), INDEX IDX_424480FEE72BCFA4 (team1_id), INDEX IDX_424480FEF59E604A (team2_id), INDEX IDX_424480FE5DFCD4B8 (winner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, product_type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, price_points INT NOT NULL, available TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reclamation (id BINARY(16) NOT NULL, subject VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, category VARCHAR(255) NOT NULL, priority VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, author_id BINARY(16) NOT NULL, INDEX IDX_CE606404F675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reclamation_response (id BINARY(16) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, is_admin_response TINYINT NOT NULL, reclamation_id BINARY(16) NOT NULL, author_id BINARY(16) NOT NULL, INDEX IDX_B9A282F72D6BA2D9 (reclamation_id), INDEX IDX_B9A282F7F675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE skin (id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, price INT NOT NULL, rarity VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, game_id BINARY(16) NOT NULL, INDEX IDX_279681EE48FD905 (game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tournoi (id BINARY(16) NOT NULL, nom VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, nb_equipes_max INT NOT NULL, prize_pool INT NOT NULL, status VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, game_id BINARY(16) DEFAULT NULL, winner_id BINARY(16) DEFAULT NULL, referee_id BINARY(16) DEFAULT NULL, INDEX IDX_18AFD9DFE48FD905 (game_id), INDEX IDX_18AFD9DF5DFCD4B8 (winner_id), INDEX IDX_18AFD9DF4A087CA2 (referee_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tournoi_team (tournoi_id BINARY(16) NOT NULL, team_id BINARY(16) NOT NULL, INDEX IDX_99034A9BF607770A (tournoi_id), INDEX IDX_99034A9B296CD8AE (team_id), PRIMARY KEY (tournoi_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE match_game ADD CONSTRAINT FK_424480FEF607770A FOREIGN KEY (tournoi_id) REFERENCES tournoi (id)');
        $this->addSql('ALTER TABLE match_game ADD CONSTRAINT FK_424480FEE72BCFA4 FOREIGN KEY (team1_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE match_game ADD CONSTRAINT FK_424480FEF59E604A FOREIGN KEY (team2_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE match_game ADD CONSTRAINT FK_424480FE5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404F675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE reclamation_response ADD CONSTRAINT FK_B9A282F72D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamation (id)');
        $this->addSql('ALTER TABLE reclamation_response ADD CONSTRAINT FK_B9A282F7F675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE skin ADD CONSTRAINT FK_279681EE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE tournoi ADD CONSTRAINT FK_18AFD9DFE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE tournoi ADD CONSTRAINT FK_18AFD9DF5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE tournoi ADD CONSTRAINT FK_18AFD9DF4A087CA2 FOREIGN KEY (referee_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE tournoi_team ADD CONSTRAINT FK_99034A9BF607770A FOREIGN KEY (tournoi_id) REFERENCES tournoi (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournoi_team ADD CONSTRAINT FK_99034A9B296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE auth_token DROP FOREIGN KEY `FK_AUTH_TOKEN_USER`');
        $this->addSql('ALTER TABLE license DROP FOREIGN KEY `FK_5768F4198CAA4D24`');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY `FK_6117D13B6C755722`');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY `FK_6117D13B8A86BD8`');
        $this->addSql('DROP TABLE auth_token');
        $this->addSql('DROP TABLE license');
        $this->addSql('DROP TABLE purchase');
        $this->addSql('ALTER TABLE merch ADD stock INT NOT NULL, ADD type VARCHAR(50) NOT NULL, ADD game_id BINARY(16) DEFAULT NULL, DROP game, DROP rarity');
        $this->addSql('ALTER TABLE merch ADD CONSTRAINT FK_F1B42EE0E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('CREATE INDEX IDX_F1B42EE0E48FD905 ON merch (game_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE auth_token (id BINARY(16) NOT NULL, user_id BINARY(16) NOT NULL, value VARCHAR(64) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_9315F04E1D775834 (value), UNIQUE INDEX UNIQ_9315F04EA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE license (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', assigned_to_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', license_code VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, is_used TINYINT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_LICENSE_CODE (license_code), UNIQUE INDEX UNIQ_5768F4198CAA4D24 (assigned_to_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE purchase (id BINARY(16) NOT NULL, quantity INT NOT NULL, total_price INT NOT NULL, created_at DATETIME NOT NULL, buyer_id BINARY(16) NOT NULL, merch_id BINARY(16) NOT NULL, INDEX IDX_6117D13B6C755722 (buyer_id), INDEX IDX_6117D13B8A86BD8 (merch_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE auth_token ADD CONSTRAINT `FK_AUTH_TOKEN_USER` FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE license ADD CONSTRAINT `FK_5768F4198CAA4D24` FOREIGN KEY (assigned_to_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT `FK_6117D13B6C755722` FOREIGN KEY (buyer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT `FK_6117D13B8A86BD8` FOREIGN KEY (merch_id) REFERENCES merch (id)');
        $this->addSql('ALTER TABLE match_game DROP FOREIGN KEY FK_424480FEF607770A');
        $this->addSql('ALTER TABLE match_game DROP FOREIGN KEY FK_424480FEE72BCFA4');
        $this->addSql('ALTER TABLE match_game DROP FOREIGN KEY FK_424480FEF59E604A');
        $this->addSql('ALTER TABLE match_game DROP FOREIGN KEY FK_424480FE5DFCD4B8');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404F675F31B');
        $this->addSql('ALTER TABLE reclamation_response DROP FOREIGN KEY FK_B9A282F72D6BA2D9');
        $this->addSql('ALTER TABLE reclamation_response DROP FOREIGN KEY FK_B9A282F7F675F31B');
        $this->addSql('ALTER TABLE skin DROP FOREIGN KEY FK_279681EE48FD905');
        $this->addSql('ALTER TABLE tournoi DROP FOREIGN KEY FK_18AFD9DFE48FD905');
        $this->addSql('ALTER TABLE tournoi DROP FOREIGN KEY FK_18AFD9DF5DFCD4B8');
        $this->addSql('ALTER TABLE tournoi DROP FOREIGN KEY FK_18AFD9DF4A087CA2');
        $this->addSql('ALTER TABLE tournoi_team DROP FOREIGN KEY FK_99034A9BF607770A');
        $this->addSql('ALTER TABLE tournoi_team DROP FOREIGN KEY FK_99034A9B296CD8AE');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE match_game');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE reclamation_response');
        $this->addSql('DROP TABLE skin');
        $this->addSql('DROP TABLE tournoi');
        $this->addSql('DROP TABLE tournoi_team');
        $this->addSql('ALTER TABLE merch DROP FOREIGN KEY FK_F1B42EE0E48FD905');
        $this->addSql('DROP INDEX IDX_F1B42EE0E48FD905 ON merch');
        $this->addSql('ALTER TABLE merch ADD rarity VARCHAR(50) NOT NULL, DROP stock, DROP game_id, CHANGE type game VARCHAR(50) NOT NULL');
    }
}
