<?php

if (! function_exists('rw_normalize_text')) {
    function rw_normalize_text($value): string
    {
        return strtr((string) $value, [
            'Ã”Ã‡Ã–' => "'",
            'Ã¢â‚¬â„¢' => "'",
            'Ã¢â‚¬Å“' => '"',
            'Ã¢â‚¬9d' => '"',
            'Ã¢â‚¬â€œ' => '-',
            'Ã¢â‚¬â€' => '-',
            'Â©' => '(c)',
        ]);
    }
}

if (! function_exists('rw_esc')) {
    function rw_esc($value): string
    {
        return esc(rw_normalize_text($value));
    }
}

if (! function_exists('field_value')) {
    function field_value(string $key, $default = ''): string
    {
        return rw_esc(old($key, $default));
    }
}

if (! function_exists('wa_link')) {
    function wa_link($value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if ($digits === '') {
            return '';
        }

        if (strpos($digits, '0') === 0) {
            $digits = '62' . substr($digits, 1);
        } elseif (strpos($digits, '62') !== 0) {
            $digits = '62' . ltrim($digits, '0');
        }

        return strlen($digits) < 10 ? '' : 'https://wa.me/' . $digits;
    }
}

if (! function_exists('instagram_link')) {
    function instagram_link($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        return 'https://instagram.com/' . trim(ltrim($value, '@'), '/');
    }
}

if (! function_exists('rw_official_email')) {
    function rw_official_email($value = ''): string
    {
        $email = trim((string) $value);

        return $email !== '' ? $email : 'sekretariat.rw05.citeureup@gmail.com';
    }
}

if (! function_exists('format_date_id')) {
    function format_date_id($value): string
    {
        $timestamp = strtotime((string) $value);
        if (! $timestamp) {
            return '';
        }

        $months = [
            1 => 'Jan',
            'Feb',
            'Mar',
            'Apr',
            'Mei',
            'Jun',
            'Jul',
            'Agu',
            'Sep',
            'Okt',
            'Nov',
            'Des',
        ];

        return date('d', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    }
}

if (! function_exists('fmt_date')) {
    function fmt_date($date): string
    {
        if (! $date) {
            return '-';
        }

        return date('d/m/Y', strtotime((string) $date));
    }
}

if (! function_exists('fmt_currency')) {
    function fmt_currency($amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('normalize_rt_code')) {
    function normalize_rt_code($rt): string
    {
        $rt = strtoupper(trim((string) $rt));
        $rt = str_replace(['RT', ' '], '', $rt);
        $digits = preg_replace('/\D+/', '', $rt);

        if ($digits === '') {
            return $rt;
        }

        return str_pad(substr($digits, 0, 2), 2, '0', STR_PAD_LEFT);
    }
}

if (! function_exists('keuangan_scope_options')) {
    function keuangan_scope_options(): array
    {
        return [
            'rw' => 'RW Umum',
            'rt' => 'Kas RT',
            'panitia' => 'Panitia Agustusan',
            'dkm' => 'DKM',
            'karang_taruna' => 'Karang Taruna',
            'posyandu' => 'Posyandu',
            'posbindu' => 'Posbindu',
        ];
    }
}

if (! function_exists('keuangan_non_rt_scope_options')) {
    function keuangan_non_rt_scope_options(): array
    {
        $options = keuangan_scope_options();
        unset($options['rt']);

        return $options;
    }
}

if (! function_exists('keuangan_normalize_scope')) {
    function keuangan_normalize_scope($scope): string
    {
        $scope = strtolower(trim((string) $scope));
        $scope = str_replace([' ', '-'], '_', $scope);

        return array_key_exists($scope, keuangan_scope_options()) ? $scope : 'rw';
    }
}

if (! function_exists('keuangan_scope_summary_label')) {
    function keuangan_scope_summary_label($scope): string
    {
        $scope = keuangan_normalize_scope($scope);
        if ($scope === 'rw') {
            return 'Kas RW';
        }
        if ($scope === 'rt') {
            return 'Kas RT';
        }

        return keuangan_scope_label($scope);
    }
}

if (! function_exists('keuangan_scope_label')) {
    function keuangan_scope_label($scope): string
    {
        $scope = (string) $scope;
        $options = keuangan_scope_options();

        return $options[$scope] ?? strtoupper($scope);
    }
}

if (! function_exists('keuangan_type_options')) {
    function keuangan_type_options(): array
    {
        return [
            'pemasukan' => 'Pemasukan',
            'pengeluaran' => 'Pengeluaran',
        ];
    }
}

if (! function_exists('keuangan_type_label')) {
    function keuangan_type_label($type): string
    {
        $type = (string) $type;
        $options = keuangan_type_options();

        return $options[$type] ?? ucfirst($type);
    }
}

if (! function_exists('keuangan_empty_summary')) {
    function keuangan_empty_summary(): array
    {
        $unitBuckets = [];
        foreach (keuangan_non_rt_scope_options() as $scope => $label) {
            $unitBuckets[$scope] = [
                'scope' => $scope,
                'label' => keuangan_scope_summary_label($scope),
                'income' => 0,
                'expense' => 0,
                'balance' => 0,
            ];
        }

        return [
            'rwIncome' => 0,
            'rwExpense' => 0,
            'rwBalance' => 0,
            'rtIncome' => 0,
            'rtExpense' => 0,
            'rtBalance' => 0,
            'panitiaIncome' => 0,
            'panitiaExpense' => 0,
            'panitiaBalance' => 0,
            'unitBuckets' => $unitBuckets,
            'overallBalance' => 0,
        ];
    }
}

if (! function_exists('keuangan_summary_add_transaction')) {
    function keuangan_summary_add_transaction(array &$summary, $scope, $type, $amount): void
    {
        $scope = keuangan_normalize_scope($scope);
        $type = (string) $type === 'pengeluaran' ? 'pengeluaran' : 'pemasukan';
        $amount = (int) $amount;

        if ($scope === 'rt') {
            if ($type === 'pemasukan') {
                $summary['rtIncome'] += $amount;
            } else {
                $summary['rtExpense'] += $amount;
            }

            return;
        }

        if (! isset($summary['unitBuckets'][$scope])) {
            $summary['unitBuckets'][$scope] = [
                'scope' => $scope,
                'label' => keuangan_scope_summary_label($scope),
                'income' => 0,
                'expense' => 0,
                'balance' => 0,
            ];
        }

        if ($type === 'pemasukan') {
            $summary['unitBuckets'][$scope]['income'] += $amount;
        } else {
            $summary['unitBuckets'][$scope]['expense'] += $amount;
        }
    }
}

if (! function_exists('keuangan_finalize_summary')) {
    function keuangan_finalize_summary(array $summary): array
    {
        $overallIncome = (int) ($summary['rtIncome'] ?? 0);
        $overallExpense = (int) ($summary['rtExpense'] ?? 0);

        foreach (keuangan_non_rt_scope_options() as $scope => $label) {
            if (! isset($summary['unitBuckets'][$scope])) {
                $summary['unitBuckets'][$scope] = [
                    'scope' => $scope,
                    'label' => keuangan_scope_summary_label($scope),
                    'income' => 0,
                    'expense' => 0,
                    'balance' => 0,
                ];
            }

            $summary['unitBuckets'][$scope]['label'] = keuangan_scope_summary_label($scope);
            $summary['unitBuckets'][$scope]['balance'] = (int) $summary['unitBuckets'][$scope]['income'] - (int) $summary['unitBuckets'][$scope]['expense'];
            $overallIncome += (int) $summary['unitBuckets'][$scope]['income'];
            $overallExpense += (int) $summary['unitBuckets'][$scope]['expense'];
        }

        $summary['rwIncome'] = (int) ($summary['unitBuckets']['rw']['income'] ?? 0);
        $summary['rwExpense'] = (int) ($summary['unitBuckets']['rw']['expense'] ?? 0);
        $summary['rwBalance'] = (int) ($summary['unitBuckets']['rw']['balance'] ?? 0);
        $summary['panitiaIncome'] = (int) ($summary['unitBuckets']['panitia']['income'] ?? 0);
        $summary['panitiaExpense'] = (int) ($summary['unitBuckets']['panitia']['expense'] ?? 0);
        $summary['panitiaBalance'] = (int) ($summary['unitBuckets']['panitia']['balance'] ?? 0);
        $summary['rtBalance'] = (int) ($summary['rtIncome'] ?? 0) - (int) ($summary['rtExpense'] ?? 0);
        $summary['overallBalance'] = $overallIncome - $overallExpense;

        return $summary;
    }
}

if (! function_exists('keuangan_summary_unit')) {
    function keuangan_summary_unit(array $summary, $scope): array
    {
        $scope = keuangan_normalize_scope($scope);
        $bucket = $summary['unitBuckets'][$scope] ?? [
            'scope' => $scope,
            'label' => keuangan_scope_summary_label($scope),
            'income' => 0,
            'expense' => 0,
            'balance' => 0,
        ];

        return [
            'label' => $bucket['label'] ?? keuangan_scope_summary_label($scope),
            'income' => (int) ($bucket['income'] ?? 0),
            'expense' => (int) ($bucket['expense'] ?? 0),
            'balance' => (int) ($bucket['balance'] ?? ((int) ($bucket['income'] ?? 0) - (int) ($bucket['expense'] ?? 0))),
        ];
    }
}

if (! function_exists('keuangan_default_rt_options')) {
    function keuangan_default_rt_options(): array
    {
        return [
            '01' => 'RT 01',
            '02' => 'RT 02',
            '03' => 'RT 03',
        ];
    }
}

if (! function_exists('keuangan_normalize_unit_filter')) {
    function keuangan_normalize_unit_filter($unit): string
    {
        $unit = strtolower(trim((string) $unit));
        $unit = str_replace([' ', '-'], '_', $unit);
        if (array_key_exists($unit, keuangan_non_rt_scope_options())) {
            return $unit;
        }

        if (preg_match('/^rt[:\-_]?(.+)$/', $unit, $matches)) {
            $rt = normalize_rt_code($matches[1]);
            return $rt !== '' ? 'rt:' . $rt : '';
        }

        return '';
    }
}

if (! function_exists('keuangan_unit_filter_scope')) {
    function keuangan_unit_filter_scope($unit): string
    {
        $unit = keuangan_normalize_unit_filter($unit);

        return array_key_exists($unit, keuangan_non_rt_scope_options()) ? $unit : '';
    }
}

if (! function_exists('keuangan_unit_filter_rt')) {
    function keuangan_unit_filter_rt($unit): string
    {
        $unit = keuangan_normalize_unit_filter($unit);
        if (preg_match('/^rt:(\d{2})$/', $unit, $matches)) {
            return $matches[1];
        }

        return '';
    }
}

if (! function_exists('keuangan_unit_options')) {
    function keuangan_unit_options(array $rtOptions): array
    {
        $options = [
            '' => 'Semua Unit Kas',
            'rw' => 'Kas RW',
        ];

        foreach ($rtOptions as $rt => $label) {
            $rt = normalize_rt_code($rt);
            if ($rt !== '') {
                $options['rt:' . $rt] = $label;
            }
        }

        foreach (keuangan_non_rt_scope_options() as $scope => $label) {
            if ($scope !== 'rw') {
                $options[$scope] = keuangan_scope_summary_label($scope);
            }
        }

        return $options;
    }
}

if (! function_exists('keuangan_unit_filter_label')) {
    function keuangan_unit_filter_label($unit): string
    {
        $unit = keuangan_normalize_unit_filter($unit);
        $scope = keuangan_unit_filter_scope($unit);
        if ($scope !== '') {
            return keuangan_scope_summary_label($scope);
        }

        $rt = keuangan_unit_filter_rt($unit);
        if ($rt !== '') {
            return 'RT ' . $rt;
        }

        return 'Semua Unit Kas';
    }
}

if (! function_exists('keuangan_normalize_date_range')) {
    function keuangan_normalize_date_range($start, $end, $fallbackMonth = ''): array
    {
        $fallbackMonth = preg_match('/^\d{4}-\d{2}$/', (string) $fallbackMonth) ? (string) $fallbackMonth : date('Y-m');
        $defaultStart = $fallbackMonth . '-01';
        $defaultEnd = date('Y-m-t', strtotime($defaultStart));

        $start = trim((string) $start);
        $end = trim((string) $end);
        $start = preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) ? $start : $defaultStart;
        $end = preg_match('/^\d{4}-\d{2}-\d{2}$/', $end) ? $end : $defaultEnd;

        if (strtotime($start) > strtotime($end)) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end];
    }
}

