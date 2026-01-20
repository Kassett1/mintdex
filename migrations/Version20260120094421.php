<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120094421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY `FK_161498D3EBB56231`');
        $this->addSql('DROP INDEX IDX_161498D3EBB56231 ON card');
        $this->addSql('ALTER TABLE card CHANGE set_id_id set_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D310FB0D18 FOREIGN KEY (set_id) REFERENCES `set` (id)');
        $this->addSql('CREATE INDEX IDX_161498D310FB0D18 ON card (set_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D310FB0D18');
        $this->addSql('DROP INDEX IDX_161498D310FB0D18 ON card');
        $this->addSql('ALTER TABLE card CHANGE set_id set_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT `FK_161498D3EBB56231` FOREIGN KEY (set_id_id) REFERENCES `set` (id)');
        $this->addSql('CREATE INDEX IDX_161498D3EBB56231 ON card (set_id_id)');
    }
}
