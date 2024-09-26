<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240926204006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE room_service (room_id INT NOT NULL, service_id INT NOT NULL, INDEX IDX_DBF263254177093 (room_id), INDEX IDX_DBF2632ED5CA9E6 (service_id), PRIMARY KEY(room_id, service_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE room_service ADD CONSTRAINT FK_DBF263254177093 FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE room_service ADD CONSTRAINT FK_DBF2632ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE room_service DROP FOREIGN KEY FK_DBF263254177093');
        $this->addSql('ALTER TABLE room_service DROP FOREIGN KEY FK_DBF2632ED5CA9E6');
        $this->addSql('DROP TABLE room_service');
    }
}
