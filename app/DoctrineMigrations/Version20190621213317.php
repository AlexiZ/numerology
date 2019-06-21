<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190621213317 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO `definition` (`name`, `content`)
VALUES (\'total\', \'XXX\'), (\'meanLetter\', \'XXX\'), (\'missing\', \'XXX\'), (\'weak\', \'XXX\'), (\'strong\', \'XXX\'), (\'mean\', \'XXX\')');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM `definition` WHERE `name` IN (\'total\', \'meanLetter\', \'missing\', \'weak\', \'strong\', \'mean\')');
    }
}
