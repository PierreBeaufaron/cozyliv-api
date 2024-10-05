<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241005132230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review CHANGE date created_at DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE room CHANGE name name VARCHAR(80) NOT NULL, CHANGE rent_price rent_price NUMERIC(6, 2) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE firstname firstname VARCHAR(80) NOT NULL, CHANGE lastname lastname VARCHAR(80) NOT NULL, CHANGE member_since created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review CHANGE created_at date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE firstname firstname VARCHAR(255) NOT NULL, CHANGE lastname lastname VARCHAR(255) NOT NULL, CHANGE created_at member_since DATETIME NOT NULL');
        $this->addSql('ALTER TABLE room CHANGE name name VARCHAR(255) NOT NULL, CHANGE rent_price rent_price DOUBLE PRECISION NOT NULL');
    }
}
