<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190904154944 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE following ADD user_id INT DEFAULT NULL, ADD episode_id INT DEFAULT NULL, ADD season_id INT DEFAULT NULL, ADD tv_show_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE following ADD CONSTRAINT FK_71BF8DE3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE following ADD CONSTRAINT FK_71BF8DE3362B62A0 FOREIGN KEY (episode_id) REFERENCES episode (id)');
        $this->addSql('ALTER TABLE following ADD CONSTRAINT FK_71BF8DE34EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE following ADD CONSTRAINT FK_71BF8DE35E3A35BB FOREIGN KEY (tv_show_id) REFERENCES `show` (id)');
        $this->addSql('CREATE INDEX IDX_71BF8DE3A76ED395 ON following (user_id)');
        $this->addSql('CREATE INDEX IDX_71BF8DE3362B62A0 ON following (episode_id)');
        $this->addSql('CREATE INDEX IDX_71BF8DE34EC001D1 ON following (season_id)');
        $this->addSql('CREATE INDEX IDX_71BF8DE35E3A35BB ON following (tv_show_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE following DROP FOREIGN KEY FK_71BF8DE3A76ED395');
        $this->addSql('ALTER TABLE following DROP FOREIGN KEY FK_71BF8DE3362B62A0');
        $this->addSql('ALTER TABLE following DROP FOREIGN KEY FK_71BF8DE34EC001D1');
        $this->addSql('ALTER TABLE following DROP FOREIGN KEY FK_71BF8DE35E3A35BB');
        $this->addSql('DROP INDEX IDX_71BF8DE3A76ED395 ON following');
        $this->addSql('DROP INDEX IDX_71BF8DE3362B62A0 ON following');
        $this->addSql('DROP INDEX IDX_71BF8DE34EC001D1 ON following');
        $this->addSql('DROP INDEX IDX_71BF8DE35E3A35BB ON following');
        $this->addSql('ALTER TABLE following DROP user_id, DROP episode_id, DROP season_id, DROP tv_show_id');
    }
}
