<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260416130320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE follow DROP FOREIGN KEY FK_68344470AC24F853');
        $this->addSql('ALTER TABLE follow DROP FOREIGN KEY FK_68344470D956F010');
        $this->addSql('DROP TABLE follow');
        $this->addSql('ALTER TABLE user_follows DROP FOREIGN KEY FK_136E9479233D34C1');
        $this->addSql('ALTER TABLE user_follows DROP FOREIGN KEY FK_136E94793AD8644E');
        $this->addSql('DROP INDEX IDX_136E94793AD8644E ON user_follows');
        $this->addSql('DROP INDEX IDX_136E9479233D34C1 ON user_follows');
        $this->addSql('ALTER TABLE user_follows ADD follower_id INT NOT NULL, ADD followed_id INT NOT NULL, DROP user_source, DROP user_target, DROP PRIMARY KEY, ADD PRIMARY KEY (follower_id, followed_id)');
        $this->addSql('ALTER TABLE user_follows ADD CONSTRAINT FK_136E9479AC24F853 FOREIGN KEY (follower_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_follows ADD CONSTRAINT FK_136E9479D956F010 FOREIGN KEY (followed_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_136E9479AC24F853 ON user_follows (follower_id)');
        $this->addSql('CREATE INDEX IDX_136E9479D956F010 ON user_follows (followed_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE follow (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, follower_id INT NOT NULL, followed_id INT NOT NULL, UNIQUE INDEX unique_follow (follower_id, followed_id), INDEX IDX_68344470AC24F853 (follower_id), INDEX IDX_68344470D956F010 (followed_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT FK_68344470AC24F853 FOREIGN KEY (follower_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT FK_68344470D956F010 FOREIGN KEY (followed_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_follows DROP FOREIGN KEY FK_136E9479AC24F853');
        $this->addSql('ALTER TABLE user_follows DROP FOREIGN KEY FK_136E9479D956F010');
        $this->addSql('DROP INDEX IDX_136E9479AC24F853 ON user_follows');
        $this->addSql('DROP INDEX IDX_136E9479D956F010 ON user_follows');
        $this->addSql('ALTER TABLE user_follows ADD user_source INT NOT NULL, ADD user_target INT NOT NULL, DROP follower_id, DROP followed_id, DROP PRIMARY KEY, ADD PRIMARY KEY (user_source, user_target)');
        $this->addSql('ALTER TABLE user_follows ADD CONSTRAINT FK_136E9479233D34C1 FOREIGN KEY (user_target) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_follows ADD CONSTRAINT FK_136E94793AD8644E FOREIGN KEY (user_source) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_136E94793AD8644E ON user_follows (user_source)');
        $this->addSql('CREATE INDEX IDX_136E9479233D34C1 ON user_follows (user_target)');
    }
}
