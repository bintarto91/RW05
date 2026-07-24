<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use RuntimeException;
use Throwable;

class ImportController extends BaseController
{
    private function datasets(): array
    {
        return [
            'program' => [
                'label' => 'Program Kerja',
                'table' => 'program_kerja',
                'columns' => ['nomor', 'judul', 'deskripsi', 'status'],
                'required' => ['judul', 'deskripsi'],
                'sample' => [['1', 'Digitalisasi Informasi RW', 'Website dan media sosial resmi untuk pusat informasi warga.', 'aktif']],
            ],
            'kegiatan' => [
                'label' => 'Kegiatan',
                'table' => 'kegiatan',
                'columns' => ['judul', 'kategori', 'tanggal', 'isi', 'status'],
                'required' => ['judul', 'tanggal', 'isi'],
                'sample' => [['Kerja Bakti Lingkungan', 'Kebersihan', '2026-06-24', 'Kerja bakti dimulai pukul 07.00 WIB di tiap RT.', 'publish']],
            ],
            'layanan' => [
                'label' => 'Layanan',
                'table' => 'layanan',
                'columns' => ['urutan', 'icon', 'nama', 'status', 'deskripsi'],
                'required' => ['nama', 'deskripsi'],
                'sample' => [['1', 'SP', 'Surat Pengantar', 'aktif', 'Informasi alur pengurusan surat pengantar RT/RW.']],
            ],
            'pengurus' => [
                'label' => 'Pengurus',
                'table' => 'pengurus',
                'columns' => ['urutan', 'nama', 'jabatan', 'rt', 'no_hp', 'tugas', 'status'],
                'required' => ['nama', 'jabatan'],
                'sample' => [['1', 'Dwi Wahyu Bintarto P., S.Kom', 'Ketua RW 05', '', '08123456789', 'Memimpin program RW 05.', 'aktif']],
            ],
            'warga' => [
                'label' => 'Data Warga & Sosial',
                'table' => 'warga',
                'columns' => warga_csv_columns(),
                'required' => ['nama_kepala_keluarga', 'rt'],
                'notes' => warga_csv_notes(),
                'sample' => [
                    ['Bapak Ahmad', '01', 'Jl. Mawar No. 12', '4', '08123456789', 'Buruh harian', 'tetap', 'kurang_mampu', 'ya', 'BPNT, PKH', 'Ada lansia dalam keluarga', 'Rumah pojok dekat mushola'],
                    ['Ibu Siti Nurhayati', '01', 'Kp. Citeureup Blok A3', '3', '081298765432', 'Karyawan pabrik', 'kontrak', 'rentan', 'tidak', '', 'Pendatang kerja pabrik', 'Perlu pemantauan kontrak tahunan'],
                    ['Bapak Rudi Hartono', '02', 'Jl. Anggrek No. 5', '5', '082112223333', 'Pedagang', 'tetap', 'umum', 'tidak', '', 'Aktif ronda malam', 'Usaha warung berjalan stabil'],
                ],
            ],
        ];
    }

    public function index()
    {
        $datasets = $this->datasets();
        $selectedType = $this->request->getPost('dataset') ?: ($this->request->getGet('type') ?: 'warga');
        $returnTo = (string) ($this->request->getPost('return_to') ?: $this->request->getGet('return_to'));
        if (! isset($datasets[$selectedType])) {
            $selectedType = 'warga';
        }

        $success = session()->getFlashdata('success') ?: '';
        $error = '';
        $details = [];

        if ($this->request->getMethod() === 'POST') {
            $mode = $this->request->getPost('mode') === 'replace' ? 'replace' : 'append';
            $file = $this->request->getFile('csv_file');

            if (! $file || ! $file->isValid()) {
                $error = 'Silakan pilih file CSV yang ingin diimport.';
            } else {
                $result = $this->importFile($file->getTempName(), $selectedType, $datasets[$selectedType], $mode);
                $success = $result['success'] ?? '';
                $error = $result['error'] ?? '';
                $details = $result['details'] ?? [];
            }

            if ($returnTo === 'warga' && $selectedType === 'warga') {
                $redirect = redirect()->to(site_url('admin/warga'));
                if ($success !== '') {
                    $redirect = $redirect->with('success', $success);
                }
                if ($error !== '') {
                    $message = $error;
                    if (! empty($details)) {
                        $message .= ' ' . implode(' ', $details);
                    }
                    $redirect = $redirect->with('error', $message);
                }

                return $redirect;
            }
        }

        return view('admin/import', [
            'currentPage' => 'import',
            'datasets' => $datasets,
            'selectedType' => $selectedType,
            'currentDataset' => $datasets[$selectedType],
            'success' => $success,
            'error' => $error,
            'details' => $details,
        ]);
    }

