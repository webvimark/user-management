<?php

use yii\db\Migration;

class m160113_182211_alter_user_visit_log extends Migration
{
    
    public function safeUp()
    {
        $this->alterColumn('{{%user_visit_log}}', 'ip', 'VARCHAR(15) NULL');
    }

    public function safeDown()
    {
    }
    
}
