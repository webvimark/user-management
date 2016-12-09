<?php

use yii\db\Migration;

class m160819_161618_alter_table_user extends Migration
{
    public function up()
    {
        $table = Yii::$app->db->schema->getTableSchema('{{%user}}');
        if (!isset($table->columns['access_token'])) {
            $this->addColumn('{{%user}}', 'access_token', 'VARCHAR(150) UNIQUE');
        }
    }

    public function down()
    {
        $this->dropColumn('{{%user}}', 'access_token');
    }
}
