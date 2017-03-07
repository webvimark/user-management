<?php

use yii\db\Migration;

class m170307_100221_update_email_type extends Migration
{
    public function up()
    {
        $this->alterColumn('user', 'email', 'varchar(254)');
    }

    public function down()
    {
        echo "m170307_100221_update_email_type cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
