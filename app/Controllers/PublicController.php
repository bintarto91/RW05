<?php

namespace App\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;

class PublicController extends BaseController
{
    private const PENGURUS_STRUCTURE_IMAGE = 'struktur-organisasi';
    private const PENGURUS_STRUCTURE_IMAGE_EXTENSIONS = ['webp', 'jpg', 'jpeg', 'png'];
    private const PENGURUS_STRUCTURE_DESCRIPTION = 'struktur-organisasi.txt';

    public function index(): string
    {
        return $this->renderPublic('public/home', ['currentPage' => 'home']);
    }

    public function profil(): string
    {
        return $this->renderPublic('public/profil', ['currentPage' => 'profil']);
    }

    public function layanan(): string
    {
        return $this->renderPublic('public/layanan', ['currentPage' => 'layanan']);
    }

    public function kesehatan(): string
    {
        return $this->renderPublic('public/kesehatan', [
            'currentPage' => 'kesehatan',
            'pageTitle' => 'Kesehatan Warga',
        ]);
    }

    public function edukasiKesehatan(): string
    {
        return $this->renderPublic('public/edukasi_kesehatan', [
            'currentPage' => 'kesehatan',
            'pageTitle' => 'Edukasi Kesehatan',
        ]);
    }

    public function keuangan()
    {
        $db = db_connect();
        $tableReady = ensure_keuangan_transaksi_table($db);
        $selectedMonth = trim((string) ($this->request->getGet('bulan') ?: date('Y-m')));
        if (! preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $selectedMonth = date('Y-m');
        }
        [$selectedStart, $selectedEnd] = keuangan_normalize_date_range(
            $this->request->getGet('start'),
            $this->request->getGet('end'),
            $selectedMonth
        );
        $selectedUnit = keuangan_normalize_unit_filter($this->request->getGet('unit'));
        $legacySelectedRt = normalize_rt_code($this->request->getGet('rt'));
        if ($selectedUnit === '' && $legacySelectedRt !== '') {
            $selectedUnit = 'rt:' . $legacySelectedRt;
        }
        $selectedRt = keuangan_unit_filter_rt($selectedUnit);

        if (! $tableReady) {
            return $this->renderPublic('public/keuangan', [
                'currentPage' => 'keuangan',
                'selectedMonth' => $selectedMonth,
                'selectedStart' => $selectedStart,
                'selectedEnd' => $selectedEnd,
                'selectedRt' => $selectedRt,
                'selectedUnit' => $selectedUnit,
                'selectedUnitLabel' => keuangan_unit_filter_label($selectedUnit),
                'monthLabel' => keuangan_period_label($selectedStart, $selectedEnd),
                'periodLabel' => keuangan_period_label($selectedStart, $selectedEnd),
                'summary' => keuangan_empty_summary(),
                'rtSummaries' => [],
                'unitSummaries' => keuangan_current_unit_summaries(keuangan_empty_summary(), [], keuangan_default_rt_options(), $selectedUnit),
                'rwIncomeRows' => [],
                'panitiaRows' => [],
                    'rwRows' => [],
                    'rtRows' => [],
                'rows' => [],
                'rtOptions' => keuangan_default_rt_options(),
                'unitOptions' => keuangan_unit_options(keuangan_default_rt_options()),
                'error' => 'Data keuangan belum siap ditampilkan. Hubungi admin RW.',
            ]);
        }

        $viewData = $this->publicFinanceViewData($db, $selectedStart, $selectedEnd, $selectedUnit);

        if ($this->request->getGet('export') === 'pdf') {
            return $this->downloadPublicFinancePdf($viewData);
        }

        return $this->renderPublic('public/keuangan', [
            'currentPage' => 'keuangan',
            'error' => '',
        ] + $viewData);
    }

