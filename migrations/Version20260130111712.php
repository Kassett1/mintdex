<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260130111712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_card (id INT AUTO_INCREMENT NOT NULL, card_condition VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, user_id INT DEFAULT NULL, card_id INT DEFAULT NULL, INDEX IDX_6C95D41AA76ED395 (user_id), INDEX IDX_6C95D41A4ACC9A20 (card_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user_card ADD CONSTRAINT FK_6C95D41AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_card ADD CONSTRAINT FK_6C95D41A4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_card DROP FOREIGN KEY FK_6C95D41AA76ED395');
        $this->addSql('ALTER TABLE user_card DROP FOREIGN KEY FK_6C95D41A4ACC9A20');
        $this->addSql('DROP TABLE user_card');
    }
}
