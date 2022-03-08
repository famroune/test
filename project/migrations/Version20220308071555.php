<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220308071555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE exclusion (id INT AUTO_INCREMENT NOT NULL, ean VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE favori (id INT AUTO_INCREMENT NOT NULL, ean VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_favori (user_id INT NOT NULL, favori_id INT NOT NULL, INDEX IDX_8AD7B9F1A76ED395 (user_id), INDEX IDX_8AD7B9F1FF17033F (favori_id), PRIMARY KEY(user_id, favori_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_exclusion (user_id INT NOT NULL, exclusion_id INT NOT NULL, INDEX IDX_D11E588A76ED395 (user_id), INDEX IDX_D11E5884012D45C (exclusion_id), PRIMARY KEY(user_id, exclusion_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_favori ADD CONSTRAINT FK_8AD7B9F1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favori ADD CONSTRAINT FK_8AD7B9F1FF17033F FOREIGN KEY (favori_id) REFERENCES favori (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_exclusion ADD CONSTRAINT FK_D11E588A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_exclusion ADD CONSTRAINT FK_D11E5884012D45C FOREIGN KEY (exclusion_id) REFERENCES exclusion (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_exclusion DROP FOREIGN KEY FK_D11E5884012D45C');
        $this->addSql('ALTER TABLE user_favori DROP FOREIGN KEY FK_8AD7B9F1FF17033F');
        $this->addSql('ALTER TABLE user_favori DROP FOREIGN KEY FK_8AD7B9F1A76ED395');
        $this->addSql('ALTER TABLE user_exclusion DROP FOREIGN KEY FK_D11E588A76ED395');
        $this->addSql('DROP TABLE exclusion');
        $this->addSql('DROP TABLE favori');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_favori');
        $this->addSql('DROP TABLE user_exclusion');
    }
}
