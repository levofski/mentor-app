<?php

use Phinx\Migration\AbstractMigration;

class AddGithubAndTwitterUidColumns extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->query('ALTER TABLE `user` ADD COLUMN `github_uid` INTEGER(10) AFTER `github_handle`');
        $this->query('ALTER TABLE `user` ADD COLUMN `twitter_uid` INTEGER(10) AFTER `twitter_handle`');
        $this->query('ALTER TABLE `user` ADD UNIQUE KEY (`github_uid`, `twitter_uid`)');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->query('ALTER TABLE `user` DROP COLUMN `github_uid`');
        $this->query('ALTER TABLE `user` DROP COLUMN `twitter_uid`');
    }
}