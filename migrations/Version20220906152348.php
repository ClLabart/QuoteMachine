<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220906152348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE likes (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE quotes CHANGE likes user_quoting_id INT NOT NULL');
        $this->addSql('ALTER TABLE quotes ADD CONSTRAINT FK_A1B588C5C0B6C4AF FOREIGN KEY (user_quoting_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A1B588C5C0B6C4AF ON quotes (user_quoting_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE likes');
        $this->addSql('ALTER TABLE quotes DROP FOREIGN KEY FK_A1B588C5C0B6C4AF');
        $this->addSql('DROP INDEX IDX_A1B588C5C0B6C4AF ON quotes');
        $this->addSql('ALTER TABLE quotes CHANGE user_quoting_id likes INT NOT NULL');
    }
}
