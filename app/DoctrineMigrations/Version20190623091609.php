<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190623091609 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO `number` (`value`, `inherited`, `duty`, `social`, `structure`, `global`, `lifePath`, `ideal`, `personalYearNow`, `secret`, `astrological`, `veryShortTerm`, `shortTerm`, `meanTerm`, `longTerm`, `missing`, `weak`, `strong`, `average`)
VALUES (\'10\', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, \'<div>xxx</div>\', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL), (\'12\', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, \'<div>xxx</div>\', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE `number` WHERE `value` IN (10, 12)');
    }
}