    public function template(string $type)
    {
        $datasets = $this->datasets();
        if (! isset($datasets[$type])) {
            return $this->response->setStatusCode(404)->setBody('Template tidak ditemukan.');
        }

        $dataset = $datasets[$type];
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $dataset['columns']);
        foreach ($dataset['sample'] as $sample) {
            fputcsv($handle, $sample);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="template-' . $type . '.csv"')
            ->setBody("\xEF\xBB\xBF" . $csv);
    }

    private function importFile(string $filePath, string $type, array $dataset, string $mode): array
    {
        $parsed = $this->parseCsv($filePath);
        if (! empty($parsed['error'])) {
            return ['error' => $parsed['error'], 'details' => []];
        }

        $missingHeaders = array_diff($dataset['required'], $parsed['headers']);
        if ($missingHeaders) {
            return ['error' => 'Kolom wajib belum lengkap: ' . implode(', ', $missingHeaders) . '.', 'details' => []];
        }
        if (! $parsed['rows']) {
            return ['error' => 'File CSV tidak memiliki data untuk diimport.', 'details' => []];
        }

        $db = db_connect();

        try {
            if ($type === 'warga' && ! ensure_warga_table($db)) {
                throw new RuntimeException('Tabel warga belum siap untuk menerima format data terbaru.');
            }

            $db->transBegin();

            if ($mode === 'replace') {
                $db->table($dataset['table'])->emptyTable();
            }

            $imported = 0;
            foreach ($parsed['rows'] as $index => $entry) {
                $line = (int) ($entry['line'] ?? ($index + 2));
                $data = $entry['data'] ?? [];

                foreach ($dataset['required'] as $requiredColumn) {
                    if (trim((string) ($data[$requiredColumn] ?? '')) === '') {
                        throw new RuntimeException('Baris ' . $line . ': kolom ' . $requiredColumn . ' wajib diisi.');
                    }
                }

                $db->table($dataset['table'])->insert($this->buildRecord($type, $data));
                $imported++;
            }

            if ($db->transStatus() === false) {
                throw new RuntimeException('Transaksi database gagal.');
            }

            $db->transCommit();

            return ['success' => $dataset['label'] . ' berhasil diimport sebanyak ' . $imported . ' baris.', 'details' => []];
        } catch (Throwable $exception) {
            $db->transRollback();

            return [
                'error' => 'Import dibatalkan karena ada data yang tidak valid.',
                'details' => [$exception->getMessage()],
            ];
        }
    }

