<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SyncPengurusReference extends BaseCommand
{
    protected $group = 'RW 05';
    protected $name = 'rw:sync-pengurus-reference';
    protected $description = 'Menyelaraskan data pengurus dengan bagan resmi tanpa menghapus kontak atau data tambahan.';
    protected $usage = 'rw:sync-pengurus-reference [--apply]';
    protected $options = [
        '--apply' => 'Simpan perubahan. Tanpa opsi ini perintah hanya menampilkan rencana.',
    ];

    public function run(array $params)
    {
        $apply = (bool) CLI::getOption('apply');
        $db = db_connect();
        if (! $db->tableExists('pengurus')) {
            CLI::error('Tabel pengurus tidak tersedia.');

            return EXIT_ERROR;
        }

        $existing = $db->table('pengurus')->orderBy('id', 'ASC')->get()->getResultArray();
        $claimed = [];
        $updates = [];
        $inserts = [];

        foreach ($this->referenceRows() as $reference) {
            $match = null;
            foreach ($existing as $row) {
                $id = (int) ($row['id'] ?? 0);
                if ($id < 1 || isset($claimed[$id])) {
                    continue;
                }
                $candidate = $this->normalizeName((string) ($row['nama'] ?? ''));
                foreach ($reference['aliases'] as $alias) {
                    if ($candidate === $this->normalizeName($alias)) {
                        $match = $row;
                        break 2;
                    }
                }
            }

            $data = [
                'urutan' => $reference['urutan'],
                'nama' => $reference['nama'],
                'jabatan' => $reference['jabatan'],
                'rt' => $reference['rt'],
                'tugas' => $reference['tugas'],
                'status' => 'aktif',
            ];

            if ($match !== null) {
                $id = (int) $match['id'];
                $claimed[$id] = true;
                $updates[] = ['id' => $id, 'data' => $data];
            } else {
                // no_hp is deliberately blank only for a genuinely new role.
                // Existing phone numbers are never overwritten by this command.
                $inserts[] = $data + ['no_hp' => ''];
            }
        }

        CLI::write(sprintf('Rencana: %d data diperbarui, %d data ditambahkan, 0 data dihapus.', count($updates), count($inserts)), 'yellow');
        if (! $apply) {
            CLI::write('Gunakan --apply setelah rencana diperiksa.', 'cyan');

            return EXIT_SUCCESS;
        }

        $db->transStart();
        foreach ($updates as $update) {
            $db->table('pengurus')->where('id', $update['id'])->update($update['data']);
        }
        foreach ($inserts as $insert) {
            $db->table('pengurus')->insert($insert);
        }
        $db->transComplete();

        if (! $db->transStatus()) {
            CLI::error('Sinkronisasi gagal dan transaksi dibatalkan.');

            return EXIT_ERROR;
        }

        CLI::write('Data pengurus berhasil diselaraskan. Nomor HP yang sudah ada tetap dipertahankan.', 'green');

        return EXIT_SUCCESS;
    }

    private function normalizeName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/\b(bpk|bapak|ibu|ust|ustadz)\.?\s+/i', '', $name);
        $name = preg_replace('/[,.;]+\s*(m\.?\s*kep|s\.?\s*kep|dr|spd|s\.?\s*pd)\.?$/i', '', $name);
        $name = preg_replace('/[^a-z0-9]+/', ' ', $name);

        return trim(preg_replace('/\s+/', ' ', $name));
    }

    private function referenceRows(): array
    {
        $rows = [
            ['Kepala Desa', 'Pembina', '', 'Memberikan pembinaan dan arahan umum kepada pengurus RW.', ['Kepala Desa']],
            ['Dwi Wahyu Bintarto Prasetyo', 'Ketua RW 05', '03', 'Memimpin, mengoordinasikan, dan mengevaluasi pelayanan serta program kerja RW 05.', ['Dwi Wahyu Bintarto Prasetyo']],
            ['Bpk. H. Sumaryono', 'Penasihat', '', 'Memberikan saran dan pertimbangan kepada Ketua RW dan pengurus.', ['H Sumaryono', 'Sumaryono']],
            ['Bpk. Erno', 'Penasihat', '', 'Memberikan saran dan pertimbangan kepada Ketua RW dan pengurus.', ['Erno']],
            ['Bpk. Irwan (Ujang Cuek)', 'Penasihat', '', 'Memberikan saran dan pertimbangan kepada Ketua RW dan pengurus.', ['Irwan (Ujang Cuek)', 'Irwan', 'Ujang Cuek']],
            ['Ibu Nia Kurniasih', 'Sekretaris', '02', 'Mengelola administrasi, surat-menyurat, notulen, agenda, dan arsip RW.', ['Nia Kurniasih']],
            ['Bpk. Wahyu Budiman', 'Bendahara', '01', 'Mengelola pencatatan, penerimaan, pengeluaran, dan laporan keuangan RW.', ['Wahyu Budiman']],
            ['Ibu Kartika', 'Ketua RT 01', '01', 'Mengoordinasikan pelayanan, pendataan, dan penyampaian informasi warga RT 01.', ['Kartika']],
            ['Bpk. Kurnia', 'Ketua RT 02', '02', 'Mengoordinasikan pelayanan, pendataan, dan penyampaian informasi warga RT 02.', ['Kurnia']],
            ['Bpk. Dadan Ruhimat', 'Ketua RT 03', '03', 'Mengoordinasikan pelayanan, pendataan, dan penyampaian informasi warga RT 03.', ['Dadan Ruhimat', 'Dadan']],
            ['Wahyu Dwi Haryono', 'Unit Pelayanan Digital & Data Warga', '02', "Administrasi online\nDatabase warga\nLayanan surat", ['Wahyu Dwi Haryono']],
            ['Bpk. Atang', 'Bidang Pembangunan & Lingkungan', '03', 'Mengoordinasikan pembangunan lingkungan, kebersihan, dan pemeliharaan fasilitas warga.', ['Atang']],
            ['Bpk. Dede (Oding)', 'Bidang Pembangunan & Lingkungan', '01', 'Mengoordinasikan pembangunan lingkungan, kebersihan, dan pemeliharaan fasilitas warga.', ['Dede (Oding)', 'Dede Setiawan', 'Dede']],
            ['Ibu Yunita Fitri Rejeki', 'Bidang Sosial & Kesehatan', '03', 'Mengoordinasikan kegiatan sosial, kesehatan warga, Posyandu, dan Posbindu.', ['Yunita Fitri Rejeki', 'Yunita Fitri Rejeki M kep']],
            ['Bpk. Yogi', 'Bidang Keamanan & Ketertiban', '03', 'Mengoordinasikan keamanan, ketertiban, kesiapsiagaan, dan komunikasi lingkungan.', ['Yogi H', 'Yogi']],
            ['Bpk. Ibnu Majah', 'Bidang Keamanan & Ketertiban', '', 'Mengoordinasikan keamanan, ketertiban, kesiapsiagaan, dan komunikasi lingkungan.', ['Ibnu Majah']],
            ['Bpk. Usep', 'Bidang Pendidikan, Agama & Budaya', '01', 'Mengoordinasikan kegiatan pendidikan, keagamaan, dan pelestarian budaya warga.', ['Usep']],
            ['Bpk. Ust. Usman Ansori', 'Bidang Pendidikan, Agama & Budaya', '03', 'Mengoordinasikan kegiatan pendidikan, keagamaan, dan pelestarian budaya warga.', ['Ust Usman Ansori', 'Usman Ansori', 'Utd Usman', 'Usman']],
            ['Andre Febrian', 'Bidang Ekonomi, Pemuda & Olahraga', '02', 'Mendorong kegiatan ekonomi warga serta program kepemudaan dan olahraga.', ['Andre Febrian']],
            ['Acep Kurnia', 'Bidang Humas & Informasi Publik', '01', 'Mengelola hubungan masyarakat, dokumentasi, dan penyampaian informasi publik RW.', ['Acep Kurnia']],
            ['Ibu Nani Maryani', 'PKK', '', 'Mengoordinasikan kegiatan PKK dan program kesejahteraan keluarga.', ['Nani Maryani']],
            ['Ibu Nia Kurniasih', 'Posyandu', '02', 'Mengoordinasikan pelayanan Posyandu, pencatatan kunjungan, dan tindak lanjut sasaran.', ['Nia Kurniasih']],
            ['Ibu Nia Kurniasih', 'Posbindu', '02', 'Mengoordinasikan skrining Posbindu PTM, pencatatan hasil, edukasi, dan tindak lanjut.', ['Nia Kurniasih']],
            ['Rudi', 'Karang Taruna', '', 'Mengoordinasikan kegiatan kepemudaan dan kemitraan Karang Taruna dengan RW.', ['Rudi']],
            ['Bpk. Ust. Usman Ansori', 'DKM / Keagamaan', '03', 'Mengoordinasikan kegiatan keagamaan dan kemitraan DKM dengan RW.', ['Ust Usman Ansori', 'Usman Ansori', 'Utd Usman', 'Usman']],
            ['Bpk. Tono', 'Linmas / Siskamling', '', 'Mendukung perlindungan masyarakat, ronda, dan kesiapsiagaan keamanan lingkungan.', ['Tono']],
        ];

        return array_map(static function (array $row, int $index): array {
            return [
                'urutan' => ($index + 1) * 10,
                'nama' => $row[0],
                'jabatan' => $row[1],
                'rt' => $row[2],
                'tugas' => $row[3],
                'aliases' => array_values(array_unique(array_merge([$row[0]], $row[4]))),
            ];
        }, $rows, array_keys($rows));
    }
}
