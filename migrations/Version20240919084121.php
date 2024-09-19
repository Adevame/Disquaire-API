<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919084121 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE disc ADD singer_id INT NOT NULL');
        $this->addSql('ALTER TABLE disc ADD CONSTRAINT FK_2AF5530271FD47C FOREIGN KEY (singer_id) REFERENCES singer (id)');
        $this->addSql('CREATE INDEX IDX_2AF5530271FD47C ON disc (singer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE disc DROP FOREIGN KEY FK_2AF5530271FD47C');
        $this->addSql('DROP INDEX IDX_2AF5530271FD47C ON disc');
        $this->addSql('ALTER TABLE disc DROP singer_id');
    }
}
