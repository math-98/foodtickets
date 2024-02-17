<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240129233715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contract ADD frequency VARCHAR(255) NOT NULL AFTER amount, CHANGE monthly_amount amount NUMERIC(8, 2) DEFAULT NULL');
        $this->addSql("UPDATE contract SET frequency = 'monthly' WHERE amount IS NOT NULL");
        $this->addSql("UPDATE contract SET frequency = 'daily' WHERE amount IS NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contract DROP frequency, CHANGE amount monthly_amount NUMERIC(8, 2) DEFAULT NULL');
    }
}
