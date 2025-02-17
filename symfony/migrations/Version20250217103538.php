<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250217103538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ledger (id SERIAL NOT NULL, wallet_id INT NOT NULL, amount NUMERIC(10, 2) NOT NULL, description VARCHAR(255) NOT NULL, transaction_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, transaction_type VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C07BA4BC712520F3 ON ledger (wallet_id)');
        $this->addSql('ALTER TABLE ledger ADD CONSTRAINT FK_C07BA4BC712520F3 FOREIGN KEY (wallet_id) REFERENCES wallet (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE ledger DROP CONSTRAINT FK_C07BA4BC712520F3');
        $this->addSql('DROP TABLE ledger');
    }
}