if (! function_exists('keuangan_period_label')) {
    function keuangan_period_label($start, $end): string
    {
        $startLabel = format_date_id($start);
        $endLabel = format_date_id($end);

        return $startLabel === $endLabel ? $startLabel : $startLabel . ' s.d. ' . $endLabel;
    }
}

if (! function_exists('keuangan_period_slug')) {
    function keuangan_period_slug($start, $end): string
    {
        return preg_replace('/[^0-9\-]+/', '-', (string) $start . '-sd-' . (string) $end) ?: date('Y-m');
    }
}

if (! function_exists('keuangan_unit_summaries')) {
    function keuangan_unit_summaries(array $summary, array $rtSummaries, array $rtOptions): array
    {
        $rtSummaryMap = [];
        foreach ($rtSummaries as $row) {
            $rt = normalize_rt_code($row['rt'] ?? '');
            if ($rt !== '') {
                $rtSummaryMap[$rt] = $row;
            }
        }

        $units = [keuangan_summary_unit($summary, 'rw')];

        foreach ($rtOptions as $rt => $label) {
            $row = $rtSummaryMap[$rt] ?? [];
            $income = (int) ($row['total_pemasukan'] ?? 0);
            $expense = (int) ($row['total_pengeluaran'] ?? 0);
            $units[] = [
                'label' => $label,
                'income' => $income,
                'expense' => $expense,
                'balance' => (int) ($row['saldo'] ?? ($income - $expense)),
            ];
        }

        foreach (keuangan_non_rt_scope_options() as $scope => $label) {
            if ($scope !== 'rw') {
                $units[] = keuangan_summary_unit($summary, $scope);
            }
        }

        return $units;
    }
}

if (! function_exists('keuangan_current_unit_summaries')) {
    function keuangan_current_unit_summaries(array $summary, array $rtSummaries, array $rtOptions, $selectedUnit): array
    {
        $selectedUnit = keuangan_normalize_unit_filter($selectedUnit);
        if ($selectedUnit === '') {
            return keuangan_unit_summaries($summary, $rtSummaries, $rtOptions);
        }

        $selectedScope = keuangan_unit_filter_scope($selectedUnit);
        if ($selectedScope !== '') {
            return [keuangan_summary_unit($summary, $selectedScope)];
        }

        $selectedRt = keuangan_unit_filter_rt($selectedUnit);
        if ($selectedRt !== '') {
            $summaryMap = [];
            foreach ($rtSummaries as $row) {
                $summaryMap[normalize_rt_code($row['rt'] ?? '')] = $row;
            }
            $row = $summaryMap[$selectedRt] ?? [];
            $income = (int) ($row['total_pemasukan'] ?? $summary['rtIncome'] ?? 0);
            $expense = (int) ($row['total_pengeluaran'] ?? $summary['rtExpense'] ?? 0);

            return [[
                'label' => 'RT ' . $selectedRt,
                'income' => $income,
                'expense' => $expense,
                'balance' => (int) ($row['saldo'] ?? ($income - $expense)),
            ]];
        }

        return keuangan_unit_summaries($summary, $rtSummaries, $rtOptions);
    }
}

if (! function_exists('service_badge')) {
    function service_badge($name, $icon): string
    {
        $icon = trim((string) $icon);
        if ($icon !== '' && preg_match('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $icon)) {
            return $icon;
        }

        $ascii = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $icon));
        if ($ascii !== '' && strlen($ascii) <= 3) {
            return $ascii;
        }

        $badge = '';
        $words = preg_split('/\s+/', trim((string) $name));
        foreach ($words as $word) {
            $cleanWord = preg_replace('/[^A-Za-z0-9]/', '', $word);
            if ($cleanWord === '') {
                continue;
            }

            $badge .= strtoupper($cleanWord[0]);
            if (strlen($badge) === 2) {
                break;
            }
        }

        return $badge !== '' ? $badge : 'RW';
    }
}

