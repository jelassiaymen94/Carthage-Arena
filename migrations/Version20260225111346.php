<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225111346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE purchase (id BINARY(16) NOT NULL, quantity INT NOT NULL, total_price INT NOT NULL, purchase_date DATETIME NOT NULL, merch_id BINARY(16) NOT NULL, user_id BINARY(16) NOT NULL, INDEX IDX_6117D13B8A86BD8 (merch_id), INDEX IDX_6117D13BA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B8A86BD8 FOREIGN KEY (merch_id) REFERENCES merch (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_skin DROP FOREIGN KEY `FK_78F824D7A76ED395`');
        $this->addSql('ALTER TABLE user_skin DROP FOREIGN KEY `FK_78F824D7F404637F`');
        $this->addSql('DROP TABLE user_skin');
        $this->addSql('ALTER TABLE skin DROP type, DROP stock, DROP api_provider, DROP delivery_method');
        $this->addSql('ALTER TABLE user ADD discord_id VARCHAR(64) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64943349DE ON user (discord_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_skin (id BINARY(16) NOT NULL, purchased_at DATETIME NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'active\' NOT NULL COLLATE `utf8mb4_unicode_ci`, user_id BINARY(16) NOT NULL, skin_id BINARY(16) NOT NULL, INDEX IDX_78F824D7A76ED395 (user_id), INDEX IDX_78F824D7F404637F (skin_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_skin ADD CONSTRAINT `FK_78F824D7A76ED395` FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_skin ADD CONSTRAINT `FK_78F824D7F404637F` FOREIGN KEY (skin_id) REFERENCES skin (id)');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B8A86BD8');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BA76ED395');
        $this->addSql('DROP TABLE purchase');
        $this->addSql('ALTER TABLE skin ADD type VARCHAR(255) NOT NULL, ADD stock INT DEFAULT NULL, ADD api_provider VARCHAR(255) DEFAULT NULL, ADD delivery_method VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_8D93D64943349DE ON `user`');
        $this->addSql('ALTER TABLE `user` DROP discord_id');
    }
}
