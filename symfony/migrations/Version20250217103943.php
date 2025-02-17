<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250217103943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transaction (id SERIAL NOT NULL, reference VARCHAR(100) NOT NULL, description VARCHAR(255) NOT NULL, transaction_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_723705D1AEA34913 ON transaction (reference)');
        $this->addSql('ALTER TABLE ledger ADD transaction_id INT NOT NULL');
        $this->addSql('ALTER TABLE ledger ADD CONSTRAINT FK_C07BA4BC2FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C07BA4BC2FC0CB0F ON ledger (transaction_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE ledger DROP CONSTRAINT FK_C07BA4BC2FC0CB0F');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP INDEX IDX_C07BA4BC2FC0CB0F');
        $this->addSql('ALTER TABLE ledger DROP transaction_id');
    }
}
