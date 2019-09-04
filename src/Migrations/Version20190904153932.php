<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190904153932 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `show` (id INT AUTO_INCREMENT NOT NULL, type_id INT DEFAULT NULL, network_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, summary LONGTEXT NOT NULL, status INT NOT NULL, poster VARCHAR(255) NOT NULL, website VARCHAR(255) DEFAULT NULL, rating INT NOT NULL, language VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, runtime INT NOT NULL, id_tvmaze INT NOT NULL, id_imdb INT DEFAULT NULL, created_at DATETIME NOT NULL, upadted_at DATETIME DEFAULT NULL, api_update DATETIME DEFAULT NULL, INDEX IDX_320ED901C54C8C93 (type_id), INDEX IDX_320ED90134128B91 (network_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE show_genre (show_id INT NOT NULL, genre_id INT NOT NULL, INDEX IDX_81E15724D0C1FC64 (show_id), INDEX IDX_81E157244296D31F (genre_id), PRIMARY KEY(show_id, genre_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE following (id INT AUTO_INCREMENT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME DEFAULT NULL, status INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE episode (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, number INT NOT NULL, runtime INT NOT NULL, summary LONGTEXT NOT NULL, airstamp DATETIME NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE network (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE season (id INT AUTO_INCREMENT NOT NULL, number INT NOT NULL, poster VARCHAR(255) DEFAULT NULL, episode_count INT NOT NULL, premiere_date DATETIME NOT NULL, end_date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE genre (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `show` ADD CONSTRAINT FK_320ED901C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE `show` ADD CONSTRAINT FK_320ED90134128B91 FOREIGN KEY (network_id) REFERENCES network (id)');
        $this->addSql('ALTER TABLE show_genre ADD CONSTRAINT FK_81E15724D0C1FC64 FOREIGN KEY (show_id) REFERENCES `show` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE show_genre ADD CONSTRAINT FK_81E157244296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE show_genre DROP FOREIGN KEY FK_81E15724D0C1FC64');
        $this->addSql('ALTER TABLE `show` DROP FOREIGN KEY FK_320ED901C54C8C93');
        $this->addSql('ALTER TABLE `show` DROP FOREIGN KEY FK_320ED90134128B91');
        $this->addSql('ALTER TABLE show_genre DROP FOREIGN KEY FK_81E157244296D31F');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE `show`');
        $this->addSql('DROP TABLE show_genre');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE following');
        $this->addSql('DROP TABLE episode');
        $this->addSql('DROP TABLE type');
        $this->addSql('DROP TABLE network');
        $this->addSql('DROP TABLE season');
        $this->addSql('DROP TABLE genre');
    }
}
