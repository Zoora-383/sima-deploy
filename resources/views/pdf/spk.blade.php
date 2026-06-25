<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Perintah Kerja (SPK) - {{ $spk->nomor_spk }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, Baskerville, Georgia, serif;
            font-size: 12.5px;
            color: #111111;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .header-container {
            position: relative;
            margin-bottom: 20px;
            border-bottom: 3px double #000000;
            padding-bottom: 12px;
            min-height: 85px;
        }
        .header-logo {
            position: absolute;
            left: 5px;
            top: 2px;
            width: 75px;
            height: auto;
        }
        .header-text {
            text-align: center;
            margin-left: 95px;
            margin-right: 95px;
        }
        .header-title-large {
            font-size: 13.5px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }
        .header-title-medium {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 4px 0;
            color: #1a365d;
        }
        .header-subtitle {
            font-size: 9.5px;
            font-style: italic;
            margin: 0;
            color: #333333;
            line-height: 1.4;
        }
        .document-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        .document-number {
            text-align: center;
            font-size: 12px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        .intro-paragraph {
            text-align: justify;
            margin-bottom: 20px;
            text-indent: 30px;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1a365d;
            border-bottom: 1px solid #cbd5e0;
            padding-bottom: 3px;
            margin-top: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.info-table td {
            padding: 6px 4px;
            vertical-align: top;
        }
        table.info-table td.label {
            width: 30%;
            font-weight: bold;
            color: #4a5568;
        }
        table.info-table td.colon {
            width: 2%;
            text-align: center;
        }
        table.info-table td.value {
            width: 68%;
        }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.items-table th {
            background-color: #f7fafc;
            border: 1px solid #cbd5e0;
            padding: 8px;
            font-weight: bold;
            text-align: left;
            color: #2d3748;
        }
        table.items-table td {
            border: 1px solid #cbd5e0;
            padding: 8px;
        }
        .terms-paragraph {
            text-align: justify;
            font-style: italic;
            color: #4a5568;
            margin-top: 15px;
            margin-bottom: 30px;
        }
        .signature-container {
            width: 100%;
            margin-top: 40px;
        }
        .signature-box {
            width: 45%;
            float: left;
            text-align: center;
        }
        .signature-box.right {
            float: right;
        }
        .signature-role {
            font-weight: bold;
            margin-bottom: 60px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .signature-nip {
            font-size: 10px;
            color: #718096;
        }
        .clearfix {
            clear: both;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT (LETTERHEAD) -->
    <div class="header-container">
        <img class="header-logo" src="https://upload.wikimedia.org/wikipedia/commons/4/46/Lambang_baru_UNJ.png" alt="Logo UNJ">
        <div class="header-text">
            <div class="header-title-large">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</div>
            <div class="header-title-medium">UNIVERSITAS NEGERI JAKARTA</div>
            <div class="header-title-large" style="margin-bottom: 5px;">UPT TEKNOLOGI INFORMASI DAN KOMUNIKASI (UPT TIK)</div>
            <div class="header-subtitle">Gedung UPT TIK (ex Bakhum), Kampus A Universitas Negeri Jakarta, Jalan Rawamangun Muka Selatan, RT.9/RW.12, Rawamangun, Kecamatan Pulo Gadung, Kota Jakarta Timur, Daerah Khusus Ibukota Jakarta 13220</div>
            <div class="header-subtitle" style="margin-top: 2px;">Surel: upt-tik@unj.ac.id | Laman: unj.ac.id</div>
        </div>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="document-title">SURAT PERINTAH KERJA (SPK)</div>
    <div class="document-number">Nomor: {{ $spk->nomor_spk }}</div>

    <!-- PARAGRAF PEMBUKA -->
    <div class="intro-paragraph">
        Berdasarkan pengajuan permohonan pemeliharaan dengan nomor <strong>{{ $spk->maintenance->nomor_pengajuan }}</strong> dan setelah melalui tahapan evaluasi kelayakan aset serta ketersediaan pagu anggaran, bersama ini Kepala UPT TIK memberikan Perintah Kerja kepada unit/pihak pelaksana terkait untuk melakukan tindakan perbaikan dan pemeliharaan dengan ketentuan teknis sebagai berikut:
    </div>

    <!-- DETAIL SPK -->
    <div class="section-title">I. KETENTUAN UMUM PEKERJAAN</div>
    <table class="info-table">
        <tr>
            <td class="label">Nama Pekerjaan</td>
            <td class="colon">:</td>
            <td class="value">{{ $spk->maintenance->title }}</td>
        </tr>
        <tr>
            <td class="label">Kategori / Unit Aset</td>
            <td class="colon">:</td>
            <td class="value">{{ $spk->maintenance->item->name }} ({{ $spk->maintenance->item->category->name ?? '-' }})</td>
        </tr>
        <tr>
            <td class="label">Sifat Pekerjaan</td>
            <td class="colon">:</td>
            <td class="value">{{ ucfirst($spk->maintenance->type) }} (Prioritas: {{ ucfirst($spk->maintenance->priority) }})</td>
        </tr>
        <tr>
            <td class="label">Tanggal Mulai Kerja</td>
            <td class="colon">:</td>
            <td class="value">{{ \Carbon\Carbon::parse($spk->tanggal_mulai_efektif)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Target Selesai Kerja</td>
            <td class="colon">:</td>
            <td class="value">{{ \Carbon\Carbon::parse($spk->tanggal_selesai_target)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Pagu Anggaran Disetujui</td>
            <td class="colon">:</td>
            <td class="value"><strong>Rp {{ number_format($spk->pagu_anggaran_disetujui, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <!-- ITEM DETAIL -->
    @if($spk->maintenance->maintenanceItems && $spk->maintenance->maintenanceItems->count() > 0)
    <div class="section-title">II. RINCIAN KEBUTUHAN PERBAIKAN</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 8%; text-align: center;">No.</th>
                <th style="width: 47%;">Nama Item/Kebutuhan</th>
                <th style="width: 15%; text-align: center;">Qty</th>
                <th style="width: 30%; text-align: right;">Estimasi Biaya</th>
            </tr>
        </thead>
        <tbody>
            @foreach($spk->maintenance->maintenanceItems as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $item->nama_item }}</td>
                <td style="text-align: center;">{{ $item->qty ?? '-' }} {{ $item->satuan }}</td>
                <td style="text-align: right;">Rp {{ number_format($item->estimasi_biaya_satuan * ($item->qty ?? 1), 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- KETERANGAN PARAGRAF (LOREM IPSUM) -->
    <div class="section-title">III. SYARAT DAN KETENTUAN PEKERJAAN</div>
    <div class="terms-paragraph">
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Pekerjaan wajib dilaksanakan secara bertanggung jawab, tepat waktu, dan melaporkan realisasi pengeluaran riil setelah pekerjaan diselesaikan untuk proses pembuatan laporan rekapitulasi maintenance.
    </div>

    <!-- TANDA TANGAN -->
    <div class="signature-container">
        <div class="signature-box">
            <div>Yang Mengajukan,</div>
            <div class="signature-role">Admin Pemeliharaan</div>
            <div class="signature-name">{{ $spk->maintenance->requester->userProfile->fullname ?? 'Admin Staff' }}</div>
            <div class="signature-nip">Staf Administrasi Inventaris</div>
        </div>
        <div class="signature-box right">
            <div>Menyetujui/Mengetahui,</div>
            <div class="signature-role">Kepala UPT TIK</div>
            <div class="signature-name">{{ $approverName }}</div>
            <div class="signature-nip">NIP. 198008122005011002</div>
        </div>
        <div class="clearfix"></div>
    </div>

</body>
</html>
