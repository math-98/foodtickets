<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240130224943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE contract SET amount = amount * 100 WHERE account_id IN (SELECT id FROM account WHERE individual_price IS NULL)');
        $this->addSql('ALTER TABLE contract CHANGE amount amount INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contract CHANGE amount amount NUMERIC(8, 2) DEFAULT NULL');
        $this->addSql('UPDATE contract SET monthly_amount = amount / 100 WHERE account_id IN (SELECT id FROM account WHERE individual_price IS NULL)');
    }
}
