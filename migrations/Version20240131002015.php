<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240131002015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE contract_income SET planned = planned * 100, billed = billed * 100, received = received * 100 WHERE contract_id IN (SELECT id FROM contract WHERE account_id IN (SELECT id FROM account WHERE individual_price IS NULL))');
        $this->addSql('ALTER TABLE contract_income CHANGE planned planned INT DEFAULT NULL, CHANGE billed billed INT DEFAULT NULL, CHANGE received received INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contract_income CHANGE planned planned NUMERIC(8, 2) NOT NULL, CHANGE billed billed NUMERIC(8, 2) NOT NULL, CHANGE received received NUMERIC(8, 2) NOT NULL');
        $this->addSql('UPDATE contract_income SET planned = planned / 100, billed = billed / 100, received = received / 100 WHERE contract_id IN (SELECT id FROM contract WHERE account_id IN (SELECT id FROM account WHERE individual_price IS NULL))');
    }
}
