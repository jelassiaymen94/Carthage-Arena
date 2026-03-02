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
        // Create purchase table (links users to merch purchases)
        $this->addSql('CREATE TABLE IF NOT EXISTS purchase (id BINARY(16) NOT NULL, quantity INT NOT NULL, total_price INT NOT NULL, purchase_date DATETIME NOT NULL, merch_id BINARY(16) NOT NULL, user_id BINARY(16) NOT NULL, INDEX IDX_6117D13B8A86BD8 (merch_id), INDEX IDX_6117D13BA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B8A86BD8 FOREIGN KEY (merch_id) REFERENCES merch (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        // Add discord_id to user (safe, additive only)
        $this->addSql('ALTER TABLE `user` ADD discord_id VARCHAR(64) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64943349DE ON `user` (discord_id)');
        // NOTE: user_skin table and skin columns (type, stock, api_provider, delivery_method) are preserved intentionally
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B8A86BD8');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BA76ED395');
        $this->addSql('DROP TABLE purchase');
        $this->addSql('DROP INDEX UNIQ_8D93D64943349DE ON `user`');
        $this->addSql('ALTER TABLE `user` DROP discord_id');
    }
}
