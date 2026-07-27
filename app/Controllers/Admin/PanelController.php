<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Dompdf\Dompdf;
use Dompdf\Options;
use Shuchkin\SimpleXLSXGen;

class PanelController extends BaseController
{
    private const PENGURUS_STRUCTURE_IMAGE = 'struktur-organisasi';
    private const PENGURUS_STRUCTURE_IMAGE_EXTENSIONS = ['webp', 'jpg', 'jpeg', 'png'];
    private const PENGURUS_STRUCTURE_DESCRIPTION = 'struktur-organisasi.txt';
    private const EDUKASI_UPLOAD_DIRECTORY = 'assets/uploads/edukasi';
    private const EDUKASI_MAX_UPLOAD_BYTES = 5 * 1024 * 1024;

    private function db()
    {
        return db_connect();
    }

    public function dashboard(): string
    {
        $db = $this->db();
        $suratTableReady = ensure_pengajuan_surat_table($db);
        $financeTableReady = ensure_keuangan_transaksi_table($db);

        $totalKegiatan = (int) ($db->query('SELECT COUNT(*) AS total FROM kegiatan')->getRowArray()['total'] ?? 0);
        $kegiatanPublish = (int) ($db->query("SELECT COUNT(*) AS total FROM kegiatan WHERE status='publish'")->getRowArray()['total'] ?? 0);
        $kegiatanDraft = (int) ($db->query("SELECT COUNT(*) AS total FROM kegiatan WHERE status='draft'")->getRowArray()['total'] ?? 0);
        $programAktif = (int) ($db->query("SELECT COUNT(*) AS total FROM program_kerja WHERE status='aktif'")->getRowArray()['total'] ?? 0);
        $layananAktif = (int) ($db->query("SELECT COUNT(*) AS total FROM layanan WHERE status='aktif'")->getRowArray()['total'] ?? 0);
        $pengurusAktif = (int) ($db->query("SELECT COUNT(*) AS total FROM pengurus WHERE status='aktif'")->getRowArray()['total'] ?? 0);
        $totalKeluarga = (int) ($db->query('SELECT COUNT(*) AS total FROM warga')->getRowArray()['total'] ?? 0);
        $totalWarga = (int) ($db->query('SELECT COALESCE(SUM(jumlah_anggota), 0) AS total FROM warga')->getRowArray()['total'] ?? 0);
        $aspirasiBaru = (int) ($db->query("SELECT COUNT(*) AS total FROM aspirasi WHERE status='baru'")->getRowArray()['total'] ?? 0);
        $aspirasiTotal = (int) ($db->query('SELECT COUNT(*) AS total FROM aspirasi')->getRowArray()['total'] ?? 0);
        $suratMenunggu = $suratTableReady ? (int) ($db->query("SELECT COUNT(*) AS total FROM pengajuan_surat WHERE status='menunggu'")->getRowArray()['total'] ?? 0) : 0;
        $suratTotal = $suratTableReady ? (int) ($db->query('SELECT COUNT(*) AS total FROM pengajuan_surat')->getRowArray()['total'] ?? 0) : 0;
        $financeMonth = date('Y-m');
        $financeSummary = [
            'income' => 0,
            'expense' => 0,
            'balance' => 0,
            'label' => $this->financeMonthLabel($financeMonth),
        ];

        if ($financeTableReady) {
            [$financeMonthStart, $financeMonthEnd] = $this->financePeriodBounds($financeMonth);
            $financeSummary['income'] = (int) ($db->query(
                "SELECT COALESCE(SUM(nominal), 0) AS total FROM keuangan_transaksi WHERE lingkup='rw' AND jenis='pemasukan' AND tanggal >= ? AND tanggal <= ?",
                [$financeMonthStart, $financeMonthEnd]
            )->getRowArray()['total'] ?? 0);
            $financeSummary['expense'] = (int) ($db->query(
                "SELECT COALESCE(SUM(nominal), 0) AS total FROM keuangan_transaksi WHERE lingkup='rw' AND jenis='pengeluaran' AND tanggal >= ? AND tanggal <= ?",
                [$financeMonthStart, $financeMonthEnd]
            )->getRowArray()['total'] ?? 0);
            $financeSummary['balance'] = $financeSummary['income'] - $financeSummary['expense'];
        }

        $aspirasiStatus = ['baru' => 0, 'diproses' => 0, 'selesai' => 0];
        foreach ($db->query('SELECT status, COUNT(*) AS total FROM aspirasi GROUP BY status')->getResultArray() as $row) {
            $aspirasiStatus[$row['status']] = (int) $row['total'];
        }

        $wargaByRt = $db->query(
            'SELECT rt, COUNT(*) AS total_kk, COALESCE(SUM(jumlah_anggota), 0) AS total_warga
             FROM warga GROUP BY rt ORDER BY CAST(rt AS UNSIGNED), rt LIMIT 8'
        )->getResultArray();
        $wargaSocialSummary = [
            'kurangMampu' => (int) ($db->query("SELECT COUNT(*) AS total FROM warga WHERE kategori_kesejahteraan IN ('kurang_mampu', 'sangat_kurang_mampu')")->getRowArray()['total'] ?? 0),
            'penerimaBantuan' => (int) ($db->query("SELECT COUNT(*) AS total FROM warga WHERE penerima_bantuan='ya'")->getRowArray()['total'] ?? 0),
            'topRt' => '',
            'topRtNeedCount' => 0,
        ];
        $topWargaAttention = $db->query(
            "SELECT rt, kurang_mampu, penerima_bantuan, (kurang_mampu + penerima_bantuan) AS total_perlu_fokus
             FROM (
                 SELECT rt,
                        SUM(CASE WHEN kategori_kesejahteraan IN ('kurang_mampu', 'sangat_kurang_mampu') THEN 1 ELSE 0 END) AS kurang_mampu,
                        SUM(CASE WHEN penerima_bantuan='ya' THEN 1 ELSE 0 END) AS penerima_bantuan
                 FROM warga
                 GROUP BY rt
             ) AS rt_summary
             WHERE kurang_mampu > 0 OR penerima_bantuan > 0
             ORDER BY total_perlu_fokus DESC, CAST(rt AS UNSIGNED), rt
             LIMIT 1"
        )->getRowArray();
        if ($topWargaAttention) {
            $wargaSocialSummary['topRt'] = normalize_rt_code($topWargaAttention['rt'] ?? '');
            $wargaSocialSummary['topRtNeedCount'] = (int) ($topWargaAttention['kurang_mampu'] ?? 0) + (int) ($topWargaAttention['penerima_bantuan'] ?? 0);
        }

        $latestAspirasi = $db->table('aspirasi')->orderBy('created_at', 'DESC')->limit(5)->get()->getResultArray();
        $latestKegiatan = $db->table('kegiatan')->orderBy('tanggal', 'DESC')->orderBy('id', 'DESC')->limit(4)->get()->getResultArray();

        $workItems = [];
        if ($suratMenunggu > 0) {
            $workItems[] = ['label' => 'Tinjau pengajuan surat', 'detail' => $suratMenunggu . ' pengajuan surat menunggu verifikasi', 'href' => site_url('admin/pengajuan-surat')];
        }
        if ($aspirasiBaru > 0) {
            $workItems[] = ['label' => 'Tinjau aspirasi baru', 'detail' => $aspirasiBaru . ' aspirasi menunggu respon pengurus', 'href' => site_url('admin/aspirasi')];
        }
        if ($kegiatanDraft > 0) {
            $workItems[] = ['label' => 'Cek draft kegiatan', 'detail' => $kegiatanDraft . ' kegiatan belum tampil di website warga', 'href' => site_url('admin/kegiatan')];
        }
        if ($totalKeluarga === 0) {
            $workItems[] = ['label' => 'Mulai pendataan warga', 'detail' => 'Data keluarga belum terisi', 'href' => site_url('admin/warga')];
        }
        if ($pengurusAktif < 3) {
            $workItems[] = ['label' => 'Lengkapi struktur pengurus', 'detail' => $pengurusAktif . ' pengurus aktif tercatat', 'href' => site_url('admin/pengurus')];
        }
        if ($workItems === []) {
            $workItems[] = ['label' => 'Data utama terkendali', 'detail' => 'Aspirasi, kegiatan, warga, dan pengurus sudah memiliki data aktif', 'href' => site_url('admin/aspirasi')];
        }

        return view('admin/dashboard', [
            'currentPage' => 'dashboard',
            'stats' => [
                ['label' => 'Aspirasi Baru', 'value' => $aspirasiBaru, 'meta' => $aspirasiTotal . ' total aspirasi', 'href' => site_url('admin/aspirasi'), 'tone' => 'attention'],
                ['label' => 'Pengajuan Surat', 'value' => $suratMenunggu, 'meta' => $suratTotal . ' total pengajuan', 'href' => site_url('admin/pengajuan-surat'), 'tone' => 'letter'],
                ['label' => 'Data Warga', 'value' => $totalKeluarga, 'meta' => $totalWarga . ' jiwa tercatat', 'href' => site_url('admin/warga'), 'tone' => 'people'],
                ['label' => 'Saldo RW', 'value' => fmt_currency($financeSummary['balance']), 'meta' => 'Masuk ' . fmt_currency($financeSummary['income']) . ' | Keluar ' . fmt_currency($financeSummary['expense']), 'href' => site_url('admin/keuangan'), 'tone' => 'program'],
                ['label' => 'Kegiatan', 'value' => $totalKegiatan, 'meta' => $kegiatanPublish . ' publish, ' . $kegiatanDraft . ' draft', 'href' => site_url('admin/kegiatan'), 'tone' => 'agenda'],
                ['label' => 'Program Aktif', 'value' => $programAktif, 'meta' => $layananAktif . ' layanan aktif', 'href' => site_url('admin/program'), 'tone' => 'program'],
            ],
            'aspirasiBaru' => $aspirasiBaru,
            'aspirasiTotal' => $aspirasiTotal,
            'suratMenunggu' => $suratMenunggu,
            'suratTotal' => $suratTotal,
            'aspirasiStatus' => $aspirasiStatus,
            'wargaByRt' => $wargaByRt,
            'wargaSocialSummary' => $wargaSocialSummary,
            'latestAspirasi' => $latestAspirasi,
            'latestKegiatan' => $latestKegiatan,
            'workItems' => $workItems,
            'financeSummary' => $financeSummary,
        ]);
    }

