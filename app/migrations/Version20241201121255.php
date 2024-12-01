<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241201121255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE offer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, processor LONGTEXT NOT NULL, ram INT NOT NULL, storage INT NOT NULL, bandwidth INT NOT NULL, monthly_price DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, offer_id INT DEFAULT NULL, order_date DATETIME NOT NULL, status VARCHAR(255) NOT NULL, total_amount DOUBLE PRECISION NOT NULL, INDEX IDX_F5299398A76ED395 (user_id), INDEX IDX_F529939853C674EE (offer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, purchase_id INT DEFAULT NULL, subject VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, closing_date DATETIME DEFAULT NULL, INDEX IDX_97A0ADA3A76ED395 (user_id), INDEX IDX_97A0ADA3558FBEB9 (purchase_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket_response (id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, user_id INT DEFAULT NULL, message LONGTEXT NOT NULL, response_date DATETIME NOT NULL, INDEX IDX_BB12F77A700047D2 (ticket_id), INDEX IDX_BB12F77AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, address LONGTEXT DEFAULT NULL, zipcode INT DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, is_banned TINYINT(1) NOT NULL, ban_date DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939853C674EE FOREIGN KEY (offer_id) REFERENCES offer (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3558FBEB9 FOREIGN KEY (purchase_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE ticket_response ADD CONSTRAINT FK_BB12F77A700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id)');
        $this->addSql('ALTER TABLE ticket_response ADD CONSTRAINT FK_BB12F77AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939853C674EE');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3A76ED395');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3558FBEB9');
        $this->addSql('ALTER TABLE ticket_response DROP FOREIGN KEY FK_BB12F77A700047D2');
        $this->addSql('ALTER TABLE ticket_response DROP FOREIGN KEY FK_BB12F77AA76ED395');
        $this->addSql('DROP TABLE offer');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE ticket');
        $this->addSql('DROP TABLE ticket_response');
        $this->addSql('DROP TABLE user');
    }
}
