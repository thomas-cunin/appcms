<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240918215125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE storage_config (id INT AUTO_INCREMENT NOT NULL, s3_bucket_name VARCHAR(255) NOT NULL, s3_access_key_id VARCHAR(255) NOT NULL, s3_secret_access_key VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE application ADD storage_config_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1CCC0198C FOREIGN KEY (storage_config_id) REFERENCES storage_config (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A45BDDC1CCC0198C ON application (storage_config_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC1CCC0198C');
        $this->addSql('DROP TABLE storage_config');
        $this->addSql('DROP INDEX UNIQ_A45BDDC1CCC0198C ON application');
        $this->addSql('ALTER TABLE application DROP storage_config_id');
    }
}