    public function layananOnline(): string
    {
        $tableReady = ensure_pengajuan_surat_table();
        $lookupCode = strtoupper(trim((string) $this->request->getGet('kode')));
        $lookupName = trim((string) $this->request->getGet('nama'));
        $lookupRt = normalize_rt_code($this->request->getGet('rt'));
        $lookupRow = null;
        $lookupRows = [];
        $lookupError = '';

        if ($tableReady && $lookupCode !== '') {
            $lookupRow = db_connect()->table('pengajuan_surat')
                ->where('kode_pengajuan', $lookupCode)
                ->get()
                ->getRowArray();
        } elseif ($tableReady && ($lookupName !== '' || $lookupRt !== '')) {
            if ($lookupName !== '' && strlen($lookupName) < 3) {
                $lookupError = 'Nama pemohon minimal 3 huruf agar pencarian lebih tepat.';
            } else {
                $builder = db_connect()->table('pengajuan_surat')
                    ->orderBy('created_at', 'DESC')
                    ->limit(10);

                if ($lookupName !== '') {
                    $builder->like('nama', $lookupName);
                }

                if ($lookupRt !== '') {
                    $builder->where('rt', $lookupRt);
                }

                $lookupRows = $builder->get()->getResultArray();
            }
        }

        return $this->renderPublic('public/layanan_online', [
                'currentPage' => 'layanan',
            'suratTypes' => surat_type_options(),
            'tableReady' => $tableReady,
            'lookupCode' => $lookupCode,
            'lookupName' => $lookupName,
            'lookupRt' => $lookupRt,
            'lookupRow' => $lookupRow,
            'lookupRows' => $lookupRows,
            'lookupError' => $lookupError,
            'successCode' => session()->getFlashdata('surat_success_code') ?: '',
            'error' => session()->getFlashdata('surat_error') ?: '',
        ]);
    }

