<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250515112404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE record_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE record (
                id INT NOT NULL,
                code INT NOT NULL,
                number VARCHAR(255) NOT NULL,
                status VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                change_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id),
                UNIQUE (code),
                UNIQUE (number)
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP SEQUENCE record_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE record
        SQL);
    }
}
