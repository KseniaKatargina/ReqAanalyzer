<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221130143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE specification_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE specification (id INT NOT NULL, user_id_id INT DEFAULT NULL, content TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E3F1A9A9D86650F ON specification (user_id_id)');
        $this->addSql('COMMENT ON COLUMN specification.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE specification ADD CONSTRAINT FK_E3F1A9A9D86650F FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE specification_id_seq CASCADE');
        $this->addSql('ALTER TABLE specification DROP CONSTRAINT FK_E3F1A9A9D86650F');
        $this->addSql('DROP TABLE specification');
    }
}
