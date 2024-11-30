<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241129211745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket ADD purchase_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3558FBEB9 FOREIGN KEY (purchase_id) REFERENCES `order` (id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA3558FBEB9 ON ticket (purchase_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3558FBEB9');
        $this->addSql('DROP INDEX IDX_97A0ADA3558FBEB9 ON ticket');
        $this->addSql('ALTER TABLE ticket DROP purchase_id');
    }
}
