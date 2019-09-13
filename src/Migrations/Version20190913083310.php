<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190913083310 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tvshow ADD premiered VARCHAR(255) DEFAULT NULL, CHANGE poster poster VARCHAR(255) DEFAULT NULL, CHANGE rating rating DOUBLE PRECISION DEFAULT NULL, CHANGE language language VARCHAR(255) DEFAULT NULL, CHANGE runtime runtime INT DEFAULT NULL, CHANGE id_imdb id_imdb VARCHAR(255) DEFAULT NULL, CHANGE api_update api_update INT DEFAULT NULL, CHANGE id_tvdb id_tvdb INT DEFAULT NULL, CHANGE upadted_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE episode CHANGE runtime runtime INT DEFAULT NULL, CHANGE summary summary LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE season CHANGE episode_count episode_count INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE episode CHANGE runtime runtime INT NOT NULL, CHANGE summary summary LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE season CHANGE episode_count episode_count INT NOT NULL');
        $this->addSql('ALTER TABLE tvshow DROP premiered, CHANGE poster poster VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE rating rating INT NOT NULL, CHANGE language language VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE runtime runtime INT NOT NULL, CHANGE id_imdb id_imdb INT DEFAULT NULL, CHANGE api_update api_update DATETIME DEFAULT NULL, CHANGE id_tvdb id_tvdb INT NOT NULL, CHANGE updated_at upadted_at DATETIME DEFAULT NULL');
    }
}