    public function profil()
    {
        $db = $this->db();

        if ($this->request->getMethod() === 'POST') {
            $db->table('profil_rw')->where('id', 1)->update([
                'nama_rw' => trim((string) $this->request->getPost('nama_rw')),
                'desa' => trim((string) $this->request->getPost('desa')),
                'kecamatan' => trim((string) $this->request->getPost('kecamatan')),
                'kabupaten' => trim((string) $this->request->getPost('kabupaten')),
                'alamat' => trim((string) $this->request->getPost('alamat')),
                'instagram' => trim((string) $this->request->getPost('instagram')),
                'whatsapp' => trim((string) $this->request->getPost('whatsapp')),
                'email' => trim((string) $this->request->getPost('email')),
                'tentang_rw' => trim((string) $this->request->getPost('tentang_rw')),
                'tagline' => trim((string) $this->request->getPost('tagline')),
                'sambutan_ketua' => trim((string) $this->request->getPost('sambutan_ketua')),
                'visi' => trim((string) $this->request->getPost('visi')),
                'misi' => trim((string) $this->request->getPost('misi')),
            ]);

            return redirect()->to(site_url('admin/profil'))->with('success', 'Profil berhasil disimpan.');
        }

        return view('admin/profil', [
            'currentPage' => 'profil',
            'profil' => $db->table('profil_rw')->where('id', 1)->get()->getRowArray() ?: [],
            'success' => session()->getFlashdata('success') ?: '',
        ]);
    }

    public function program()
    {
        return $this->crud('program', [
            'title' => 'Program Kerja',
            'table' => 'program_kerja',
            'order' => ['nomor' => 'ASC', 'id' => 'ASC'],
            'fields' => [
                'nomor' => ['label' => 'Nomor', 'type' => 'number', 'default' => 0],
                'judul' => ['label' => 'Judul', 'type' => 'text', 'required' => true],
                'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea', 'required' => true, 'full' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'], 'default' => 'aktif'],
            ],
            'columns' => ['nomor' => 'Nomor', 'judul' => 'Judul', 'deskripsi' => 'Deskripsi', 'status' => 'Status'],
        ]);
    }

    public function kegiatan()
    {
        return $this->crud('kegiatan', [
            'title' => 'Kegiatan & Pengumuman',
            'table' => 'kegiatan',
            'order' => ['tanggal' => 'DESC', 'id' => 'DESC'],
            'fields' => [
                'judul' => ['label' => 'Judul', 'type' => 'text', 'required' => true],
                'kategori' => ['label' => 'Kategori', 'type' => 'text', 'default' => 'Pengumuman'],
                'tanggal' => ['label' => 'Tanggal', 'type' => 'date', 'default' => date('Y-m-d'), 'required' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['publish' => 'Publish', 'draft' => 'Draft'], 'default' => 'publish'],
                'isi' => ['label' => 'Isi', 'type' => 'textarea', 'required' => true, 'full' => true],
            ],
            'columns' => ['judul' => 'Judul', 'kategori' => 'Kategori', 'tanggal' => 'Tanggal', 'status' => 'Status'],
        ]);
    }

    public function layanan()
    {
        return $this->crud('layanan', [
            'title' => 'Layanan Warga',
            'table' => 'layanan',
            'order' => ['urutan' => 'ASC', 'id' => 'ASC'],
            'fields' => [
                'urutan' => ['label' => 'Urutan', 'type' => 'number', 'default' => 0],
                'icon' => ['label' => 'Icon', 'type' => 'text', 'default' => 'RW'],
                'nama' => ['label' => 'Nama Layanan', 'type' => 'text', 'required' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'], 'default' => 'aktif'],
                'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea', 'required' => true, 'full' => true],
            ],
            'columns' => ['urutan' => 'Urutan', 'icon' => 'Icon', 'nama' => 'Nama Layanan', 'status' => 'Status'],
        ]);
    }

