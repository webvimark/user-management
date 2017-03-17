<?php

use yii\db\Migration;

/**
 * Handles adding auth_type to table `user`.
 */
class m170315_110029_add_auth_type_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->addColumn('user', 'auth_type', $this->string(15)->defaultValue('local'));
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropColumn('user', 'auth_type');
    }
}
