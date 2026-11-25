<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261119201748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category CHANGE name name VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE comment CHANGE tutorial_id tutorial_id INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C66BFFE9989D9B62 ON tutorial (slug)');
        $this->addSql('ALTER TABLE user ADD pseudonyme VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE comment CHANGE tutorial_id tutorial_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_C66BFFE9989D9B62 ON tutorial');
        $this->addSql('ALTER TABLE user DROP pseudonyme');
    }
}
