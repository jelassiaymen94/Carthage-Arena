<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260226015913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // user_skin table is preserved and already has the correct status default.
        // This migration is intentionally a no-op to avoid altering existing data.
    }

    public function down(Schema $schema): void
    {
        // no-op
    }
}