if (! function_exists('pengurus_context_label')) {
    function pengurus_context_label($jabatan, $rt): string
    {
        $rt = trim((string) $rt);
        if ($rt !== '') {
            return 'RT ' . $rt;
        }

        $jabatan = strtolower(trim((string) $jabatan));
        if ($jabatan === '') {
            return 'Pengurus RW 05';
        }

        $map = [
            'ketua rw' => 'Ketua RW 05',
            'sekretaris' => 'Administrasi RW 05',
            'bendahara' => 'Keuangan RW 05',
            'keamanan' => 'Keamanan Lingkungan',
            'kebersihan' => 'Lingkungan & Kebersihan',
            'sosial' => 'Sosial Kemasyarakatan',
            'informasi' => 'Informasi & Digitalisasi',
        ];

        foreach ($map as $needle => $label) {
            if (strpos($jabatan, $needle) !== false) {
                return $label;
            }
        }

        return 'Pengurus RW 05';
    }
}

if (! function_exists('is_selected')) {
    function is_selected($a, $b): string
    {
        return (string) $a === (string) $b ? 'selected' : '';
    }
}

if (! function_exists('surat_type_options')) {
    function surat_type_options(): array
    {
        return [
            'Surat Pengantar',
            'Surat Keterangan',
            'Surat Undangan',
            'Surat Edaran / Pemberitahuan',
            'Surat Permohonan',
            'Surat Tugas / Mandat',
            'Berita Acara',
            'Surat Keputusan Ketua RW 05',
        ];
    }
}

if (! function_exists('surat_template_profiles')) {
    function surat_template_profiles(): array
    {
        return [
            'Surat Pengantar' => [
                'title' => 'SURAT PENGANTAR',
                'recipient_default' => '',
                'salutation' => 'Dengan hormat,',
                'purpose_label' => 'Keperluan Pengantar',
                'lead' => 'Yang bertanda tangan di bawah ini, Ketua RW 05 Desa Citeureup Kecamatan Dayeuhkolot Kabupaten Bandung, dengan ini memberikan pengantar kepada warga sebagai berikut:',
                'rows' => [
                    ['label' => 'Nama', 'value' => '{{nama}}'],
                    ['label' => 'Tempat/Tanggal Lahir', 'value' => '{{tempat_tanggal_lahir}}'],
                    ['label' => 'Alamat', 'value' => '{{alamat}}'],
                    ['label' => 'RT/RW', 'value' => '{{rt}} / 05'],
                    ['label' => 'Keperluan', 'value' => '{{keperluan}}'],
                ],
                'body' => [
                    'Surat pengantar ini dibuat sebagai dasar bagi yang bersangkutan untuk mengurus keperluan administrasi lebih lanjut kepada pihak terkait.',
                ],
                'detail_label' => 'Keterangan Tambahan',
                'closing' => 'Demikian surat pengantar ini dibuat untuk dapat dipergunakan sebagaimana mestinya. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.',
            ],
            'Surat Keterangan' => [
                'title' => 'SURAT KETERANGAN',
                'recipient_default' => '',
                'salutation' => 'Dengan hormat,',
                'purpose_label' => 'Keterangan',
                'lead' => 'Yang bertanda tangan di bawah ini, Ketua RW 05 Desa Citeureup Kecamatan Dayeuhkolot Kabupaten Bandung, menerangkan bahwa:',
                'rows' => [
                    ['label' => 'Nama', 'value' => '{{nama}}'],
                    ['label' => 'NIK', 'value' => '{{nik}}'],
                    ['label' => 'Alamat', 'value' => '{{alamat}}'],
                    ['label' => 'RT/RW', 'value' => '{{rt}} / 05'],
                    ['label' => 'Keterangan', 'value' => '{{keterangan}}'],
                ],
                'body' => [
                    'Berdasarkan data dan/atau keterangan dari Ketua RT setempat, nama tersebut benar merupakan warga/berdomisili di wilayah RW 05 Desa Citeureup.',
                ],
                'detail_label' => 'Keterangan Tambahan',
                'closing' => 'Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.',
            ],
            'Surat Undangan' => [
                'title' => 'SURAT UNDANGAN',
                'recipient_default' => 'Bapak/Ibu/Saudara/i Warga RW 05',
                'salutation' => 'Dengan hormat,',
                'purpose_label' => 'Agenda',
                'lead' => 'Dalam rangka meningkatkan koordinasi dan kebersamaan warga RW 05 Desa Citeureup, kami mengundang Bapak/Ibu/Saudara/i untuk hadir pada:',
                'rows' => [
                    ['label' => 'Hari/Tanggal', 'value' => '{{hari_tanggal}}'],
                    ['label' => 'Waktu', 'value' => '{{waktu}}'],
                    ['label' => 'Tempat', 'value' => '{{tempat}}'],
                    ['label' => 'Agenda', 'value' => '{{agenda}}'],
                ],
                'body' => [
                    'Mengingat pentingnya kegiatan tersebut, kami mengharapkan kehadiran Bapak/Ibu/Saudara/i tepat pada waktunya.',
                ],
                'detail_label' => 'Keterangan Tambahan',
                'closing' => 'Demikian undangan ini kami sampaikan. Atas perhatian dan kehadirannya, kami ucapkan terima kasih.',
            ],
            'Surat Edaran / Pemberitahuan' => [
                'title' => 'SURAT EDARAN / PEMBERITAHUAN',
                'recipient_default' => 'Bapak/Ibu/Saudara/i Warga RW 05',
                'salutation' => 'Dengan hormat,',
                'purpose_label' => 'Kegiatan / Informasi',
                'lead' => 'Sehubungan dengan pelaksanaan program dan kegiatan lingkungan RW 05 Desa Citeureup, bersama ini kami menyampaikan pemberitahuan sebagai berikut:',
                'rows' => [
                    ['label' => 'Kegiatan/Informasi', 'value' => '{{kegiatan_informasi}}'],
                    ['label' => 'Hari/Tanggal', 'value' => '{{hari_tanggal}}'],
                    ['label' => 'Waktu', 'value' => '{{waktu}}'],
                    ['label' => 'Tempat', 'value' => '{{tempat}}'],
                    ['label' => 'Keterangan', 'value' => '{{keterangan}}'],
                ],
                'body' => [
                    'Kami mengimbau seluruh warga untuk memperhatikan informasi ini dan berpartisipasi sesuai dengan kebutuhan kegiatan tersebut.',
                ],
                'detail_label' => 'Keterangan Tambahan',
                'closing' => 'Demikian pemberitahuan ini kami sampaikan. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.',
            ],
            'Surat Permohonan' => [
                'title' => 'SURAT PERMOHONAN',
                'recipient_default' => '',
                'salutation' => 'Dengan hormat,',
                'purpose_label' => 'Kegiatan / Keperluan',
                'lead' => 'Sehubungan dengan kebutuhan warga dan lingkungan RW 05 Desa Citeureup, bersama ini kami mengajukan permohonan kepada Bapak/Ibu terkait hal berikut:',
                'rows' => [
                    ['label' => 'Jenis Permohonan', 'value' => '{{jenis_permohonan}}'],
                    ['label' => 'Kegiatan/Keperluan', 'value' => '{{kegiatan_keperluan}}'],
                    ['label' => 'Waktu Pelaksanaan', 'value' => '{{waktu_pelaksanaan}}'],
                    ['label' => 'Lokasi', 'value' => '{{lokasi}}'],
                ],
                'body' => [
                    'Permohonan ini diajukan sebagai upaya mendukung pelayanan warga, kegiatan sosial, keamanan, kebersihan, serta penataan lingkungan RW 05 Desa Citeureup.',
                ],
                'detail_label' => 'Keterangan Tambahan',
                'closing' => 'Demikian surat permohonan ini kami sampaikan. Atas perhatian dan dukungannya, kami ucapkan terima kasih.',
            ],
            'Surat Tugas / Mandat' => [
                'title' => 'SURAT TUGAS / MANDAT',
                'recipient_default' => '',
                'salutation' => 'Dengan hormat,',
                'purpose_label' => 'Tugas',
                'lead' => 'Dalam rangka mendukung pelaksanaan kegiatan dan pelayanan warga RW 05 Desa Citeureup, Ketua RW 05 memberikan tugas/mandat kepada:',
                'rows' => [
                    ['label' => 'Nama', 'value' => '{{nama_ditugaskan}}'],
                    ['label' => 'Jabatan', 'value' => '{{jabatan}}'],
                    ['label' => 'Alamat', 'value' => '{{alamat_tugas}}'],
                    ['label' => 'Tugas', 'value' => '{{tugas}}'],
                    ['label' => 'Masa Tugas', 'value' => '{{masa_tugas}}'],
                ],
                'body' => [
                    'Yang bersangkutan diharapkan melaksanakan tugas dengan penuh tanggung jawab, tertib, transparan, dan tetap berkoordinasi dengan pengurus RW 05 serta Ketua RT setempat.',
                ],
                'detail_label' => 'Keterangan Tambahan',
                'closing' => 'Demikian surat tugas/mandat ini dibuat untuk dilaksanakan sebagaimana mestinya.',
            ],
            'Berita Acara' => [
                'title' => 'BERITA ACARA',
                'recipient_default' => 'Arsip RW 05 Desa Citeureup',
                'salutation' => '',
                'purpose_label' => 'Nama Kegiatan',
                'lead' => 'Pada hari ini, {{hari_tanggal}}, bertempat di {{tempat}}, telah dilaksanakan kegiatan/musyawarah sebagai berikut:',
                'rows' => [
                    ['label' => 'Nama Kegiatan', 'value' => '{{nama_kegiatan}}'],
                    ['label' => 'Agenda', 'value' => '{{agenda}}'],
                    ['label' => 'Peserta', 'value' => '{{peserta}}'],
                    ['label' => 'Pimpinan', 'value' => '{{pimpinan}}'],
                ],
                'body' => [
                    'Adapun hasil kegiatan/musyawarah tersebut adalah sebagai berikut:',
                    '1. {{hasil_1}}',
                    '2. {{hasil_2}}',
                    '3. {{hasil_3}}',
                    'Berita acara ini dibuat sebagai bukti tertulis atas pelaksanaan kegiatan/musyawarah dan kesepakatan warga RW 05 Desa Citeureup.',
                ],
                'detail_label' => 'Keterangan Tambahan',
                'closing' => 'Demikian berita acara ini dibuat dengan sebenar-benarnya untuk dipergunakan sebagaimana mestinya.',
            ],
            'Surat Keputusan Ketua RW 05' => [
                'title' => 'SURAT KEPUTUSAN KETUA RW 05',
                'recipient_default' => 'Arsip RW 05 Desa Citeureup',
                'salutation' => '',
                'purpose_label' => 'Pokok Keputusan',
                'lead' => '',
                'rows' => [],
                'body' => [
                    'Menimbang bahwa untuk mendukung pelaksanaan program kerja, pelayanan warga, dan kegiatan lingkungan RW 05 Desa Citeureup, dipandang perlu menetapkan keputusan Ketua RW 05.',
                    'Mengingat hasil musyawarah dan/atau kebutuhan organisasi lingkungan RW 05 Desa Citeureup, maka Ketua RW 05 menetapkan:',
                    'KESATU: {{keputusan_1}}',
                    'KEDUA: {{keputusan_2}}',
                    'KETIGA: {{keputusan_3}}',
                    'KEEMPAT: Keputusan ini berlaku sejak tanggal ditetapkan.',
                    'Keputusan ini dibuat untuk menjadi pedoman dalam pelaksanaan kegiatan dan koordinasi di lingkungan RW 05 Desa Citeureup.',
                    'Apabila di kemudian hari terdapat kekeliruan dalam keputusan ini, maka akan dilakukan perbaikan sebagaimana mestinya.',
                ],
                'detail_label' => 'Keterangan Tambahan',
                'closing' => '',
            ],
        ];
    }
}

