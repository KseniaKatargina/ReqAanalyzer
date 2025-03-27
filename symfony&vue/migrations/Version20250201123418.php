<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250201123418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE text_analysis_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE text_analyses_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE text_analyses (id INT NOT NULL, user_id_id INT DEFAULT NULL, processed_text TEXT NOT NULL, description TEXT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1AC4042E9D86650F ON text_analyses (user_id_id)');
        $this->addSql('ALTER TABLE text_analyses ADD CONSTRAINT FK_1AC4042E9D86650F FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE text_analyses_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE text_analysis_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE text_analyses DROP CONSTRAINT FK_1AC4042E9D86650F');
        $this->addSql('DROP TABLE text_analyses');
    }
}