    private function parseCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'rb');
        if (! $handle) {
            return ['error' => 'File CSV tidak dapat dibaca.'];
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return ['error' => 'File CSV kosong.'];
        }

        $delimiter = $this->detectDelimiter($firstLine);
        rewind($handle);

        $headers = fgetcsv($handle, 0, $delimiter);
        if ($headers === false) {
            fclose($handle);
            return ['error' => 'Header CSV tidak valid.'];
        }

        $headers = array_map([$this, 'normalizeHeader'], $headers);
        $rows = [];
        $lineNumber = 1;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $lineNumber++;
            if ($this->isBlankRow($data)) {
                continue;
            }

            $data = array_pad($data, count($headers), '');
            $rows[] = [
                'line' => $lineNumber,
                'data' => array_combine($headers, array_map(static fn ($value) => trim((string) $value), array_slice($data, 0, count($headers)))),
            ];
        }

        fclose($handle);

        return ['headers' => $headers, 'rows' => $rows];
    }

    private function normalizeHeader($value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', (string) $value);
        $value = strtolower(trim($value));
        $value = str_replace([' ', '-'], '_', $value);

        return preg_replace('/[^a-z0-9_]/', '', $value);
    }

    private function detectDelimiter(string $line): string
    {
        $selected = ',';
        $bestCount = -1;
        foreach ([',', ';', "\t", '|'] as $delimiter) {
            $count = substr_count($line, $delimiter);
            if ($count > $bestCount) {
                $bestCount = $count;
                $selected = $delimiter;
            }
        }

        return $selected;
    }

    private function isBlankRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeStatus($value, array $allowed, string $default): string
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function normalizeDate($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        $timestamp = strtotime($value);

        return $timestamp ? date('Y-m-d', $timestamp) : '';
    }

    private function buildRecord(string $type, array $row): array
    {
        if ($type === 'program') {
            return [
                'nomor' => (int) ($row['nomor'] ?? 0),
                'judul' => trim((string) ($row['judul'] ?? '')),
                'deskripsi' => trim((string) ($row['deskripsi'] ?? '')),
                'status' => $this->normalizeStatus($row['status'] ?? 'aktif', ['aktif', 'nonaktif'], 'aktif'),
            ];
        }

        if ($type === 'kegiatan') {
            $tanggal = $this->normalizeDate($row['tanggal'] ?? '');
            if ($tanggal === '') {
                throw new RuntimeException('Kolom tanggal wajib berformat tanggal yang valid.');
            }

            return [
                'judul' => trim((string) ($row['judul'] ?? '')),
                'kategori' => trim((string) ($row['kategori'] ?? 'Pengumuman')) ?: 'Pengumuman',
                'tanggal' => $tanggal,
                'isi' => trim((string) ($row['isi'] ?? '')),
                'status' => $this->normalizeStatus($row['status'] ?? 'publish', ['publish', 'draft'], 'publish'),
            ];
        }

        if ($type === 'layanan') {
            $nama = trim((string) ($row['nama'] ?? ''));
            $icon = trim((string) ($row['icon'] ?? '')) ?: service_badge($nama, '');

            return [
                'urutan' => (int) ($row['urutan'] ?? 0),
                'icon' => $icon,
                'nama' => $nama,
                'status' => $this->normalizeStatus($row['status'] ?? 'aktif', ['aktif', 'nonaktif'], 'aktif'),
                'deskripsi' => trim((string) ($row['deskripsi'] ?? '')),
            ];
        }

        if ($type === 'pengurus') {
            return [
                'urutan' => (int) ($row['urutan'] ?? 0),
                'nama' => trim((string) ($row['nama'] ?? '')),
                'jabatan' => trim((string) ($row['jabatan'] ?? '')),
                'rt' => trim((string) ($row['rt'] ?? '')),
                'no_hp' => trim((string) ($row['no_hp'] ?? '')),
                'tugas' => trim((string) ($row['tugas'] ?? '')),
                'status' => $this->normalizeStatus($row['status'] ?? 'aktif', ['aktif', 'nonaktif'], 'aktif'),
            ];
        }

        $penerimaBantuan = $this->normalizeStatus($row['penerima_bantuan'] ?? 'tidak', array_keys(warga_bantuan_options()), 'tidak');
        $jenisBantuan = trim((string) ($row['jenis_bantuan'] ?? ''));

        if ($penerimaBantuan === 'ya' && $jenisBantuan === '') {
            throw new RuntimeException('Kolom jenis_bantuan wajib diisi jika penerima_bantuan bernilai ya.');
        }

        return [
            'nama_kepala_keluarga' => trim((string) ($row['nama_kepala_keluarga'] ?? '')),
            'rt' => normalize_rt_code($row['rt'] ?? ''),
            'alamat' => trim((string) ($row['alamat'] ?? '')),
            'jumlah_anggota' => max(1, (int) ($row['jumlah_anggota'] ?? 1)),
            'no_hp' => trim((string) ($row['no_hp'] ?? '')),
            'pekerjaan_kepala_keluarga' => trim((string) ($row['pekerjaan_kepala_keluarga'] ?? '')),
            'status_tinggal' => $this->normalizeStatus($row['status_tinggal'] ?? 'tetap', array_keys(warga_status_tinggal_options()), 'tetap'),
            'kategori_kesejahteraan' => $this->normalizeStatus($row['kategori_kesejahteraan'] ?? 'umum', array_keys(warga_kesejahteraan_options()), 'umum'),
            'penerima_bantuan' => $penerimaBantuan,
            'jenis_bantuan' => $jenisBantuan,
            'kondisi_khusus' => trim((string) ($row['kondisi_khusus'] ?? '')),
            'keterangan' => trim((string) ($row['keterangan'] ?? '')),
        ];
    }
}