    public function edukasi()
    {
        $db = $this->db();
        $tableReady = ensure_edukasi_materi_table($db);
        $id = (int) $this->request->getGet('id');

        if (! $tableReady) {
            return view('admin/edukasi', [
                'currentPage' => 'edukasi',
                'tableReady' => false,
                'rows' => [],
                'edit' => null,
                'filters' => ['kategori' => '', 'jenis' => '', 'status' => ''],
                'categoryOptions' => edukasi_category_options(),
                'typeOptions' => edukasi_type_options(),
                'statusOptions' => edukasi_status_options(),
                'summary' => ['total' => 0, 'publish' => 0, 'draft' => 0, 'poster' => 0, 'video' => 0, 'artikel' => 0],
                'error' => 'Penyimpanan materi edukasi belum siap. Coba muat ulang atau hubungi pengelola hosting.',
                'success' => '',
            ]);
        }

        if ($this->request->getMethod() === 'POST') {
            $postedId = (int) $this->request->getPost('id');
            $existing = $postedId > 0
                ? $db->table('edukasi_materi')->where('id', $postedId)->get()->getRowArray()
                : null;
            $redirectUrl = $postedId > 0
                ? site_url('admin/edukasi?action=edit&id=' . $postedId)
                : site_url('admin/edukasi');

            if ($postedId > 0 && ! $existing) {
                return redirect()->to(site_url('admin/edukasi'))->with('error', 'Materi yang akan diedit tidak ditemukan.');
            }

            $kategori = trim((string) $this->request->getPost('kategori'));
            $jenis = trim((string) $this->request->getPost('jenis'));
            $judul = trim((string) $this->request->getPost('judul'));
            $ringkasan = trim((string) $this->request->getPost('ringkasan'));
            $penulis = trim((string) $this->request->getPost('penulis'));
            $institusi = trim((string) $this->request->getPost('institusi'));
            $tahun = trim((string) $this->request->getPost('tahun'));
            $tautan = trim((string) $this->request->getPost('tautan'));
            $status = trim((string) $this->request->getPost('status'));
            $urutan = max(0, min(9999, (int) $this->request->getPost('urutan')));

            $validationError = '';
            if (! array_key_exists($kategori, edukasi_category_options())) {
                $validationError = 'Pilih kategori edukasi yang tersedia.';
            } elseif (! array_key_exists($jenis, edukasi_type_options())) {
                $validationError = 'Pilih jenis materi: Poster, Video, atau Artikel.';
            } elseif ($judul === '' || strlen($judul) > 180) {
                $validationError = 'Judul wajib diisi dan maksimal 180 karakter.';
            } elseif ($penulis === '' || strlen($penulis) > 160) {
                $validationError = 'Nama dosen/penulis wajib diisi dan maksimal 160 karakter.';
            } elseif (strlen($institusi) > 160) {
                $validationError = 'Nama institusi maksimal 160 karakter.';
            } elseif (strlen($ringkasan) > 1000) {
                $validationError = 'Ringkasan maksimal 1000 karakter.';
            } elseif ($tahun !== '' && ! preg_match('/^(19|20)\d{2}$/', $tahun)) {
                $validationError = 'Tahun harus berupa empat angka, misalnya 2026.';
            } elseif (! array_key_exists($status, edukasi_status_options())) {
                $validationError = 'Pilih status Draft atau Tayang.';
            } elseif ($tautan !== '' && ! $this->isAllowedEducationUrl($tautan)) {
                $validationError = 'Tautan harus berupa alamat http atau https yang valid.';
            }

            if ($validationError !== '') {
                return redirect()->to($redirectUrl)->withInput()->with('error', $validationError);
            }

            $currentFilePath = $existing && (string) ($existing['jenis'] ?? '') === $jenis
                ? trim((string) ($existing['file_path'] ?? ''))
                : '';
            $filePath = $currentFilePath;
            $newFilePath = '';
            $upload = $this->request->getFile('materi_file');
            $hasUpload = $upload && $upload->getError() !== UPLOAD_ERR_NO_FILE;

            if ($jenis === 'video' && $hasUpload) {
                return redirect()->to($redirectUrl)->withInput()->with('error', 'Materi video menggunakan tautan, bukan upload file.');
            }

            if ($hasUpload) {
                if (! $upload->isValid()) {
                    return redirect()->to($redirectUrl)->withInput()->with('error', 'File gagal diupload. Silakan pilih ulang file.');
                }
                if ($upload->getSize() > self::EDUKASI_MAX_UPLOAD_BYTES) {
                    return redirect()->to($redirectUrl)->withInput()->with('error', 'Ukuran file maksimal 5 MB.');
                }

                $extension = strtolower($upload->getClientExtension());
                $mimeType = strtolower((string) $upload->getMimeType());
                $allowed = $jenis === 'poster'
                    ? [
                        'jpg' => ['image/jpeg'],
                        'jpeg' => ['image/jpeg'],
                        'png' => ['image/png'],
                        'webp' => ['image/webp'],
                    ]
                    : ['pdf' => ['application/pdf', 'application/x-pdf']];

                if (! isset($allowed[$extension]) || ! in_array($mimeType, $allowed[$extension], true)) {
                    $message = $jenis === 'poster'
                        ? 'Poster harus berupa JPG, PNG, atau WebP.'
                        : 'File artikel harus berupa PDF.';

                    return redirect()->to($redirectUrl)->withInput()->with('error', $message);
                }

                $targetDirectory = rtrim(FCPATH, DIRECTORY_SEPARATOR)
                    . DIRECTORY_SEPARATOR
                    . str_replace('/', DIRECTORY_SEPARATOR, self::EDUKASI_UPLOAD_DIRECTORY);
                if (! is_dir($targetDirectory) && ! mkdir($targetDirectory, 0755, true) && ! is_dir($targetDirectory)) {
                    return redirect()->to($redirectUrl)->withInput()->with('error', 'Folder upload materi tidak dapat dibuat.');
                }

                $targetExtension = $extension === 'jpeg' ? 'jpg' : $extension;
                $fileName = date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $targetExtension;
                try {
                    $upload->move($targetDirectory, $fileName);
                } catch (\Throwable $exception) {
                    log_message('error', 'Gagal memindahkan file materi edukasi: ' . $exception->getMessage());

                    return redirect()->to($redirectUrl)->withInput()->with('error', 'File belum dapat disimpan. Silakan coba kembali.');
                }
                $newFilePath = self::EDUKASI_UPLOAD_DIRECTORY . '/' . $fileName;
                $filePath = $newFilePath;
            }

            if ($jenis === 'poster') {
                $tautan = '';
                if ($filePath === '') {
                    return redirect()->to($redirectUrl)->withInput()->with('error', 'Upload file poster terlebih dahulu.');
                }
            } elseif ($jenis === 'video') {
                $filePath = '';
                if ($tautan === '') {
                    return redirect()->to($redirectUrl)->withInput()->with('error', 'Masukkan tautan video terlebih dahulu.');
                }
            } elseif ($tautan === '' && $filePath === '') {
                return redirect()->to($redirectUrl)->withInput()->with('error', 'Artikel memerlukan tautan atau file PDF.');
            }

            $data = [
                'kategori' => $kategori,
                'jenis' => $jenis,
                'judul' => $judul,
                'ringkasan' => $ringkasan,
                'penulis' => $penulis,
                'institusi' => $institusi,
                'tahun' => $tahun,
                'tautan' => $tautan,
                'file_path' => $filePath,
                'urutan' => $urutan,
                'status' => $status,
            ];

            try {
                if ($postedId > 0) {
                    $db->table('edukasi_materi')->where('id', $postedId)->update($data);
                } else {
                    $db->table('edukasi_materi')->insert($data);
                }
            } catch (\Throwable $exception) {
                if ($newFilePath !== '') {
                    $this->removeManagedEducationFile($newFilePath);
                }
                log_message('error', 'Gagal menyimpan materi edukasi: ' . $exception->getMessage());

                return redirect()->to($redirectUrl)->withInput()->with('error', 'Materi belum dapat disimpan. Silakan coba kembali.');
            }

            $oldFilePath = trim((string) ($existing['file_path'] ?? ''));
            if ($oldFilePath !== '' && $oldFilePath !== $filePath) {
                $this->removeManagedEducationFile($oldFilePath);
            }

            return redirect()->to(site_url('admin/edukasi'))
                ->with('success', $postedId > 0 ? 'Materi edukasi berhasil diperbarui.' : 'Materi edukasi berhasil ditambahkan.');
        }

        $edit = null;
        if ($this->request->getGet('action') === 'edit' && $id > 0) {
            $edit = $db->table('edukasi_materi')->where('id', $id)->get()->getRowArray();
        }

        $filters = [
            'kategori' => trim((string) $this->request->getGet('kategori')),
            'jenis' => trim((string) $this->request->getGet('jenis')),
            'status' => trim((string) $this->request->getGet('status')),
        ];
        if (! array_key_exists($filters['kategori'], edukasi_category_options())) {
            $filters['kategori'] = '';
        }
        if (! array_key_exists($filters['jenis'], edukasi_type_options())) {
            $filters['jenis'] = '';
        }
        if (! array_key_exists($filters['status'], edukasi_status_options())) {
            $filters['status'] = '';
        }

        $rowsBuilder = $db->table('edukasi_materi');
        foreach ($filters as $field => $value) {
            if ($value !== '') {
                $rowsBuilder->where($field, $value);
            }
        }
        $rows = $rowsBuilder
            ->orderBy('kategori', 'ASC')
            ->orderBy('urutan', 'ASC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $summary = ['total' => 0, 'publish' => 0, 'draft' => 0, 'poster' => 0, 'video' => 0, 'artikel' => 0];
        foreach ($db->table('edukasi_materi')->select('jenis, status')->get()->getResultArray() as $row) {
            $summary['total']++;
            if (isset($summary[$row['status']])) {
                $summary[$row['status']]++;
            }
            if (isset($summary[$row['jenis']])) {
                $summary[$row['jenis']]++;
            }
        }

        return view('admin/edukasi', [
            'currentPage' => 'edukasi',
            'tableReady' => true,
            'rows' => $rows,
            'edit' => $edit,
            'filters' => $filters,
            'categoryOptions' => edukasi_category_options(),
            'typeOptions' => edukasi_type_options(),
            'statusOptions' => edukasi_status_options(),
            'summary' => $summary,
            'error' => session()->getFlashdata('error') ?: '',
            'success' => session()->getFlashdata('success') ?: '',
        ]);
    }

    public function deleteEdukasi(int $id)
    {
        $db = $this->db();
        if (! ensure_edukasi_materi_table($db)) {
            return redirect()->to(site_url('admin/edukasi'))->with('error', 'Penyimpanan materi edukasi belum siap.');
        }

        $row = $db->table('edukasi_materi')->where('id', $id)->get()->getRowArray();
        if (! $row) {
            return redirect()->to(site_url('admin/edukasi'))->with('error', 'Materi edukasi tidak ditemukan.');
        }

        $db->table('edukasi_materi')->where('id', $id)->delete();
        $this->removeManagedEducationFile((string) ($row['file_path'] ?? ''));

        return redirect()->to(site_url('admin/edukasi'))->with('success', 'Materi edukasi berhasil dihapus.');
    }

    public function pengajuanSurat()
    {
        $db = $this->db();
        $tableReady = ensure_pengajuan_surat_table($db);
        $id = (int) $this->request->getGet('id');

        if (! $tableReady) {
            return view('admin/pengajuan_surat', [
                'currentPage' => 'pengajuan-surat',
                'rows' => [],
                'statusCounts' => array_fill_keys(array_keys(surat_status_options()), 0),
                'statusOptions' => surat_status_options(),
                'error' => 'Tabel pengajuan_surat belum bisa dibuat. Cek izin database hosting.',
                'success' => '',
            ]);
        }

        if ($this->request->getMethod() === 'POST') {
            $status = (string) $this->request->getPost('status');
            if (! array_key_exists($status, surat_status_options())) {
                $status = 'menunggu';
            }

            $db->table('pengajuan_surat')->where('id', (int) $this->request->getPost('id'))->update([
                'status' => $status,
                'nomor_surat' => trim((string) $this->request->getPost('nomor_surat')),
                'catatan_admin' => trim((string) $this->request->getPost('catatan_admin')),
            ]);

            return redirect()->to(site_url('admin/pengajuan-surat'))->with('success', 'Status pengajuan surat berhasil diperbarui.');
        }

        if ($this->request->getGet('action') === 'delete' && $id > 0) {
            $db->table('pengajuan_surat')->where('id', $id)->delete();

            return redirect()->to(site_url('admin/pengajuan-surat'))->with('success', 'Pengajuan surat berhasil dihapus.');
        }

        $statusCounts = array_fill_keys(array_keys(surat_status_options()), 0);
        foreach ($db->query('SELECT status, COUNT(*) AS total FROM pengajuan_surat GROUP BY status')->getResultArray() as $row) {
            $statusCounts[$row['status']] = (int) $row['total'];
        }

        return view('admin/pengajuan_surat', [
            'currentPage' => 'pengajuan-surat',
            'rows' => $db->table('pengajuan_surat')->orderBy('created_at', 'DESC')->get()->getResultArray(),
            'statusCounts' => $statusCounts,
            'statusOptions' => surat_status_options(),
            'error' => session()->getFlashdata('error') ?: '',
            'success' => session()->getFlashdata('success') ?: '',
        ]);
    }

    public function pengurus()
    {
        return $this->crud('pengurus', [
            'title' => 'Pengurus RW',
            'table' => 'pengurus',
            'order' => ['urutan' => 'ASC', 'id' => 'ASC'],
            'description' => 'Isi struktur organisasi dari Ketua RW, Sekretaris, Bendahara, seksi-seksi, hingga Ketua RT. Bisa ditambah satu per satu atau upload CSV dari template.',
            'importType' => 'pengurus',
            'imageUpload' => [
                'title' => 'Gambar Struktur Organisasi',
                'description' => 'Upload gambar bagan struktur organisasi bila sudah dibuat dari Canva, PowerPoint, atau desain lain. Format yang didukung: JPG, PNG, atau WebP maksimal 2 MB.',
                'imageUrl' => $this->pengurusStructureImageUrl(),
                'uploadUrl' => site_url('admin/pengurus/struktur-gambar'),
                'deleteUrl' => site_url('admin/pengurus/struktur-gambar/delete'),
                'descriptionValue' => $this->pengurusStructureDescription(),
                'descriptionSaveUrl' => site_url('admin/pengurus/struktur-penjelasan'),
            ],
            'fields' => [
                'urutan' => ['label' => 'Urutan', 'type' => 'number', 'default' => 0],
                'nama' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
                'jabatan' => ['label' => 'Jabatan', 'type' => 'text', 'required' => true],
                'rt' => ['label' => 'RT', 'type' => 'text'],
                'no_hp' => ['label' => 'No HP', 'type' => 'text'],
                'tugas' => ['label' => 'Tugas Utama', 'type' => 'textarea', 'full' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'], 'default' => 'aktif'],
            ],
            'columns' => ['urutan' => 'Urutan', 'nama' => 'Nama', 'jabatan' => 'Jabatan', 'rt' => 'RT', 'status' => 'Status', 'tugas' => 'Tugas Utama'],
        ]);
    }

    public function uploadPengurusStructureImage()
    {
        $file = $this->request->getFile('struktur_gambar');

        if (! $file || ! $file->isValid()) {
            return redirect()->to(site_url('admin/pengurus'))->with('error', 'Pilih file gambar struktur organisasi terlebih dahulu.');
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return redirect()->to(site_url('admin/pengurus'))->with('error', 'Ukuran gambar maksimal 2 MB.');
        }

        $extension = strtolower($file->getClientExtension());
        if (! in_array($extension, self::PENGURUS_STRUCTURE_IMAGE_EXTENSIONS, true)) {
            return redirect()->to(site_url('admin/pengurus'))->with('error', 'Format gambar harus JPG, PNG, atau WebP.');
        }

        $targetDirectory = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'assets';
        if (! is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        $targetExtension = $extension === 'jpeg' ? 'jpg' : $extension;
        $this->removePengurusStructureImages();
        $file->move($targetDirectory, self::PENGURUS_STRUCTURE_IMAGE . '.' . $targetExtension, true);

        return redirect()->to(site_url('admin/pengurus'))->with('success', 'Gambar struktur organisasi berhasil diupload.');
    }

    public function deletePengurusStructureImage()
    {
        $this->removePengurusStructureImages();

        return redirect()->to(site_url('admin/pengurus'))->with('success', 'Gambar struktur organisasi berhasil dihapus.');
    }

    public function savePengurusStructureDescription()
    {
        $description = trim((string) $this->request->getPost('struktur_penjelasan'));

        if (strlen($description) > 3000) {
            return redirect()->to(site_url('admin/pengurus'))->with('error', 'Penjelasan struktur organisasi maksimal 3000 karakter.');
        }

        $path = $this->pengurusStructureDescriptionPath();
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        if ($description === '') {
            if (is_file($path)) {
                unlink($path);
            }

            return redirect()->to(site_url('admin/pengurus'))->with('success', 'Penjelasan struktur organisasi dikosongkan.');
        }

        file_put_contents($path, $description);

        return redirect()->to(site_url('admin/pengurus'))->with('success', 'Penjelasan struktur organisasi berhasil disimpan.');
    }

    public function warga()
    {
        $db = $this->db();
        $tableReady = ensure_warga_table($db);
        $id = (int) $this->request->getGet('id');
        $filters = [
            'rt' => normalize_rt_code($this->request->getGet('rt')),
            'status_tinggal' => trim((string) $this->request->getGet('status_tinggal')),
            'kategori_kesejahteraan' => trim((string) $this->request->getGet('kategori_kesejahteraan')),
            'penerima_bantuan' => trim((string) $this->request->getGet('penerima_bantuan')),
        ];
        $statusOptions = warga_status_tinggal_options();
        $kesejahteraanOptions = warga_kesejahteraan_options();
        $bantuanOptions = warga_bantuan_options();

        if (! isset($statusOptions[$filters['status_tinggal']])) {
            $filters['status_tinggal'] = '';
        }
        if (! isset($kesejahteraanOptions[$filters['kategori_kesejahteraan']])) {
            $filters['kategori_kesejahteraan'] = '';
        }
        if (! isset($bantuanOptions[$filters['penerima_bantuan']])) {
            $filters['penerima_bantuan'] = '';
        }

        if (! $tableReady) {
            return view('admin/warga', [
                'currentPage' => 'warga',
                'rows' => [],
                'edit' => null,
                'filters' => $filters,
                'rtOptions' => [],
                'statusOptions' => $statusOptions,
                'kesejahteraanOptions' => $kesejahteraanOptions,
                'bantuanOptions' => $bantuanOptions,
                'summary' => ['totalKk' => 0, 'totalWarga' => 0, 'kurangMampu' => 0, 'penerimaBantuan' => 0],
                'bantuanBreakdown' => [],
                'formAction' => $this->wargaUrl($filters),
                'exportUrl' => $this->wargaUrl($filters, ['export' => 'csv']),
                'xlsxUrl' => $this->wargaUrl($filters, ['export' => 'xlsx']),
                'pdfExportUrl' => $this->wargaUrl($filters, ['export' => 'pdf']),
                'cetakUrl' => $this->wargaUrl($filters, ['export' => 'cetak']),
                'allExportUrl' => $this->wargaUrl([], ['export' => 'csv']),
                'allXlsxUrl' => $this->wargaUrl([], ['export' => 'xlsx']),
                'allPdfExportUrl' => $this->wargaUrl([], ['export' => 'pdf']),
                'allCetakUrl' => $this->wargaUrl([], ['export' => 'cetak']),
                'templateUrl' => site_url('admin/import/template/warga'),
                'importUrl' => site_url('admin/import?type=warga'),
                'error' => 'Tabel warga belum bisa dibuat. Cek izin database hosting.',
                'success' => '',
            ]);
        }

        if ($this->request->getMethod() === 'POST') {
            $postedId = (int) $this->request->getPost('id');
            $redirectExtra = $postedId > 0 ? ['action' => 'edit', 'id' => $postedId] : [];
            $nama = trim((string) $this->request->getPost('nama_kepala_keluarga'));
            $rt = normalize_rt_code($this->request->getPost('rt'));
            $alamat = trim((string) $this->request->getPost('alamat'));
            $jumlahAnggota = max(1, (int) preg_replace('/\D+/', '', (string) $this->request->getPost('jumlah_anggota')));
            $noHp = trim((string) $this->request->getPost('no_hp'));
            $pekerjaan = trim((string) $this->request->getPost('pekerjaan_kepala_keluarga'));
            $statusTinggal = (string) $this->request->getPost('status_tinggal');
            $kategoriKesejahteraan = (string) $this->request->getPost('kategori_kesejahteraan');
            $penerimaBantuan = (string) $this->request->getPost('penerima_bantuan');
            $jenisBantuan = trim((string) $this->request->getPost('jenis_bantuan'));
            $kondisiKhusus = trim((string) $this->request->getPost('kondisi_khusus'));
            $keterangan = trim((string) $this->request->getPost('keterangan'));

            if ($nama === '' || $rt === '') {
                return redirect()->to($this->wargaUrl($filters, $redirectExtra))
                    ->withInput()
                    ->with('error', 'Nama kepala keluarga dan RT wajib diisi.');
            }

            if (! isset($statusOptions[$statusTinggal])) {
                $statusTinggal = 'tetap';
            }
            if (! isset($kesejahteraanOptions[$kategoriKesejahteraan])) {
                $kategoriKesejahteraan = 'umum';
            }
            if (! isset($bantuanOptions[$penerimaBantuan])) {
                $penerimaBantuan = 'tidak';
            }
            if ($penerimaBantuan === 'ya' && $jenisBantuan === '') {
                return redirect()->to($this->wargaUrl($filters, $redirectExtra))
                    ->withInput()
                    ->with('error', 'Jenis bantuan wajib diisi jika keluarga penerima bantuan.');
            }

            $data = [
                'nama_kepala_keluarga' => substr($nama, 0, 120),
                'rt' => substr($rt, 0, 20),
                'alamat' => substr($alamat, 0, 255),
                'jumlah_anggota' => $jumlahAnggota,
                'no_hp' => substr($noHp, 0, 40),
                'pekerjaan_kepala_keluarga' => substr($pekerjaan, 0, 120),
                'status_tinggal' => $statusTinggal,
                'kategori_kesejahteraan' => $kategoriKesejahteraan,
                'penerima_bantuan' => $penerimaBantuan,
                'jenis_bantuan' => $penerimaBantuan === 'ya' ? substr($jenisBantuan, 0, 255) : '',
                'kondisi_khusus' => $kondisiKhusus,
                'keterangan' => $keterangan,
            ];

            if ($postedId > 0) {
                $db->table('warga')->where('id', $postedId)->update($data);
                $message = 'Data warga berhasil diperbarui.';
            } else {
                $db->table('warga')->insert($data);
                $message = 'Data warga berhasil ditambahkan.';
            }

            return redirect()->to($this->wargaUrl($filters))->with('success', $message);
        }

        if ($this->request->getGet('action') === 'delete' && $id > 0) {
            $db->table('warga')->where('id', $id)->delete();

            return redirect()->to($this->wargaUrl($filters))->with('success', 'Data warga berhasil dihapus.');
        }

        $edit = null;
        if ($this->request->getGet('action') === 'edit' && $id > 0) {
            $edit = $db->table('warga')->where('id', $id)->get()->getRowArray();
        }

        $rows = $this->wargaRows($db, $filters);
        $summary = $this->wargaSummary($rows);
        $bantuanBreakdown = $this->wargaBantuanBreakdown($rows);
        if ($this->request->getGet('export') === 'csv') {
            return $this->downloadWargaCsv($rows, $filters);
        }

        if ($this->request->getGet('export') === 'xlsx') {
            return $this->downloadWargaExcel($rows, $filters);
        }

        if ($this->request->getGet('export') === 'pdf') {
            return $this->downloadWargaPdf([
                'rows' => $rows,
                'summary' => $summary,
                'filters' => $filters,
                'statusOptions' => $statusOptions,
                'kesejahteraanOptions' => $kesejahteraanOptions,
                'bantuanOptions' => $bantuanOptions,
                'rtSummaries' => $this->wargaSummaryByRt($rows),
                'showDetailedRows' => true,
            ]);
        }

        if ($this->request->getGet('export') === 'cetak') {
            return $this->response->setBody(view('admin/warga_print', [
                'rows' => $rows,
                'summary' => $summary,
                'filters' => $filters,
                'statusOptions' => $statusOptions,
                'kesejahteraanOptions' => $kesejahteraanOptions,
                'bantuanOptions' => $bantuanOptions,
                'rtSummaries' => $this->wargaSummaryByRt($rows),
                'showDetailedRows' => true,
                'autoPrint' => false,
                'isPreview' => true,
                'pdfUrl' => $this->wargaUrl($filters, ['export' => 'pdf']),
                'xlsxUrl' => $this->wargaUrl($filters, ['export' => 'xlsx']),
            ]))->setHeader('Content-Type', 'text/html; charset=UTF-8');
        }

        $rtOptions = [];
        foreach ($db->query("SELECT DISTINCT rt FROM warga WHERE rt IS NOT NULL AND rt <> '' ORDER BY CAST(rt AS UNSIGNED), rt")->getResultArray() as $row) {
            $normalized = normalize_rt_code($row['rt']);
            if ($normalized !== '') {
                $rtOptions[$normalized] = 'RT ' . $normalized;
            }
        }

        return view('admin/warga', [
            'currentPage' => 'warga',
            'rows' => $rows,
            'edit' => $edit,
            'filters' => $filters,
            'rtOptions' => $rtOptions,
            'statusOptions' => $statusOptions,
            'kesejahteraanOptions' => $kesejahteraanOptions,
            'bantuanOptions' => $bantuanOptions,
            'summary' => $summary,
            'bantuanBreakdown' => $bantuanBreakdown,
            'formAction' => $this->wargaUrl($filters),
            'exportUrl' => $this->wargaUrl($filters, ['export' => 'csv']),
            'xlsxUrl' => $this->wargaUrl($filters, ['export' => 'xlsx']),
            'pdfExportUrl' => $this->wargaUrl($filters, ['export' => 'pdf']),
            'cetakUrl' => $this->wargaUrl($filters, ['export' => 'cetak']),
            'allExportUrl' => $this->wargaUrl([], ['export' => 'csv']),
            'allXlsxUrl' => $this->wargaUrl([], ['export' => 'xlsx']),
            'allPdfExportUrl' => $this->wargaUrl([], ['export' => 'pdf']),
            'allCetakUrl' => $this->wargaUrl([], ['export' => 'cetak']),
            'templateUrl' => site_url('admin/import/template/warga'),
            'importUrl' => site_url('admin/import?type=warga'),
            'error' => session()->getFlashdata('error') ?: '',
            'success' => session()->getFlashdata('success') ?: '',
        ]);
    }

    private function wargaRows($db, array $filters): array
    {
        $builder = $db->table('warga');

        if (($filters['rt'] ?? '') !== '') {
            $this->applyWargaRtFilter($builder, $filters['rt']);
        }
        if (($filters['status_tinggal'] ?? '') !== '') {
            $builder->where('status_tinggal', $filters['status_tinggal']);
        }
        if (($filters['kategori_kesejahteraan'] ?? '') !== '') {
            $builder->where('kategori_kesejahteraan', $filters['kategori_kesejahteraan']);
        }
        if (($filters['penerima_bantuan'] ?? '') !== '') {
            $builder->where('penerima_bantuan', $filters['penerima_bantuan']);
        }

        return $builder
            ->orderBy('CAST(rt AS UNSIGNED)', 'ASC', false)
            ->orderBy('nama_kepala_keluarga', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function applyWargaRtFilter($builder, string $rt): void
    {
        $rt = normalize_rt_code($rt);
        if ($rt === '') {
            return;
        }

        if (! ctype_digit($rt)) {
            $builder->where('rt', $rt);
            return;
        }

        $numericRt = (string) ((int) $rt);

        $builder->groupStart()
            ->where('rt', $rt)
            ->orWhere('rt', $numericRt)
            ->orWhere('rt', 'RT' . $rt)
            ->orWhere('rt', 'RT ' . $rt)
            ->orWhere('rt', 'RT' . $numericRt)
            ->orWhere('rt', 'RT ' . $numericRt)
            ->orWhere('CAST(rt AS UNSIGNED) =', (int) $rt, false)
            ->groupEnd();
    }

    private function wargaUrl(array $filters, array $extra = []): string
    {
        $query = [];
        foreach (['rt', 'status_tinggal', 'kategori_kesejahteraan', 'penerima_bantuan'] as $key) {
            $value = trim((string) ($filters[$key] ?? ''));
            if ($value !== '') {
                $query[$key] = $value;
            }
        }
        foreach ($extra as $key => $value) {
            if ($value !== null && $value !== '') {
                $query[$key] = $value;
            }
        }

        $baseUrl = site_url('admin/warga');

        return $query === [] ? $baseUrl : $baseUrl . '?' . http_build_query($query);
    }

    private function downloadWargaCsv(array $rows, array $filters)
    {
        $handle = fopen('php://temp', 'r+');
        $columns = warga_csv_columns();
        fputcsv($handle, $columns);

        foreach ($rows as $row) {
            $line = [];
            foreach ($columns as $column) {
                $line[] = (string) ($row[$column] ?? '');
            }
            fputcsv($handle, $line);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = 'data-warga-' . (! empty($filters['rt']) ? 'rt-' . $filters['rt'] : 'semua-rt') . '-' . date('Ymd-His') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->setBody("\xEF\xBB\xBF" . $csv);
    }

    private function downloadWargaExcel(array $rows, array $filters)
    {
        $columns = warga_csv_columns();
        $data = [array_merge(['no'], $columns)];

        foreach ($rows as $index => $row) {
            $line = [$index + 1];
            foreach ($columns as $column) {
                $value = $row[$column] ?? '';
                $line[] = is_numeric($value) ? (float) $value : (string) $value;
            }
            $data[] = $line;
        }

        $xlsx = SimpleXLSXGen::fromArray($data);
        $xlsx->setDefaultFont('Calibri', 11);
        $xlsx->boldRow(1);

        $fileBase = 'data-warga-' . (! empty($filters['rt']) ? 'rt-' . $filters['rt'] : 'semua-rt') . '-' . date('Ymd-His');
        $tempFile = tempnam(WRITEPATH . 'cache', 'warga-xlsx-');
        $xlsxFile = $tempFile . '.xlsx';
        @unlink($tempFile);
        $xlsx->saveAs($xlsxFile);
        $binary = is_file($xlsxFile) ? file_get_contents($xlsxFile) : '';
        @unlink($xlsxFile);

        if ($binary === '') {
            return $this->response
                ->setStatusCode(500)
                ->setBody('File Excel gagal dibuat. Coba ulangi atau hubungi admin teknis.');
        }

        return $this->response->download($fileBase . '.xlsx', $binary, true);
    }

    private function wargaSummary(array $rows): array
    {
        $summary = [
            'totalKk' => count($rows),
            'totalWarga' => 0,
            'kurangMampu' => 0,
            'penerimaBantuan' => 0,
        ];

        foreach ($rows as $row) {
            $summary['totalWarga'] += (int) ($row['jumlah_anggota'] ?? 0);
            if (in_array((string) ($row['kategori_kesejahteraan'] ?? ''), ['kurang_mampu', 'sangat_kurang_mampu'], true)) {
                $summary['kurangMampu']++;
            }
            if (($row['penerima_bantuan'] ?? '') === 'ya') {
                $summary['penerimaBantuan']++;
            }
        }

        return $summary;
    }

    private function wargaSummaryByRt(array $rows): array
    {
        $summaryByRt = [];

        foreach ($rows as $row) {
            $rt = normalize_rt_code($row['rt'] ?? '');
            if ($rt === '') {
                $rt = '-';
            }

            if (! isset($summaryByRt[$rt])) {
                $summaryByRt[$rt] = [
                    'rt' => $rt,
                    'totalKk' => 0,
                    'totalWarga' => 0,
                    'kurangMampu' => 0,
                    'penerimaBantuan' => 0,
                ];
            }

            $summaryByRt[$rt]['totalKk']++;
            $summaryByRt[$rt]['totalWarga'] += (int) ($row['jumlah_anggota'] ?? 0);

            if (in_array((string) ($row['kategori_kesejahteraan'] ?? ''), ['kurang_mampu', 'sangat_kurang_mampu'], true)) {
                $summaryByRt[$rt]['kurangMampu']++;
            }

            if (($row['penerima_bantuan'] ?? '') === 'ya') {
                $summaryByRt[$rt]['penerimaBantuan']++;
            }
        }

        return array_values($summaryByRt);
    }

    private function wargaBantuanBreakdown(array $rows): array
    {
        $breakdown = [];

        foreach ($rows as $row) {
            if (($row['penerima_bantuan'] ?? '') !== 'ya') {
                continue;
            }

            $items = preg_split('/[,;|]+/', (string) ($row['jenis_bantuan'] ?? ''));
            foreach ($items ?: [] as $item) {
                $label = trim($item);
                if ($label === '') {
                    $label = 'Bantuan belum dirinci';
                }
                $key = strtolower($label);
                if (! isset($breakdown[$key])) {
                    $breakdown[$key] = ['label' => $label, 'total' => 0];
                }
                $breakdown[$key]['total']++;
            }
        }

        uasort($breakdown, static fn ($a, $b): int => ($b['total'] <=> $a['total']) ?: strcmp($a['label'], $b['label']));

        return array_values($breakdown);
    }

    private function downloadWargaPdf(array $data)
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml(view('admin/warga_print', $data), 'UTF-8');
        $dompdf->render();

        $fileName = 'laporan-warga-' . (! empty($data['filters']['rt']) ? 'rt-' . $data['filters']['rt'] : 'semua-rt') . '-' . date('Ymd-His');

        return $this->response->download($fileName . '.pdf', $dompdf->output(), true);
    }

    public function aspirasi()
    {
        $db = $this->db();
        $id = (int) $this->request->getGet('id');

        if ($this->request->getMethod() === 'POST') {
            $db->table('aspirasi')->where('id', (int) $this->request->getPost('id'))->update([
                'status' => $this->request->getPost('status') ?: 'baru',
                'catatan_admin' => trim((string) $this->request->getPost('catatan_admin')),
            ]);

            return redirect()->to(site_url('admin/aspirasi'));
        }

        if ($this->request->getGet('action') === 'delete' && $id > 0) {
            $db->table('aspirasi')->where('id', $id)->delete();

            return redirect()->to(site_url('admin/aspirasi'));
        }

        return view('admin/aspirasi', [
            'currentPage' => 'aspirasi',
            'rows' => $db->table('aspirasi')->orderBy('created_at', 'DESC')->get()->getResultArray(),
        ]);
    }

    public function keuangan()
    {
        $db = $this->db();
        $tableReady = ensure_keuangan_transaksi_table($db);
        $id = (int) $this->request->getGet('id');
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
            return view('admin/keuangan', [
                'currentPage' => 'keuangan',
                'rows' => [],
                'edit' => null,
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
                'rtOptions' => keuangan_default_rt_options(),
                'unitOptions' => keuangan_unit_options(keuangan_default_rt_options()),
                'success' => '',
                'error' => 'Tabel keuangan_transaksi belum bisa dibuat. Cek izin database hosting.',
            ]);
        }

        if ($this->request->getMethod() === 'POST') {
            $postedId = (int) $this->request->getPost('id');
            $tanggal = trim((string) $this->request->getPost('tanggal'));
            $lingkup = (string) $this->request->getPost('lingkup');
            $jenis = (string) $this->request->getPost('jenis');
            $kategori = trim((string) $this->request->getPost('kategori'));
            $nominal = (int) preg_replace('/\D+/', '', (string) $this->request->getPost('nominal'));
            $keterangan = trim((string) $this->request->getPost('keterangan'));
            $rt = normalize_rt_code($this->request->getPost('rt'));

            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                return redirect()->to($this->financeUrl($selectedStart, $selectedEnd, $selectedUnit))->with('error', 'Tanggal transaksi wajib diisi dengan format yang benar.');
            }

            if (! array_key_exists($lingkup, keuangan_scope_options())) {
                $lingkup = 'rw';
            }

            if (! array_key_exists($jenis, keuangan_type_options())) {
                $jenis = 'pemasukan';
            }

            if ($kategori === '' || $nominal <= 0) {
                return redirect()->to($this->financeUrl($selectedStart, $selectedEnd, $selectedUnit))->with('error', 'Kategori dan nominal transaksi wajib diisi.');
            }

            if ($lingkup === 'rt' && $rt === '') {
                return redirect()->to($this->financeUrl($selectedStart, $selectedEnd, $selectedUnit))->with('error', 'RT wajib diisi untuk transaksi kas per RT.');
            }

            $data = [
                'tanggal' => $tanggal,
                'lingkup' => $lingkup,
                'rt' => $lingkup === 'rt' ? $rt : null,
                'jenis' => $jenis,
                'kategori' => substr($kategori, 0, 120),
                'nominal' => $nominal,
                'keterangan' => $keterangan,
            ];

            if ($postedId > 0) {
                $db->table('keuangan_transaksi')->where('id', $postedId)->update($data);
                $message = 'Transaksi keuangan berhasil diperbarui.';
            } else {
                $db->table('keuangan_transaksi')->insert($data);
                $message = 'Transaksi keuangan berhasil ditambahkan.';
            }

            return redirect()->to($this->financeUrl($selectedStart, $selectedEnd, $selectedUnit))->with('success', $message);
        }

        if ($this->request->getGet('action') === 'delete' && $id > 0) {
            $db->table('keuangan_transaksi')->where('id', $id)->delete();

            return redirect()->to($this->financeUrl($selectedStart, $selectedEnd, $selectedUnit))->with('success', 'Transaksi keuangan berhasil dihapus.');
        }

        $edit = null;
        if ($this->request->getGet('action') === 'edit' && $id > 0) {
            $edit = $db->table('keuangan_transaksi')->where('id', $id)->get()->getRowArray();
        }

        $viewData = $this->financeViewData($db, $selectedStart, $selectedEnd, $selectedUnit);

        if ($this->request->getGet('export') === 'csv') {
            return $this->downloadFinanceReportCsv($viewData);
        }

        if ($this->request->getGet('export') === 'pdf') {
            return $this->downloadFinanceReportPdf($viewData);
        }

        return view('admin/keuangan', [
            'currentPage' => 'keuangan',
            'edit' => $edit,
            'success' => session()->getFlashdata('success') ?: '',
            'error' => session()->getFlashdata('error') ?: '',
        ] + $viewData);
    }

    public function akun()
    {
        $db = $this->db();
        ensure_admin_users_table($db);
        $admin = $db->table('admin_users')
            ->select('id, nama, username, role, status, created_at, password_hash')
            ->where('id', (int) session('admin_id'))
            ->get()
            ->getRowArray();

        if (! $admin) {
            return redirect()->to(site_url('admin/logout'));
        }

        if ($this->request->getMethod() === 'POST') {
            $action = (string) $this->request->getPost('action');
            $roleOptions = admin_role_options();

            if ($action === 'change_password') {
                $currentPassword = (string) $this->request->getPost('current_password');
                $newPassword = (string) $this->request->getPost('new_password');
                $confirmPassword = (string) $this->request->getPost('confirm_password');

                if (! password_verify($currentPassword, $admin['password_hash'])) {
                    return redirect()->to(site_url('admin/akun'))->with('error', 'Password saat ini tidak sesuai.');
                }
                if (strlen($newPassword) < 8) {
                    return redirect()->to(site_url('admin/akun'))->with('error', 'Password baru minimal 8 karakter.');
                }
                if ($newPassword !== $confirmPassword) {
                    return redirect()->to(site_url('admin/akun'))->with('error', 'Konfirmasi password baru belum sama.');
                }
                if ($currentPassword === $newPassword) {
                    return redirect()->to(site_url('admin/akun'))->with('error', 'Password baru harus berbeda dari password saat ini.');
                }

                $db->table('admin_users')->where('id', (int) $admin['id'])->update([
                    'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                ]);

                return redirect()->to(site_url('admin/akun'))->with('success', 'Password akun sendiri berhasil diperbarui.');
            }

            if ($action === 'save_user') {
                $userId = (int) $this->request->getPost('user_id');
                $formUrl = site_url('admin/akun' . ($userId > 0 ? '?edit_user=' . $userId : ''));
                $nama = trim((string) $this->request->getPost('nama'));
                $username = normalize_admin_username($this->request->getPost('username'));
                $role = trim((string) $this->request->getPost('role')) ?: 'admin';
                $status = (string) $this->request->getPost('status');
                $newPassword = (string) $this->request->getPost('new_user_password');
                $confirmPassword = (string) $this->request->getPost('confirm_user_password');

                if ($nama === '' || $username === '') {
                    return redirect()->to($formUrl)->withInput()->with('error', 'Nama dan username wajib diisi.');
                }
                if (! preg_match('/^[a-z0-9._-]{3,40}$/', $username)) {
                    return redirect()->to($formUrl)->withInput()->with('error', 'Username minimal 3 karakter. Spasi akan otomatis diganti underscore.');
                }
                if (! isset($roleOptions[$role])) {
                    $role = 'admin';
                }
                if (! in_array($status, ['aktif', 'nonaktif'], true)) {
                    $status = 'aktif';
                }
                if ($userId === (int) $admin['id']) {
                    $status = 'aktif';
                }

                $duplicate = $db->table('admin_users')
                    ->where('username', $username)
                    ->where('id !=', $userId)
                    ->get()
                    ->getRowArray();
                if ($duplicate) {
                    return redirect()->to($formUrl)->withInput()->with('error', 'Username sudah dipakai akun lain.');
                }

                $data = [
                    'nama' => substr($nama, 0, 120),
                    'username' => substr($username, 0, 80),
                    'role' => substr($role, 0, 40),
                    'status' => $status,
                ];

                if ($userId <= 0 || $newPassword !== '') {
                    if (strlen($newPassword) < 8) {
                        return redirect()->to($formUrl)->withInput()->with('error', 'Password akun baru/reset minimal 8 karakter.');
                    }
                    if ($newPassword !== $confirmPassword) {
                        return redirect()->to($formUrl)->withInput()->with('error', 'Konfirmasi password akun belum sama.');
                    }
                    $data['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }

                if ($userId > 0) {
                    $db->table('admin_users')->where('id', $userId)->update($data);
                    return redirect()->to(site_url('admin/akun'))->with('success', 'Akun admin berhasil diperbarui.');
                }

                $db->table('admin_users')->insert($data);
                return redirect()->to(site_url('admin/akun'))->with('success', 'Akun admin baru berhasil dibuat.');
            }

            if ($action === 'delete_user') {
                $userId = (int) $this->request->getPost('user_id');
                if ($userId === (int) $admin['id']) {
                    return redirect()->to(site_url('admin/akun'))->with('error', 'Akun yang sedang dipakai tidak bisa dihapus.');
                }

                $activeCount = (int) ($db->query("SELECT COUNT(*) AS total FROM admin_users WHERE status='aktif'")->getRowArray()['total'] ?? 0);
                $target = $db->table('admin_users')->where('id', $userId)->get()->getRowArray();
                if ($target && ($target['status'] ?? '') === 'aktif' && $activeCount <= 1) {
                    return redirect()->to(site_url('admin/akun'))->with('error', 'Minimal harus ada satu akun aktif.');
                }

                $db->table('admin_users')->where('id', $userId)->delete();
                return redirect()->to(site_url('admin/akun'))->with('success', 'Akun admin berhasil dihapus.');
            }
        }

        $editUser = null;
        $editId = (int) $this->request->getGet('edit_user');
        if ($editId > 0) {
            $editUser = $db->table('admin_users')
                ->select('id, nama, username, role, status, created_at')
                ->where('id', $editId)
                ->get()
                ->getRowArray();
        }

        return view('admin/akun', [
            'currentPage' => 'akun',
            'admin' => $admin,
            'users' => $db->table('admin_users')->select('id, nama, username, role, status, created_at')->orderBy('id', 'ASC')->get()->getResultArray(),
            'editUser' => $editUser,
            'roleOptions' => admin_role_options(),
            'error' => session()->getFlashdata('error') ?: '',
            'success' => session()->getFlashdata('success') ?: '',
        ]);
    }

    private function crud(string $page, array $config)
    {
        $db = $this->db();
        $table = $db->table($config['table']);
        $id = (int) $this->request->getGet('id');

        if ($this->request->getMethod() === 'POST') {
            $data = [];
            foreach ($config['fields'] as $name => $field) {
                $value = $this->request->getPost($name);
                if (($field['type'] ?? '') === 'number') {
                    $value = (int) $value;
                } else {
                    $value = trim((string) $value);
                }
                $data[$name] = $value;
            }

            $postedId = (int) $this->request->getPost('id');
            if ($postedId > 0) {
                $db->table($config['table'])->where('id', $postedId)->update($data);
            } else {
                $db->table($config['table'])->insert($data);
            }

            return redirect()->to(site_url('admin/' . $page));
        }

        if ($this->request->getGet('action') === 'delete' && $id > 0) {
            $db->table($config['table'])->where('id', $id)->delete();

            return redirect()->to(site_url('admin/' . $page));
        }

        $edit = null;
        if ($this->request->getGet('action') === 'edit' && $id > 0) {
            $edit = $db->table($config['table'])->where('id', $id)->get()->getRowArray();
        }

        $rowsBuilder = $db->table($config['table']);
        foreach ($config['order'] as $column => $direction) {
            $rowsBuilder->orderBy($column, $direction);
        }

        return view('admin/crud', [
            'currentPage' => $page,
            'page' => $page,
            'config' => $config,
            'edit' => $edit,
            'rows' => $rowsBuilder->get()->getResultArray(),
        ]);
    }

    private function isAllowedEducationUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
    }

    private function removeManagedEducationFile(string $relativePath): void
    {
        $relativePath = ltrim(str_replace('\\', '/', trim($relativePath)), '/');
        if (! preg_match('#^assets/uploads/edukasi/[a-zA-Z0-9._-]+$#', $relativePath)) {
            return;
        }

        $absolutePath = rtrim(FCPATH, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
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
        $path = $this->pengurusStructureDescriptionPath();

        return is_file($path) ? trim((string) file_get_contents($path)) : '';
    }

    private function pengurusStructureDescriptionPath(): string
    {
        return WRITEPATH . self::PENGURUS_STRUCTURE_DESCRIPTION;
    }

    private function removePengurusStructureImages(): void
    {
        foreach (self::PENGURUS_STRUCTURE_IMAGE_EXTENSIONS as $extension) {
            $path = FCPATH . 'assets' . DIRECTORY_SEPARATOR . self::PENGURUS_STRUCTURE_IMAGE . '.' . $extension;
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function financeViewData($db, string $selectedStart, string $selectedEnd, string $selectedUnit = ''): array
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

        $rtOptions = $this->financeRtOptions($db, $rtSummaries);
        $panitiaRows = array_values(array_filter($rows, static fn ($row) => ($row['lingkup'] ?? '') === 'panitia'));
        $unitSummaries = keuangan_current_unit_summaries($summary, $rtSummaries, $rtOptions, $selectedUnit);

        return [
            'rows' => $rows,
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
            'rtOptions' => $rtOptions,
            'unitOptions' => keuangan_unit_options($rtOptions),
        ];
    }

    private function financeRtOptions($db, array $rtSummaries = []): array
    {
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

        return $rtOptions;
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

    private function financeUrl(string $selectedStart, string $selectedEnd, string $selectedUnit = '', array $extra = []): string
    {
        $query = array_filter(array_merge([
            'start' => $selectedStart,
            'end' => $selectedEnd,
            'unit' => keuangan_normalize_unit_filter($selectedUnit),
        ], $extra), static fn ($value) => $value !== '' && $value !== null);

        return site_url('admin/keuangan' . ($query ? '?' . http_build_query($query) : ''));
    }

    private function downloadFinanceReportPdf(array $data)
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml(view('admin/keuangan_print', $data), 'UTF-8');
        $dompdf->render();

        $fileName = 'laporan-keuangan-' . keuangan_period_slug($data['selectedStart'] ?? date('Y-m-01'), $data['selectedEnd'] ?? date('Y-m-t'));
        if (! empty($data['selectedUnit'])) {
            $fileName .= '-' . str_replace(':', '-', $data['selectedUnit']);
        }

        return $this->response->download($fileName . '.pdf', $dompdf->output(), true);
    }

    private function downloadFinanceReportCsv(array $data)
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['tanggal', 'unit_kas', 'lingkup', 'rt', 'jenis', 'kategori', 'nominal', 'keterangan']);

        foreach (($data['rows'] ?? []) as $row) {
            $scope = (string) ($row['lingkup'] ?? 'rw');
            $unitLabel = $scope === 'rt' && ! empty($row['rt'])
                ? 'RT ' . normalize_rt_code($row['rt'])
                : keuangan_scope_label($scope);
            fputcsv($handle, [
                $row['tanggal'] ?? '',
                $unitLabel,
                $row['lingkup'] ?? '',
                $row['rt'] ?? '',
                $row['jenis'] ?? '',
                $row['kategori'] ?? '',
                $row['nominal'] ?? 0,
                $row['keterangan'] ?? '',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = 'laporan-keuangan-' . keuangan_period_slug($data['selectedStart'] ?? date('Y-m-01'), $data['selectedEnd'] ?? date('Y-m-t')) . (! empty($data['selectedUnit']) ? '-' . str_replace(':', '-', $data['selectedUnit']) : '-semua-unit') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->setBody("\xEF\xBB\xBF" . $csv);
    }
}
