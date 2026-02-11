<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211183750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE auth_token (id BINARY(16) NOT NULL, value VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, user_id BINARY(16) NOT NULL, UNIQUE INDEX UNIQ_9315F04E1D775834 (value), UNIQUE INDEX UNIQ_9315F04EA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE merch (stock INT NOT NULL, size VARCHAR(50) NOT NULL, id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, price_points INT NOT NULL, available TINYINT NOT NULL, product_type VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE skin (rarity VARCHAR(100) NOT NULL, image VARCHAR(255) NOT NULL, id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE auth_token ADD CONSTRAINT FK_9315F04EA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE merch ADD CONSTRAINT FK_F1B42EE0BF396750 FOREIGN KEY (id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE skin ADD CONSTRAINT FK_279681EBF396750 FOREIGN KEY (id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournoi DROP FOREIGN KEY `FK_18AFD9DF4A087CA2`');
        $this->addSql('ALTER TABLE tournoi DROP FOREIGN KEY `FK_18AFD9DF5DFCD4B8`');
        $this->addSql('ALTER TABLE tournoi_team DROP FOREIGN KEY `FK_99034A9B296CD8AE`');
        $this->addSql('ALTER TABLE tournoi_team DROP FOREIGN KEY `FK_99034A9BF607770A`');
        $this->addSql('DROP TABLE tournoi');
        $this->addSql('DROP TABLE tournoi_team');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tournoi (id BINARY(16) NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date_debut DATE NOT NULL, date_fin DATE NOT NULL, nb_equipes_max INT NOT NULL, prize_pool INT NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, winner_id BINARY(16) DEFAULT NULL, referee_id BINARY(16) DEFAULT NULL, INDEX IDX_18AFD9DF5DFCD4B8 (winner_id), INDEX IDX_18AFD9DF4A087CA2 (referee_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE tournoi_team (tournoi_id BINARY(16) NOT NULL, team_id BINARY(16) NOT NULL, INDEX IDX_99034A9BF607770A (tournoi_id), INDEX IDX_99034A9B296CD8AE (team_id), PRIMARY KEY (tournoi_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE tournoi ADD CONSTRAINT `FK_18AFD9DF4A087CA2` FOREIGN KEY (referee_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tournoi ADD CONSTRAINT `FK_18AFD9DF5DFCD4B8` FOREIGN KEY (winner_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE tournoi_team ADD CONSTRAINT `FK_99034A9B296CD8AE` FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournoi_team ADD CONSTRAINT `FK_99034A9BF607770A` FOREIGN KEY (tournoi_id) REFERENCES tournoi (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE auth_token DROP FOREIGN KEY FK_9315F04EA76ED395');
        $this->addSql('ALTER TABLE merch DROP FOREIGN KEY FK_F1B42EE0BF396750');
        $this->addSql('ALTER TABLE skin DROP FOREIGN KEY FK_279681EBF396750');
        $this->addSql('DROP TABLE auth_token');
        $this->addSql('DROP TABLE merch');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE skin');
    }
}
