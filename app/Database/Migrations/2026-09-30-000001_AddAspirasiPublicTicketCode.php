<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddAspirasiPublicTicketCode extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('aspirasi')) {
            throw new RuntimeException('Table aspirasi must exist before adding public ticket codes.');
        }

        if (! $this->db->fieldExists('kode_tiket', 'aspirasi')) {
            $this->forge->addColumn('aspirasi', [
                'kode_tiket' => [
                    'type' => 'VARCHAR',
                    'constraint' => 40,
                    'null' => true,
                ],
            ]);
        }

        $this->forge->addUniqueKey('kode_tiket', 'aspirasi_kode_tiket_unique');
        $this->forge->processIndexes('aspirasi');
    }

    public function down()
    {
        if ($this->db->fieldExists('kode_tiket', 'aspirasi')) {
            $this->forge->dropKey('aspirasi', 'aspirasi_kode_tiket_unique');
            $this->forge->dropColumn('aspirasi', 'kode_tiket');
        }
    }
}