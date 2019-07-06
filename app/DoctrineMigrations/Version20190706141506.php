<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190706141506 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('UPDATE analysis SET example = \'1\' WHERE hash IN (\'5d185ffd6e886\', \'5d185458c7184\', \'5d17d40c8b1a7\', \'5d17ccb52f9e0\', \'5d1270ade2ac8\', \'5d1270028f716\', \'5d12675c06a39\', \'5ccf253a33a63\')');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql('UPDATE analysis SET example = \'0\' WHERE hash IN (\'5d185ffd6e886\', \'5d185458c7184\', \'5d17d40c8b1a7\', \'5d17ccb52f9e0\', \'5d1270ade2ac8\', \'5d1270028f716\', \'5d12675c06a39\', \'5ccf253a33a63\')');
    }
}