if (! function_exists('surat_template_profile')) {
    function surat_template_profile($jenisSurat): array
    {
        $profiles = surat_template_profiles();
        $jenisSurat = (string) $jenisSurat;

        return $profiles[$jenisSurat] ?? [
            'purpose_label' => 'Keperluan Surat',
            'lead' => 'Yang bertanda tangan di bawah ini, pengurus {{site_name}}, menerangkan bahwa:',
            'body' => [
                'Nama tersebut di atas tercatat sebagai warga {{site_name}} dan mengajukan {{jenis_surat_lower}} untuk keperluan {{keperluan}}.',
            ],
            'detail_label' => 'Keterangan Tambahan',
            'closing' => 'Demikian surat ini dibuat agar dapat dipergunakan sebagaimana mestinya.',
        ];
    }
}

if (! function_exists('surat_type_field_definitions')) {
    function surat_type_field_definitions(): array
    {
        return [
            'Surat Pengantar' => [
                ['key' => 'tujuan_surat', 'label' => 'Kepada Yth.', 'placeholder' => 'Contoh: Kepala Desa Citeureup / pihak tujuan'],
                ['key' => 'tempat_tanggal_lahir', 'label' => 'Tempat/Tanggal Lahir', 'placeholder' => 'Contoh: Bandung, 01 Januari 1990', 'required' => true],
            ],
            'Surat Keterangan' => [
                ['key' => 'tujuan_surat', 'label' => 'Kepada Yth.', 'placeholder' => 'Contoh: Instansi tujuan bila ada'],
                ['key' => 'nik', 'label' => 'NIK', 'placeholder' => 'Isi bila diminta pengurus/instansi'],
                ['key' => 'keterangan', 'label' => 'Keterangan', 'placeholder' => 'Contoh: benar berdomisili di wilayah RW 05', 'type' => 'textarea', 'rows' => 3, 'full' => true, 'required' => true],
            ],
            'Surat Undangan' => [
                ['key' => 'tujuan_surat', 'label' => 'Kepada Yth.', 'placeholder' => 'Contoh: Bapak/Ibu/Saudara/i Warga RW 05'],
                ['key' => 'hari_tanggal', 'label' => 'Hari/Tanggal', 'placeholder' => 'Contoh: Sabtu, 10 Juli 2026', 'required' => true],
                ['key' => 'waktu', 'label' => 'Waktu', 'placeholder' => 'Contoh: 19.30 WIB', 'required' => true],
                ['key' => 'tempat', 'label' => 'Tempat', 'placeholder' => 'Contoh: Balai RW 05', 'required' => true],
                ['key' => 'agenda', 'label' => 'Agenda', 'placeholder' => 'Contoh: Rapat koordinasi warga', 'type' => 'textarea', 'rows' => 3, 'full' => true, 'required' => true],
            ],
            'Surat Edaran / Pemberitahuan' => [
                ['key' => 'tujuan_surat', 'label' => 'Kepada Yth.', 'placeholder' => 'Contoh: Bapak/Ibu/Saudara/i Warga RW 05'],
                ['key' => 'kegiatan_informasi', 'label' => 'Kegiatan/Informasi', 'placeholder' => 'Contoh: Kerja bakti lingkungan', 'required' => true],
                ['key' => 'hari_tanggal', 'label' => 'Hari/Tanggal', 'placeholder' => 'Contoh: Minggu, 11 Juli 2026'],
                ['key' => 'waktu', 'label' => 'Waktu', 'placeholder' => 'Contoh: 07.00 WIB'],
                ['key' => 'tempat', 'label' => 'Tempat', 'placeholder' => 'Contoh: Area RW 05'],
                ['key' => 'keterangan', 'label' => 'Keterangan', 'placeholder' => 'Isi pemberitahuan lengkap', 'type' => 'textarea', 'rows' => 3, 'full' => true, 'required' => true],
            ],
            'Surat Permohonan' => [
                ['key' => 'tujuan_surat', 'label' => 'Kepada Yth.', 'placeholder' => 'Contoh: Kepala Desa / dinas / pihak terkait', 'required' => true],
                ['key' => 'jenis_permohonan', 'label' => 'Jenis Permohonan', 'placeholder' => 'Contoh: Bantuan sarana lingkungan', 'required' => true],
                ['key' => 'kegiatan_keperluan', 'label' => 'Kegiatan/Keperluan', 'placeholder' => 'Contoh: Pengadaan lampu jalan', 'required' => true],
                ['key' => 'waktu_pelaksanaan', 'label' => 'Waktu Pelaksanaan', 'placeholder' => 'Contoh: Juli 2026'],
                ['key' => 'lokasi', 'label' => 'Lokasi', 'placeholder' => 'Contoh: Wilayah RT 01 RW 05'],
            ],
            'Surat Tugas / Mandat' => [
                ['key' => 'tujuan_surat', 'label' => 'Kepada Yth.', 'placeholder' => 'Contoh: Pihak terkait bila ada'],
                ['key' => 'nama_ditugaskan', 'label' => 'Nama', 'placeholder' => 'Nama penerima tugas', 'required' => true],
                ['key' => 'jabatan', 'label' => 'Jabatan', 'placeholder' => 'Contoh: Ketua RT / Seksi Keamanan', 'required' => true],
                ['key' => 'alamat_tugas', 'label' => 'Alamat', 'placeholder' => 'Alamat penerima tugas'],
                ['key' => 'tugas', 'label' => 'Tugas', 'placeholder' => 'Uraikan tugas/mandat yang diberikan', 'type' => 'textarea', 'rows' => 3, 'full' => true, 'required' => true],
                ['key' => 'masa_tugas', 'label' => 'Masa Tugas', 'placeholder' => 'Contoh: 10-15 Juli 2026', 'required' => true],
            ],
            'Berita Acara' => [
                ['key' => 'tujuan_surat', 'label' => 'Kepada Yth.', 'placeholder' => 'Biasanya untuk Arsip RW 05 Desa Citeureup'],
                ['key' => 'hari_tanggal', 'label' => 'Hari/Tanggal', 'placeholder' => 'Contoh: Jumat, 03 Juli 2026', 'required' => true],
                ['key' => 'tempat', 'label' => 'Tempat', 'placeholder' => 'Contoh: Balai RW 05', 'required' => true],
                ['key' => 'nama_kegiatan', 'label' => 'Nama Kegiatan', 'placeholder' => 'Contoh: Musyawarah warga', 'required' => true],
                ['key' => 'agenda', 'label' => 'Agenda', 'placeholder' => 'Agenda kegiatan/musyawarah', 'required' => true],
                ['key' => 'peserta', 'label' => 'Peserta', 'placeholder' => 'Contoh: Pengurus RW, Ketua RT, perwakilan warga', 'required' => true],
                ['key' => 'pimpinan', 'label' => 'Pimpinan', 'placeholder' => 'Nama pimpinan rapat/kegiatan', 'required' => true],
                ['key' => 'hasil_1', 'label' => 'Hasil 1', 'placeholder' => 'Poin hasil pertama', 'type' => 'textarea', 'rows' => 2, 'full' => true],
                ['key' => 'hasil_2', 'label' => 'Hasil 2', 'placeholder' => 'Poin hasil kedua', 'type' => 'textarea', 'rows' => 2, 'full' => true],
                ['key' => 'hasil_3', 'label' => 'Hasil 3', 'placeholder' => 'Poin hasil ketiga', 'type' => 'textarea', 'rows' => 2, 'full' => true],
            ],
            'Surat Keputusan Ketua RW 05' => [
                ['key' => 'tujuan_surat', 'label' => 'Kepada Yth.', 'placeholder' => 'Biasanya untuk Arsip RW 05 Desa Citeureup'],
                ['key' => 'pokok_keputusan', 'label' => 'Pokok Keputusan', 'placeholder' => 'Contoh: Pembentukan panitia kegiatan', 'required' => true],
                ['key' => 'keputusan_1', 'label' => 'KESATU', 'placeholder' => 'Isi keputusan pertama', 'type' => 'textarea', 'rows' => 2, 'full' => true, 'required' => true],
                ['key' => 'keputusan_2', 'label' => 'KEDUA', 'placeholder' => 'Isi keputusan kedua', 'type' => 'textarea', 'rows' => 2, 'full' => true],
                ['key' => 'keputusan_3', 'label' => 'KETIGA', 'placeholder' => 'Isi keputusan ketiga', 'type' => 'textarea', 'rows' => 2, 'full' => true],
            ],
        ];
    }
}

