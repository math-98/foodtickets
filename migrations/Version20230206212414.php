<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230206212414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contract_income (id INT AUTO_INCREMENT NOT NULL, contract_id INT NOT NULL, period VARCHAR(255) NOT NULL, description VARCHAR(1024) NOT NULL, planned NUMERIC(8, 2) NOT NULL, billed NUMERIC(8, 2) NOT NULL, received NUMERIC(8, 2) NOT NULL, INDEX IDX_3E8879D2576E0FD (contract_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract_income ADD CONSTRAINT FK_3E8879D2576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract_income DROP FOREIGN KEY FK_3E8879D2576E0FD');
        $this->addSql('DROP TABLE contract_income');
    }
}
