<?php

use Phinx\Migration\AbstractMigration;

class CreateOauthTables extends AbstractMigration
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
        $this->query('
            CREATE TABLE `states` (
			    `client_config_id`  varchar(255) not null,
			    `user_id`           varchar(255) not null,
			    `scope`             varchar(255) default null,
			    `issue_time`        integer(10)  not null,
			    `state`             varchar(255) not null,
                primary key(`state`),
                unique key(`client_config_id`, `user_id`, `scope`)
            )
        ');

        $this->query('
            CREATE TABLE `access_tokens` (
			    `client_config_id`  varchar(255) not null,
			    `user_id`           varchar(255) not null,
			    `scope`             varchar(255) default null,
			    `issue_time`        integer(10)  not null,
			    `access_token`      varchar(255) not null,
			    `token_type`        varchar(255) not null,
			    `expires_in`        integer(10)  default null,
                unique key(`client_config_id`, `user_id`, `scope`)
            )
        ');

        $this->query('
            CREATE TABLE `refresh_tokens` (
			    `client_config_id`  varchar(255) not null,
			    `user_id`           varchar(255) not null,
			    `scope`             varchar(255) default null,
			    `issue_time`        integer(10)  not null,
			    `refresh_token`     varchar(255) not null,
                unique key(`client_config_id`, `user_id`, `scope`)
            )
        ');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

        $this->query('DROP TABLE `refresh_tokens`');
        $this->query('DROP TABLE `access_tokens`');
        $this->query('DROP TABLE `states`');
    }
}