if (! function_exists('surat_type_fields')) {
    function surat_type_fields($jenisSurat): array
    {
        $definitions = surat_type_field_definitions();
        $jenisSurat = (string) $jenisSurat;

        return $definitions[$jenisSurat] ?? [];
    }
}

if (! function_exists('surat_request_data_normalize')) {
    function surat_request_data_normalize($jenisSurat, $payload): array
    {
        $payload = is_array($payload) ? $payload : [];
        $normalized = [];

        foreach (surat_type_fields($jenisSurat) as $field) {
            $key = (string) ($field['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $value = trim((string) ($payload[$key] ?? ''));
            $limit = (($field['type'] ?? 'text') === 'textarea') ? 1200 : 255;
            $normalized[$key] = mb_substr($value, 0, $limit);
        }

        return $normalized;
    }
}

if (! function_exists('surat_request_data_decode')) {
    function surat_request_data_decode($payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (! is_string($payload) || trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : [];
    }
}

if (! function_exists('surat_request_data_entries')) {
    function surat_request_data_entries($jenisSurat, $payload): array
    {
        $payload = surat_request_data_decode($payload);
        $entries = [];

        foreach (surat_type_fields($jenisSurat) as $field) {
            $key = (string) ($field['key'] ?? '');
            $value = trim((string) ($payload[$key] ?? ''));

            if ($key === '' || $value === '') {
                continue;
            }

            if (($field['type'] ?? '') === 'date') {
                $value = fmt_date($value);
            }

            $entries[] = [
                'key' => $key,
                'label' => (string) ($field['label'] ?? ucfirst(str_replace('_', ' ', $key))),
                'value' => $value,
            ];
        }

        return $entries;
    }
}

if (! function_exists('surat_type_detail_guidance_map')) {
    function surat_type_detail_guidance_map(): array
    {
        return [
            'Surat Pengantar' => 'Isi data warga dan keperluan administrasi. Bagian Kepada Yth. boleh dikosongkan bila belum tahu instansi tujuan.',
            'Surat Keterangan' => 'Tulis keterangan yang perlu diterangkan oleh RW. NIK boleh diisi jika memang diminta pengurus atau instansi tujuan.',
            'Surat Undangan' => 'Isi hari/tanggal, waktu, tempat, dan agenda kegiatan agar undangan siap dicetak.',
            'Surat Edaran / Pemberitahuan' => 'Isi nama kegiatan/informasi, jadwal, tempat, dan keterangan pemberitahuan untuk warga.',
            'Surat Permohonan' => 'Isi pihak tujuan, jenis permohonan, kebutuhan, waktu, dan lokasi yang dimohonkan.',
            'Surat Tugas / Mandat' => 'Isi penerima tugas, jabatan, uraian tugas, dan masa tugas yang diberikan Ketua RW.',
            'Berita Acara' => 'Isi data kegiatan/musyawarah, peserta, pimpinan, dan poin hasil yang akan dicatat sebagai berita acara.',
            'Surat Keputusan Ketua RW 05' => 'Isi pokok keputusan dan butir KESATU, KEDUA, serta KETIGA sesuai hasil musyawarah atau kebutuhan organisasi RW.',
        ];
    }
}

if (! function_exists('surat_type_detail_guidance')) {
    function surat_type_detail_guidance($jenisSurat): string
    {
        $guidanceMap = surat_type_detail_guidance_map();
        $jenisSurat = (string) $jenisSurat;

        return $guidanceMap[$jenisSurat] ?? 'Tulis keterangan tambahan yang benar-benar dibutuhkan agar pengurus bisa menyiapkan isi surat dengan tepat.';
    }
}

if (! function_exists('surat_status_options')) {
    function surat_status_options(): array
    {
        return [
            'menunggu' => 'Menunggu',
            'diproses' => 'Diproses',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'selesai' => 'Selesai',
        ];
    }
}

if (! function_exists('surat_status_label')) {
    function surat_status_label($status): string
    {
        $status = (string) $status;
        $options = surat_status_options();

        return $options[$status] ?? ucfirst($status);
    }
}

if (! function_exists('warga_status_tinggal_options')) {
    function warga_status_tinggal_options(): array
    {
        return [
            'tetap' => 'Tetap',
            'kontrak' => 'Kontrak',
            'kos' => 'Kos',
            'pindah' => 'Pindah',
        ];
    }
}

if (! function_exists('warga_kesejahteraan_options')) {
    function warga_kesejahteraan_options(): array
    {
        return [
            'umum' => 'Umum',
            'rentan' => 'Rentan',
            'kurang_mampu' => 'Kurang Mampu',
            'sangat_kurang_mampu' => 'Sangat Kurang Mampu',
        ];
    }
}

if (! function_exists('warga_bantuan_options')) {
    function warga_bantuan_options(): array
    {
        return [
            'tidak' => 'Tidak',
            'ya' => 'Ya',
        ];
    }
}

if (! function_exists('warga_csv_columns')) {
    function warga_csv_columns(): array
    {
        return [
            'nama_kepala_keluarga',
            'rt',
            'alamat',
            'jumlah_anggota',
            'no_hp',
            'pekerjaan_kepala_keluarga',
            'status_tinggal',
            'kategori_kesejahteraan',
            'penerima_bantuan',
            'jenis_bantuan',
            'kondisi_khusus',
            'keterangan',
        ];
    }
}

if (! function_exists('warga_csv_notes')) {
    function warga_csv_notes(): array
    {
        return [
            'Kolom wajib minimal: nama_kepala_keluarga dan rt.',
            'status_tinggal diisi salah satu dari: tetap, kontrak, kos, pindah.',
            'kategori_kesejahteraan diisi salah satu dari: umum, rentan, kurang_mampu, sangat_kurang_mampu.',
            'penerima_bantuan diisi ya atau tidak. Jika ya, kolom jenis_bantuan sebaiknya diisi.',
            'Hindari menyimpan NIK, nomor KK, foto KTP, atau dokumen sensitif lain di dataset ini.',
        ];
    }
}

if (! function_exists('warga_option_label')) {
    function warga_option_label(array $options, $value): string
    {
        $value = (string) $value;

        return $options[$value] ?? ($value !== '' ? ucfirst(str_replace('_', ' ', $value)) : '-');
    }
}

if (! function_exists('edukasi_topic_definitions')) {
    function edukasi_topic_definitions(): array
    {
        return [
            'ibu-anak' => [
                'number' => '01',
                'title' => 'Posyandu, Ibu & Anak',
                'description' => 'Materi untuk mendampingi kesehatan ibu, bayi, balita, dan pemantauan tumbuh kembang bersama kader Posyandu.',
                'items' => ['Kesehatan ibu sejak masa kehamilan', 'Peran Posyandu dan pemantauan pertumbuhan', 'Dukungan keluarga untuk ibu dan anak'],
            ],
            'gizi-stunting' => [
                'number' => '02',
                'title' => 'Gizi Keluarga & Stunting',
                'description' => 'Panduan awal untuk memahami peran pola asuh, makanan bergizi, dan pemantauan pertumbuhan anak.',
                'items' => ['Pola asuh yang mendukung tumbuh kembang', 'Kebiasaan makan bergizi dalam keluarga', 'Pentingnya pemantauan pertumbuhan'],
            ],
            'lansia' => [
                'number' => '03',
                'title' => 'Lansia & Posbindu',
                'description' => 'Materi pendampingan untuk membantu warga lanjut usia tetap aktif, terhubung, dan memperoleh informasi kesehatan yang tepat.',
                'items' => ['Aktivitas sehat bagi lansia', 'Dukungan keluarga dan lingkungan', 'Pemanfaatan kegiatan Posbindu'],
            ],
            'remaja' => [
                'number' => '04',
                'title' => 'Remaja & Kesehatan Mental',
                'description' => 'Edukasi untuk membangun kepedulian, komunikasi yang sehat, dan dukungan keluarga bagi remaja.',
                'items' => ['Mengenali pentingnya kesehatan mental', 'Membangun komunikasi yang saling menghargai', 'Mengetahui kapan perlu mencari bantuan'],
            ],
            'hidup-sehat' => [
                'number' => '05',
                'title' => 'Pola Hidup Sehat',
                'description' => 'Inspirasi kebiasaan sederhana untuk bergerak aktif serta menjaga kesehatan diri dan lingkungan.',
                'items' => ['Aktivitas fisik sesuai kemampuan', 'Kebiasaan hidup bersih dan sehat', 'Pemeriksaan kesehatan secara berkala'],
            ],
            'rokok-narkoba' => [
                'number' => '06',
                'title' => 'Pencegahan Rokok & Narkoba',
                'description' => 'Materi keluarga untuk membangun lingkungan yang saling menjaga dari rokok dan penyalahgunaan narkoba.',
                'items' => ['Risiko rokok bagi diri dan keluarga', 'Pencegahan sejak usia remaja', 'Peran keluarga dan lingkungan bebas asap rokok'],
            ],
        ];
    }
}

if (! function_exists('edukasi_category_options')) {
    function edukasi_category_options(): array
    {
        $options = [];
        foreach (edukasi_topic_definitions() as $key => $topic) {
            $options[$key] = $topic['number'] . ' — ' . $topic['title'];
        }

        return $options;
    }
}

if (! function_exists('edukasi_type_options')) {
    function edukasi_type_options(): array
    {
        return [
            'poster' => 'Poster',
            'video' => 'Video',
            'artikel' => 'Artikel',
        ];
    }
}

if (! function_exists('edukasi_status_options')) {
    function edukasi_status_options(): array
    {
        return [
            'publish' => 'Tayang',
            'draft' => 'Draft',
        ];
    }
}

if (! function_exists('edukasi_material_public_url')) {
    function edukasi_material_public_url(array $material): string
    {
        $filePath = trim((string) ($material['file_path'] ?? ''));
        if ($filePath !== '') {
            return base_url(ltrim(str_replace('\\', '/', $filePath), '/'));
        }

        $url = trim((string) ($material['tautan'] ?? ''));

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
    }
}

if (! function_exists('edukasi_material_action_label')) {
    function edukasi_material_action_label($type, bool $hasFile = false): string
    {
        return match ((string) $type) {
            'poster' => 'Lihat Poster',
            'video' => 'Tonton Video',
            'artikel' => $hasFile ? 'Buka Artikel PDF' : 'Baca Artikel',
            default => 'Buka Materi',
        };
    }
}

if (! function_exists('ensure_edukasi_materi_table')) {
    function ensure_edukasi_materi_table($db = null): bool
    {
        $db = $db ?: db_connect();

        try {
            $tableExisted = $db->tableExists('edukasi_materi');
            $db->query(
                "CREATE TABLE IF NOT EXISTS edukasi_materi (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    kategori VARCHAR(40) NOT NULL,
                    jenis VARCHAR(20) NOT NULL,
                    judul VARCHAR(180) NOT NULL,
                    ringkasan TEXT NULL,
                    penulis VARCHAR(160) NOT NULL,
                    institusi VARCHAR(160) NULL,
                    tahun VARCHAR(10) NULL,
                    tautan VARCHAR(500) NULL,
                    file_path VARCHAR(255) NULL,
                    urutan INT UNSIGNED NOT NULL DEFAULT 0,
                    status VARCHAR(20) NOT NULL DEFAULT 'draft',
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    KEY kategori_status (kategori, status),
                    KEY jenis (jenis),
                    KEY urutan (urutan),
                    KEY created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );

            if (! $tableExisted && $db->table('edukasi_materi')->countAllResults() === 0) {
                $seedRows = [
                    [
                        'kategori' => 'ibu-anak',
                        'jenis' => 'artikel',
                        'judul' => '1000 Hari Pertama Kehidupan',
                        'ringkasan' => 'Materi kesehatan ibu, bayi, dan balita dari Ayo Sehat.',
                        'penulis' => 'Kementerian Kesehatan RI',
                        'institusi' => 'Kementerian Kesehatan RI',
                        'tautan' => 'https://ayosehat.kemkes.go.id/1000-hari-pertama-kehidupan',
                        'urutan' => 10,
                        'status' => 'publish',
                    ],
                    [
                        'kategori' => 'ibu-anak',
                        'jenis' => 'video',
                        'judul' => 'Video Posyandu, Ibu & Anak',
                        'ringkasan' => 'Pilihan video edukasi pada kanal resmi Kementerian Kesehatan RI.',
                        'penulis' => 'Kementerian Kesehatan RI',
                        'institusi' => 'Kementerian Kesehatan RI',
                        'tautan' => 'https://www.youtube.com/@KementerianKesehatanRI/search?query=posyandu%20ibu%20dan%20anak',
                        'urutan' => 20,
                        'status' => 'publish',
                    ],
                    [
                        'kategori' => 'ibu-anak',
                        'jenis' => 'poster',
                        'judul' => 'Dukung Tumbuh Kembang Anak',
                        'ringkasan' => 'Poster edukasi untuk mendukung pertumbuhan dan perkembangan anak.',
                        'penulis' => 'Rai Nurani, S.Kep., Ners., M.Kep.',
                        'institusi' => 'STIKes Dharma Husada',
                        'file_path' => 'assets/poster-tumbuh-kembang-anak.png',
                        'urutan' => 30,
                        'status' => 'publish',
                    ],
                    [
                        'kategori' => 'gizi-stunting',
                        'jenis' => 'artikel',
                        'judul' => 'Cegah Stunting dengan Pola Asuh yang Baik',
                        'ringkasan' => 'Materi pola asuh dan pencegahan stunting dari Ayo Sehat.',
                        'penulis' => 'Kementerian Kesehatan RI',
                        'institusi' => 'Kementerian Kesehatan RI',
                        'tautan' => 'https://ayosehat.kemkes.go.id/cegah-stunting-dengan-pola-asuh-yang-baik',
                        'urutan' => 10,
                        'status' => 'publish',
                    ],
                    [
                        'kategori' => 'gizi-stunting',
                        'jenis' => 'video',
                        'judul' => 'Video Gizi Anak & Stunting',
                        'ringkasan' => 'Pilihan video edukasi pada kanal resmi Kementerian Kesehatan RI.',
                        'penulis' => 'Kementerian Kesehatan RI',
                        'institusi' => 'Kementerian Kesehatan RI',
                        'tautan' => 'https://www.youtube.com/@KementerianKesehatanRI/search?query=stunting%20gizi%20anak',
                        'urutan' => 20,
                        'status' => 'publish',
                    ],
                    [
                        'kategori' => 'lansia',
                        'jenis' => 'artikel',
                        'judul' => 'Hari Lanjut Usia Nasional',
                        'ringkasan' => 'Informasi dan edukasi kesehatan lansia dari Ayo Sehat.',
                        'penulis' => 'Kementerian Kesehatan RI',
                        'institusi' => 'Kementerian Kesehatan RI',
                        'tautan' => 'https://ayosehat.kemkes.go.id/agenda-kegiatan/hari-lanjut-usia-nasional',
                        'urutan' => 10,
                        'status' => 'publish',
                    ],
                    [
                        'kategori' => 'lansia',
                        'jenis' => 'video',
                        'judul' => 'Video Lansia & Posbindu',
                        'ringkasan' => 'Pilihan video edukasi pada kanal resmi Kementerian Kesehatan RI.',
                        'penulis' => 'Kementerian Kesehatan RI',
                        'institusi' => 'Kementerian Kesehatan RI',
                        'tautan' => 'https://www.youtube.com/@KementerianKesehatanRI/search?query=lansia%20posbindu',
                        'urutan' => 20,
                        'status' => 'publish',
                    ],
                    [
                        'kategori' => 'remaja',
                        'jenis' => 'artikel',
                        'judul' => 'Gangguan Kesehatan Mental',
                        'ringkasan' => 'Materi pengenalan kesehatan mental dari Ayo Sehat.',
                        'penulis' => 'Kementerian Kesehatan RI',
                        'institusi' => 'Kementerian Kesehatan RI',
                        'tautan' => 'https://ayosehat.kemkes.go.id/gangguan-kesehatan-mental',
                        'urutan' => 10,
                        'status' => 'publish',
                    ],
                    [
                        'kategori' => 'remaja',
                        'jenis' => 'video',
                        'judul' => 'Video Kesehatan Mental Remaja',
                        'ringkasan' => 'Pilihan video edukasi pada kanal resmi Kementerian Kesehatan RI.',
                        'penulis' => 'Kementerian Kesehatan RI',
                        'institusi' => 'Kementerian Kesehatan RI',
                        'tautan' => 'https://www.youtube.com/@KementerianKesehatanRI/search?query=kesehatan%20mental%20remaja',
                        'urutan' => 20,
                        'status' => 'publish',
                    ],
                    [
                        'kategori' => 'hidup-sehat',
                        'jenis' => 'artikel',
                        'judul' => 'Menjaga Kesehatan Jantung dengan Aktivitas Fisik',
                        'ringkasan' => 'Materi aktivitas fisik dan kesehatan jantung dari Ayo Sehat.',
                        'penulis' => 'Kementerian Kesehatan RI',
                        'institusi' => 'Kementerian Kesehatan RI',
                        'tautan' => 'https://ayosehat.kemkes.go.id/cara-menjaga-kesehatan-jantung',
                        'urutan' => 10,
                        'status' => 'publish',
                    ],
                    [
                        'kategori' => 'hidup-sehat',
                        'jenis' => 'video',
                        'judul' => 'Video GERMAS & Aktivitas Fisik',
                        'ringkasan' => 'Pilihan video edukasi pada kanal resmi Kementerian Kesehatan RI.',
                        'penulis' => 'Kementerian Kesehatan RI',
                        'institusi' => 'Kementerian Kesehatan RI',
                        'tautan' => 'https://www.youtube.com/@KementerianKesehatanRI/search?query=GERMAS%20aktivitas%20fisik',
                        'urutan' => 20,
                        'status' => 'publish',
                    ],
                    [
                        'kategori' => 'rokok-narkoba',
                        'jenis' => 'artikel',
                        'judul' => 'Rokok Membuat Hidup Jadi Redup',
                        'ringkasan' => 'Materi pencegahan dampak rokok dari Ayo Sehat.',
                        'penulis' => 'Kementerian Kesehatan RI',
                        'institusi' => 'Kementerian Kesehatan RI',
                        'tautan' => 'https://ayosehat.kemkes.go.id/rokok-membuat-hidup-jadi-redup',
                        'urutan' => 10,
                        'status' => 'publish',
                    ],
                    [
                        'kategori' => 'rokok-narkoba',
                        'jenis' => 'video',
                        'judul' => 'Video Pencegahan Rokok & Narkoba',
                        'ringkasan' => 'Pilihan video edukasi pada kanal resmi Kementerian Kesehatan RI.',
                        'penulis' => 'Kementerian Kesehatan RI',
                        'institusi' => 'Kementerian Kesehatan RI',
                        'tautan' => 'https://www.youtube.com/@KementerianKesehatanRI/search?query=rokok%20narkoba',
                        'urutan' => 20,
                        'status' => 'publish',
                    ],
                ];
                $seedDefaults = [
                    'ringkasan' => '',
                    'institusi' => '',
                    'tahun' => '',
                    'tautan' => '',
                    'file_path' => '',
                    'urutan' => 0,
                    'status' => 'draft',
                ];
                $db->table('edukasi_materi')->insertBatch(array_map(
                    static fn (array $row): array => array_merge($seedDefaults, $row),
                    $seedRows
                ));
            }

            return true;
        } catch (Throwable $exception) {
            log_message('error', 'Gagal menyiapkan tabel edukasi_materi: ' . $exception->getMessage());

            return false;
        }
    }
}

if (! function_exists('ensure_pengajuan_surat_table')) {
    function ensure_pengajuan_surat_table($db = null): bool
    {
        $db = $db ?: db_connect();

        try {
            $db->query(
                "CREATE TABLE IF NOT EXISTS pengajuan_surat (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    kode_pengajuan VARCHAR(32) NOT NULL,
                    jenis_surat VARCHAR(120) NOT NULL,
                    keperluan VARCHAR(180) NOT NULL,
                    nama VARCHAR(120) NOT NULL,
                    no_hp VARCHAR(40) NOT NULL,
                    rt VARCHAR(20) NOT NULL,
                    alamat VARCHAR(255) NOT NULL,
                    detail TEXT NULL,
                    detail_json LONGTEXT NULL,
                    lampiran_catatan TEXT NULL,
                    status VARCHAR(30) NOT NULL DEFAULT 'menunggu',
                    nomor_surat VARCHAR(100) NULL,
                    catatan_admin TEXT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY kode_pengajuan (kode_pengajuan),
                    KEY status (status),
                    KEY jenis_surat (jenis_surat),
                    KEY created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );

            $detailJsonColumn = $db->query("SHOW COLUMNS FROM pengajuan_surat LIKE 'detail_json'")->getRowArray();
            if (! $detailJsonColumn) {
                $db->query('ALTER TABLE pengajuan_surat ADD COLUMN detail_json LONGTEXT NULL AFTER detail');
            }

            return true;
        } catch (Throwable $exception) {
            log_message('error', 'Gagal menyiapkan tabel pengajuan_surat: ' . $exception->getMessage());

            return false;
        }
    }
}

if (! function_exists('ensure_warga_table')) {
    function ensure_warga_table($db = null): bool
    {
        $db = $db ?: db_connect();

        try {
            $db->query(
                "CREATE TABLE IF NOT EXISTS warga (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    nama_kepala_keluarga VARCHAR(120) NOT NULL,
                    rt VARCHAR(20) NOT NULL,
                    alamat VARCHAR(255) NULL,
                    jumlah_anggota INT UNSIGNED NOT NULL DEFAULT 1,
                    no_hp VARCHAR(40) NULL,
                    pekerjaan_kepala_keluarga VARCHAR(120) NULL,
                    status_tinggal VARCHAR(20) NOT NULL DEFAULT 'tetap',
                    kategori_kesejahteraan VARCHAR(40) NOT NULL DEFAULT 'umum',
                    penerima_bantuan VARCHAR(10) NOT NULL DEFAULT 'tidak',
                    jenis_bantuan VARCHAR(255) NULL,
                    kondisi_khusus TEXT NULL,
                    keterangan TEXT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    KEY rt (rt),
                    KEY status_tinggal (status_tinggal),
                    KEY kategori_kesejahteraan (kategori_kesejahteraan),
                    KEY penerima_bantuan (penerima_bantuan)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );

            $missingColumns = [
                'pekerjaan_kepala_keluarga' => "ALTER TABLE warga ADD COLUMN pekerjaan_kepala_keluarga VARCHAR(120) NULL AFTER no_hp",
                'kategori_kesejahteraan' => "ALTER TABLE warga ADD COLUMN kategori_kesejahteraan VARCHAR(40) NOT NULL DEFAULT 'umum' AFTER status_tinggal",
                'penerima_bantuan' => "ALTER TABLE warga ADD COLUMN penerima_bantuan VARCHAR(10) NOT NULL DEFAULT 'tidak' AFTER kategori_kesejahteraan",
                'jenis_bantuan' => "ALTER TABLE warga ADD COLUMN jenis_bantuan VARCHAR(255) NULL AFTER penerima_bantuan",
                'kondisi_khusus' => "ALTER TABLE warga ADD COLUMN kondisi_khusus TEXT NULL AFTER jenis_bantuan",
                'created_at' => "ALTER TABLE warga ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER keterangan",
                'updated_at' => "ALTER TABLE warga ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
            ];

            foreach ($missingColumns as $column => $sql) {
                $exists = $db->query("SHOW COLUMNS FROM warga LIKE '" . $db->escapeString($column) . "'")->getRowArray();
                if (! $exists) {
                    $db->query($sql);
                }
            }

            $db->query("UPDATE warga SET status_tinggal='tetap' WHERE status_tinggal IS NULL OR status_tinggal = ''");
            $db->query("UPDATE warga SET kategori_kesejahteraan='umum' WHERE kategori_kesejahteraan IS NULL OR kategori_kesejahteraan = ''");
            $db->query("UPDATE warga SET penerima_bantuan='tidak' WHERE penerima_bantuan IS NULL OR penerima_bantuan = ''");

            foreach ($db->table('warga')->select('id, rt, jumlah_anggota')->get()->getResultArray() as $row) {
                $updates = [];
                $normalizedRt = normalize_rt_code($row['rt'] ?? '');
                if ($normalizedRt !== '' && $normalizedRt !== (string) ($row['rt'] ?? '')) {
                    $updates['rt'] = $normalizedRt;
                }

                $jumlahAnggota = max(1, (int) ($row['jumlah_anggota'] ?? 1));
                if ($jumlahAnggota !== (int) ($row['jumlah_anggota'] ?? 0)) {
                    $updates['jumlah_anggota'] = $jumlahAnggota;
                }

                if ($updates !== []) {
                    $db->table('warga')->where('id', (int) $row['id'])->update($updates);
                }
            }

            return true;
        } catch (Throwable $exception) {
            log_message('error', 'Gagal menyiapkan tabel warga: ' . $exception->getMessage());

            return false;
        }
    }
}

if (! function_exists('ensure_admin_users_table')) {
    function ensure_admin_users_table($db = null): bool
    {
        $db = $db ?: db_connect();

        try {
            $db->query(
                "CREATE TABLE IF NOT EXISTS admin_users (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    nama VARCHAR(120) NOT NULL,
                    username VARCHAR(80) NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    role VARCHAR(40) NOT NULL DEFAULT 'admin',
                    status VARCHAR(20) NOT NULL DEFAULT 'aktif',
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY username (username),
                    KEY status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );

            $missingColumns = [
                'role' => "ALTER TABLE admin_users ADD COLUMN role VARCHAR(40) NOT NULL DEFAULT 'admin' AFTER password_hash",
                'status' => "ALTER TABLE admin_users ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'aktif' AFTER role",
                'created_at' => "ALTER TABLE admin_users ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER status",
                'updated_at' => "ALTER TABLE admin_users ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
            ];

            foreach ($missingColumns as $column => $sql) {
                $exists = $db->query("SHOW COLUMNS FROM admin_users LIKE '" . $db->escapeString($column) . "'")->getRowArray();
                if (! $exists) {
                    $db->query($sql);
                }
            }

            $db->query("UPDATE admin_users SET role='admin' WHERE role IS NULL OR role = ''");
            $db->query("UPDATE admin_users SET status='aktif' WHERE status IS NULL OR status = ''");

            return true;
        } catch (Throwable $exception) {
            log_message('error', 'Gagal menyiapkan tabel admin_users: ' . $exception->getMessage());

            return false;
        }
    }
}

if (! function_exists('admin_role_options')) {
    function admin_role_options(): array
    {
        return [
            'superadmin' => 'Super Admin',
            'admin' => 'Admin Umum',
            'ketua_rw' => 'Ketua RW',
            'sekretaris' => 'Sekretaris',
            'bendahara' => 'Bendahara',
            'operator' => 'Operator Data',
        ];
    }
}

if (! function_exists('normalize_admin_username')) {
    function normalize_admin_username($username): string
    {
        $username = strtolower(trim((string) $username));
        $username = preg_replace('/\s+/', '_', $username);
        $username = preg_replace('/[^a-z0-9._-]+/', '', $username);
        $username = preg_replace('/_+/', '_', $username);

        return trim($username, '._-');
    }
}

if (! function_exists('ensure_keuangan_transaksi_table')) {
    function ensure_keuangan_transaksi_table($db = null): bool
    {
        $db = $db ?: db_connect();

        try {
            $db->query(
                "CREATE TABLE IF NOT EXISTS keuangan_transaksi (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    tanggal DATE NOT NULL,
                    lingkup VARCHAR(30) NOT NULL DEFAULT 'rw',
                    rt VARCHAR(20) NULL,
                    jenis VARCHAR(20) NOT NULL DEFAULT 'pemasukan',
                    kategori VARCHAR(120) NOT NULL,
                    nominal BIGINT UNSIGNED NOT NULL DEFAULT 0,
                    keterangan TEXT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    KEY tanggal (tanggal),
                    KEY lingkup (lingkup),
                    KEY rt (rt),
                    KEY jenis (jenis)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );

            try {
                $db->query("ALTER TABLE keuangan_transaksi MODIFY lingkup VARCHAR(30) NOT NULL DEFAULT 'rw'");
            } catch (Throwable $exception) {
                log_message('debug', 'Kolom lingkup keuangan_transaksi tidak perlu diubah atau gagal diubah: ' . $exception->getMessage());
            }

            foreach ($db->table('keuangan_transaksi')->select('id, rt, lingkup')->where('lingkup', 'rt')->get()->getResultArray() as $row) {
                $normalizedRt = normalize_rt_code($row['rt'] ?? '');
                if ($normalizedRt !== '' && $normalizedRt !== (string) ($row['rt'] ?? '')) {
                    $db->table('keuangan_transaksi')->where('id', (int) $row['id'])->update(['rt' => $normalizedRt]);
                }
            }

            return true;
        } catch (Throwable $exception) {
            log_message('error', 'Gagal menyiapkan tabel keuangan_transaksi: ' . $exception->getMessage());

            return false;
        }
    }
}