    public function submitLayananOnline()
    {
        if (! ensure_pengajuan_surat_table()) {
            return redirect()->to(site_url('layanan-online'))
                ->withInput()
                ->with('surat_error', 'Tabel pengajuan surat belum siap. Silakan hubungi admin RW.');
        }

        $jenisSurat = trim((string) $this->request->getPost('jenis_surat'));
        $nama = trim((string) $this->request->getPost('nama'));
        $noHp = trim((string) $this->request->getPost('no_hp'));
        $rt = trim((string) $this->request->getPost('rt'));
        $alamat = trim((string) $this->request->getPost('alamat'));
        $keperluan = trim((string) $this->request->getPost('keperluan'));
        $detail = trim((string) $this->request->getPost('detail'));
        $lampiranCatatan = trim((string) $this->request->getPost('lampiran_catatan'));
        $suratData = surat_request_data_normalize($jenisSurat, $this->request->getPost('surat_data'));

        if (! in_array($jenisSurat, surat_type_options(), true)) {
            $jenisSurat = 'Surat Pengantar';
            $suratData = surat_request_data_normalize($jenisSurat, $this->request->getPost('surat_data'));
        }

        if ($nama === '' || $noHp === '' || $rt === '' || $alamat === '' || $keperluan === '') {
            return redirect()->to(site_url('layanan-online'))
                ->withInput()
                ->with('surat_error', 'Nama, nomor WhatsApp, RT, alamat, dan keperluan surat wajib diisi.');
        }

        if (strlen($detail) > 2000 || strlen($lampiranCatatan) > 1000) {
            return redirect()->to(site_url('layanan-online'))
                ->withInput()
                ->with('surat_error', 'Detail pengajuan terlalu panjang. Ringkas dulu agar mudah ditinjau pengurus.');
        }

        $missingStructuredFields = [];
        foreach (surat_type_fields($jenisSurat) as $field) {
            if (! empty($field['required']) && trim((string) ($suratData[$field['key']] ?? '')) === '') {
                $missingStructuredFields[] = (string) $field['label'];
            }
        }

        if ($missingStructuredFields !== []) {
            return redirect()->to(site_url('layanan-online'))
                ->withInput()
                ->with('surat_error', 'Lengkapi data surat berikut: ' . implode(', ', $missingStructuredFields) . '.');
        }

        $kodePengajuan = $this->newPengajuanSuratCode();

        db_connect()->table('pengajuan_surat')->insert([
            'kode_pengajuan' => $kodePengajuan,
            'jenis_surat' => $jenisSurat,
            'keperluan' => substr($keperluan, 0, 180),
            'nama' => substr($nama, 0, 120),
            'no_hp' => substr($noHp, 0, 40),
            'rt' => substr($rt, 0, 20),
            'alamat' => substr($alamat, 0, 255),
            'detail' => $detail,
            'detail_json' => $suratData !== [] ? json_encode($suratData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'lampiran_catatan' => $lampiranCatatan,
            'status' => 'menunggu',
        ]);

        return redirect()->to(site_url('layanan-online'))
            ->with('surat_success_code', $kodePengajuan);
    }

    public function cetakSurat(string $kodePengajuan)
    {
        if (! ensure_pengajuan_surat_table()) {
            return redirect()->to(site_url('layanan-online'))
                ->with('surat_error', 'Tabel pengajuan surat belum siap.');
        }

        $kodePengajuan = strtoupper(trim($kodePengajuan));
        $db = db_connect();
        $pengajuan = $db->table('pengajuan_surat')
            ->where('kode_pengajuan', $kodePengajuan)
            ->get()
            ->getRowArray();

        if (! $pengajuan) {
            return redirect()->to(site_url('layanan-online?kode=' . rawurlencode($kodePengajuan)))
                ->with('surat_error', 'Kode pengajuan tidak ditemukan.');
        }

        if (! in_array($pengajuan['status'], ['disetujui', 'selesai'], true)) {
            return redirect()->to(site_url('layanan-online?kode=' . rawurlencode($kodePengajuan)))
                ->with('surat_error', 'Surat belum bisa dicetak karena belum disetujui admin RW.');
        }

        $profil = $db->table('profil_rw')->where('id', 1)->get()->getRowArray() ?: [];
        $profil['email'] = rw_official_email($profil['email'] ?? '');
        $ketua = $db->table('pengurus')
            ->where('status', 'aktif')
            ->like('jabatan', 'Ketua RW')
            ->orderBy('urutan', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();
        $rtCode = normalize_rt_code($pengajuan['rt'] ?? '');
        $ketuaRt = null;
        if ($rtCode !== '') {
            $ketuaRt = $db->table('pengurus')
                ->where('status', 'aktif')
                ->where('rt', $rtCode)
                ->like('jabatan', 'Ketua RT')
                ->orderBy('urutan', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();
            if (! $ketuaRt) {
                $ketuaRt = $db->table('pengurus')
                    ->where('status', 'aktif')
                    ->groupStart()
                        ->like('jabatan', 'RT ' . $rtCode)
                        ->orLike('jabatan', 'RT ' . (string) ((int) $rtCode))
                    ->groupEnd()
                    ->orderBy('urutan', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getRowArray();
            }
        }

        $viewData = [
            'pengajuan' => $pengajuan,
            'profil' => $profil,
            'ketuaNama' => $ketua['nama'] ?? 'Ketua RW 05',
            'ketuaJabatan' => $ketua['jabatan'] ?? 'Ketua RW 05',
            'rtKetuaNama' => $ketuaRt['nama'] ?? '',
            'rtKetuaJabatan' => $rtCode !== '' ? 'Ketua RT ' . $rtCode : 'Ketua RT',
            'logoUrl' => '',
            'ttdUrl' => $this->assetDataUriIfExists('ttd-ketua.png'),
            'stempelUrl' => $this->assetDataUriIfExists('stempel-rw.png'),
        ];

        if ($this->request->getGet('preview') === '1') {
            return view('public/surat_print', $viewData + ['forPdf' => false]);
        }

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml(view('public/surat_print', $viewData + ['forPdf' => true]), 'UTF-8');
        $dompdf->render();

        $fileName = $this->suratDownloadFilename($pengajuan);

        return $this->response->download($fileName, $dompdf->output(), true);
    }

    public function kegiatan(): string
    {
        return $this->renderPublic('public/kegiatan', ['currentPage' => 'kegiatan']);
    }

    public function pengurus(): string
    {
        return $this->renderPublic('public/pengurus', ['currentPage' => 'pengurus']);
    }

    public function aspirasi(): string
    {
        return $this->renderPublic('public/aspirasi', [
            'currentPage' => 'aspirasi',
            'success' => (bool) session()->getFlashdata('aspirasi_success'),
            'error' => session()->getFlashdata('aspirasi_error') ?: '',
        ]);
    }

    public function submitAspirasi()
    {
        $nama = trim((string) $this->request->getPost('nama'));
        $pesan = trim((string) $this->request->getPost('pesan'));

        if ($nama === '' || $pesan === '') {
            return redirect()->to(site_url('aspirasi'))
                ->withInput()
                ->with('aspirasi_error', 'Nama dan pesan wajib diisi.');
        }

        db_connect()->table('aspirasi')->insert([
            'nama' => $nama,
            'no_hp' => trim((string) $this->request->getPost('no_hp')),
            'rt' => trim((string) $this->request->getPost('rt')),
            'kategori' => trim((string) ($this->request->getPost('kategori') ?: 'Aspirasi')),
            'pesan' => $pesan,
        ]);

        return redirect()->to(site_url('aspirasi'))
            ->with('aspirasi_success', true);
    }

    private function renderPublic(string $view, array $data = []): string
    {
        return view($view, array_merge($this->sharedData(), $data));
    }

    private function sharedData(): array
    {
        $db = db_connect();

        $profil = $db->table('profil_rw')->where('id', 1)->get()->getRowArray() ?: [];
        $programs = $db->table('program_kerja')
            ->where('status', 'aktif')
            ->orderBy('nomor', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
        $layanan = $db->table('layanan')
            ->where('status', 'aktif')
            ->orderBy('urutan', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
        $kegiatan = $db->table('kegiatan')
            ->where('status', 'publish')
            ->orderBy('tanggal', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(8)
            ->get()
            ->getResultArray();
        $pengurus = $db->table('pengurus')
            ->where('status', 'aktif')
            ->orderBy('urutan', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return [
            'profil' => $profil,
            'programs' => $programs,
            'layanan' => $layanan,
            'kegiatan' => $kegiatan,
            'pengurus' => $pengurus,
            'strukturPengurusImage' => $this->pengurusStructureImageUrl(),
            'strukturPengurusDescription' => $this->pengurusStructureDescription(),
            'totalWarga' => (int) ($db->query("SELECT COALESCE(SUM(jumlah_anggota),0) AS total FROM warga WHERE status_tinggal <> 'pindah'")->getRowArray()['total'] ?? 0),
            'totalKK' => (int) ($db->query("SELECT COUNT(*) AS total FROM warga WHERE status_tinggal <> 'pindah'")->getRowArray()['total'] ?? 0),
            'totalAspirasi' => (int) ($db->query('SELECT COUNT(*) AS total FROM aspirasi')->getRowArray()['total'] ?? 0),
            'waLink' => wa_link($profil['whatsapp'] ?? ''),
            'instagramLink' => instagram_link($profil['instagram'] ?? ''),
            'siteName' => $profil['nama_rw'] ?? 'RW 05 Desa Citeureup',
            'desa' => $profil['desa'] ?? 'Citeureup',
            'adminEntryUrl' => session('admin_id') ? site_url('admin') : site_url('admin/login'),
            'adminEntryLabel' => session('admin_id') ? 'Dashboard Admin' : 'Login Admin',
        ];
    }

    private function pengurusStructureImageUrl(): string
    {
        $latestFile = null;

        foreach (self::PENGURUS_STRUCTURE_IMAGE_EXTENSIONS as $extension) {
            $path = FCPATH . 'assets' . DIRECTORY_SEPARATOR . self::PENGURUS_STRUCTURE_IMAGE . '.' . $extension;
            if (is_file($path)) {
                $modifiedAt = (int) filemtime($path);
                if ($latestFile === null || $modifiedAt > $latestFile['modifiedAt']) {
                    $latestFile = [
                        'fileName' => self::PENGURUS_STRUCTURE_IMAGE . '.' . $extension,
                        'modifiedAt' => $modifiedAt,
                    ];
                }
            }
        }

        return $latestFile
            ? base_url('assets/' . $latestFile['fileName']) . '?v=' . $latestFile['modifiedAt']
            : '';
    }

    private function pengurusStructureDescription(): string
    {
        $path = WRITEPATH . self::PENGURUS_STRUCTURE_DESCRIPTION;

        return is_file($path) ? trim((string) file_get_contents($path)) : '';
    }

    private function assetDataUriIfExists(string $fileName): string
    {
        $path = FCPATH . 'assets' . DIRECTORY_SEPARATOR . $fileName;

        if (! is_file($path)) {
            return '';
        }

        $mime = match (strtolower((string) pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
    }

    private function suratDownloadFilename(array $pengajuan): string
    {
        $jenisSurat = $this->filenamePart($pengajuan['jenis_surat'] ?? 'surat');
        $rt = normalize_rt_code($pengajuan['rt'] ?? '');
        $rtPart = $rt !== '' ? 'rt' . $rt : 'rw05';
        $namaPemohon = $this->filenamePart($pengajuan['nama'] ?? 'pemohon');

        return $jenisSurat . '-' . $rtPart . '-' . $namaPemohon . '.pdf';
    }

    private function filenamePart($value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
        $value = trim((string) $value, '-');

        return $value !== '' ? $value : 'rw05';
    }

    private function newPengajuanSuratCode(): string
    {
        $db = db_connect();

        for ($attempt = 0; $attempt < 8; $attempt++) {
            $suffix = strtoupper(bin2hex(random_bytes(3)));
            $code = 'RW05-' . date('Ymd') . '-' . $suffix;
            $exists = $db->table('pengajuan_surat')
                ->where('kode_pengajuan', $code)
                ->countAllResults() > 0;

            if (! $exists) {
                return $code;
            }
        }

        return 'RW05-' . date('YmdHis');
    }

    private function publicFinanceViewData($db, string $selectedStart, string $selectedEnd, string $selectedUnit = ''): array
    {
        $selectedUnit = keuangan_normalize_unit_filter($selectedUnit);
        $selectedRt = keuangan_unit_filter_rt($selectedUnit);
        $selectedScope = keuangan_unit_filter_scope($selectedUnit);
        $selectedMonth = substr($selectedStart, 0, 7);
        $periodLabel = keuangan_period_label($selectedStart, $selectedEnd);

        $rowsBuilder = $db->table('keuangan_transaksi')
            ->where('tanggal >=', $selectedStart)
            ->where('tanggal <=', $selectedEnd);

        if ($selectedScope !== '') {
            $rowsBuilder->where('lingkup', $selectedScope);
        } elseif ($selectedRt !== '') {
            $rowsBuilder->where('lingkup', 'rt')->where('rt', $selectedRt);
        }

        $rows = $rowsBuilder
            ->orderBy('tanggal', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $summary = keuangan_empty_summary();

        foreach ($rows as $row) {
            $amount = (int) ($row['nominal'] ?? 0);
            $scope = (string) ($row['lingkup'] ?? 'rw');
            keuangan_summary_add_transaction($summary, $scope, $row['jenis'] ?? 'pemasukan', $amount);
        }

        $summary = keuangan_finalize_summary($summary);

        $rwRows = array_values(array_filter($rows, static fn ($row) => ($row['lingkup'] ?? '') === 'rw'));
        $rtRows = array_values(array_filter($rows, static fn ($row) => ($row['lingkup'] ?? '') === 'rt'));
        $panitiaRows = array_values(array_filter($rows, static fn ($row) => ($row['lingkup'] ?? '') === 'panitia'));

        $rtBuilder = $db->table('keuangan_transaksi')
            ->select("rt,
                COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN nominal ELSE 0 END), 0) AS total_pemasukan,
                COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN nominal ELSE 0 END), 0) AS total_pengeluaran,
                COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN nominal ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN nominal ELSE 0 END), 0) AS saldo", false)
            ->where('lingkup', 'rt')
            ->where('tanggal >=', $selectedStart)
            ->where('tanggal <=', $selectedEnd);

        if ($selectedRt !== '') {
            $rtBuilder->where('rt', $selectedRt);
        } elseif ($selectedScope !== '') {
            $rtBuilder->where('rt', '__none__');
        }

        $rtSummaries = $rtBuilder
            ->groupBy('rt')
            ->orderBy('rt', 'ASC')
            ->get()
            ->getResultArray();

        $rwIncomeRows = $db->table('keuangan_transaksi')
            ->where('lingkup', 'rw')
            ->where('jenis', 'pemasukan')
            ->where('tanggal >=', $selectedStart)
            ->where('tanggal <=', $selectedEnd)
            ->orderBy('tanggal', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $rtOptions = keuangan_default_rt_options();
        foreach ($db->query("SELECT DISTINCT rt FROM warga WHERE rt IS NOT NULL AND rt <> '' ORDER BY CAST(rt AS UNSIGNED), rt")->getResultArray() as $row) {
            $normalized = normalize_rt_code($row['rt']);
            if ($normalized !== '') {
                $rtOptions[$normalized] = 'RT ' . $normalized;
            }
        }
        foreach ($rtSummaries as $row) {
            $normalized = normalize_rt_code($row['rt'] ?? '');
            if ($normalized !== '' && ! isset($rtOptions[$normalized])) {
                $rtOptions[$normalized] = 'RT ' . $normalized;
            }
        }
        ksort($rtOptions);
        $unitSummaries = keuangan_current_unit_summaries($summary, $rtSummaries, $rtOptions, $selectedUnit);

        return [
            'selectedMonth' => $selectedMonth,
            'selectedStart' => $selectedStart,
            'selectedEnd' => $selectedEnd,
            'selectedRt' => $selectedRt,
            'selectedUnit' => $selectedUnit,
            'selectedUnitLabel' => keuangan_unit_filter_label($selectedUnit),
            'monthLabel' => $periodLabel,
            'periodLabel' => $periodLabel,
            'summary' => $summary,
            'rtSummaries' => $rtSummaries,
            'unitSummaries' => $unitSummaries,
            'rwIncomeRows' => $rwIncomeRows,
            'panitiaRows' => $panitiaRows,
                'rwRows' => $rwRows,
                'rtRows' => $rtRows,
            'rows' => $rows,
            'rtOptions' => $rtOptions,
            'unitOptions' => keuangan_unit_options($rtOptions),
        ];
    }

    private function financePeriodBounds(string $selectedMonth): array
    {
        $monthStart = $selectedMonth . '-01';

        return [$monthStart, date('Y-m-t', strtotime($monthStart))];
    }

    private function financeMonthLabel(string $selectedMonth): string
    {
        $parts = explode(' ', format_date_id($selectedMonth . '-01'));

        return ($parts[1] ?? '') . ' ' . ($parts[2] ?? '');
    }

    private function downloadPublicFinancePdf(array $data)
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml(view('public/keuangan_print', $data), 'UTF-8');
        $dompdf->render();

        $fileName = 'laporan-keuangan-rw05-' . keuangan_period_slug($data['selectedStart'] ?? date('Y-m-01'), $data['selectedEnd'] ?? date('Y-m-t'));
        if (! empty($data['selectedUnit'])) {
            $fileName .= '-' . str_replace(':', '-', $data['selectedUnit']);
        }

        return $this->response->download($fileName . '.pdf', $dompdf->output(), true);
    }
}
