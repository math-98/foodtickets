<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240218004036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE account SET individual_price = individual_price * 100');
        $this->addSql('ALTER TABLE account CHANGE individual_price individual_price INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE account CHANGE individual_price individual_price NUMERIC(8, 2) DEFAULT NULL');
        $this->addSql('UPDATE account SET individual_price = individual_price / 100');
    }
}
