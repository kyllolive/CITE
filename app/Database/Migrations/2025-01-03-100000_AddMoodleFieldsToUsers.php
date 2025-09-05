<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMoodleFieldsToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'moodle_token' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'moodle_id'
            ],
            'moodle_username' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
                'after' => 'moodle_token'
            ],
            'last_moodle_sync' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'moodle_username'
            ]
        ];
        
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'moodle_token');
        $this->forge->dropColumn('users', 'moodle_username');
        $this->forge->dropColumn('users', 'last_moodle_sync');
    }
}