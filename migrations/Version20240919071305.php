<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919071305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA1271FD47C FOREIGN KEY (singer_id) REFERENCES singer (id)');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA1C38F37CA FOREIGN KEY (disc_id) REFERENCES disc (id)');
        $this->addSql('CREATE INDEX IDX_33EDEEA1271FD47C ON song (singer_id)');
        $this->addSql('CREATE INDEX IDX_33EDEEA1C38F37CA ON song (disc_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA1271FD47C');
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA1C38F37CA');
        $this->addSql('DROP INDEX IDX_33EDEEA1271FD47C ON song');
        $this->addSql('DROP INDEX IDX_33EDEEA1C38F37CA ON song');
    }
}
