<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat Penyelesaian - {{ $roadmap->nama_roadmap }}</title>
    <style>
        @page {
            size: 297mm 210mm;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            overflow: hidden;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            color: #17334F;
        }

        /* 1. Outer Deep Navy Border (Exact 10mm Margin from Page Edges) */
        .frame-outer {
            position: absolute;
            top: 10mm;
            left: 10mm;
            width: 277mm;
            height: 190mm;
            border: 3.5px solid #17334F;
            z-index: 1;
        }

        /* 2. Inner Classic Gold Border (Exact 13mm Margin from Page Edges) */
        .frame-inner-gold {
            position: absolute;
            top: 13mm;
            left: 13mm;
            width: 271mm;
            height: 184mm;
            border: 1.5px solid #C5A059;
            z-index: 2;
        }

        /* 3. Subtle Inner Accent Line (Exact 15mm Margin from Page Edges) */
        .frame-inner-subtle {
            position: absolute;
            top: 15mm;
            left: 15mm;
            width: 267mm;
            height: 180mm;
            border: 0.5px solid #e2e8f0;
            z-index: 3;
        }

        /* 4. Elegant Corner Accents */
        .corner {
            position: absolute;
            width: 12mm;
            height: 12mm;
            border: 2px solid #C5A059;
            z-index: 4;
        }
        .corner-tl { top: 16mm; left: 16mm; border-right: none; border-bottom: none; }
        .corner-tr { top: 16mm; right: 16mm; border-left: none; border-bottom: none; }
        .corner-bl { bottom: 16mm; left: 16mm; border-right: none; border-top: none; }
        .corner-br { bottom: 16mm; right: 16mm; border-left: none; border-top: none; }

        /* 5. Master Content Container (Perfect Symmetrical Margins: 25mm Left/Right, 20mm Top/Bottom) */
        .content-container {
            position: absolute;
            top: 19mm;
            left: 25mm;
            width: 247mm;
            height: 172mm;
            z-index: 5;
        }

        .master-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        /* ROW 1: HEADER (LOGO & DEPARTMENT TITLE) */
        .row-header {
            text-align: center;
            vertical-align: top;
            height: 25mm;
        }
        .logo-img {
            max-height: 15mm;
            max-width: 90mm;
            width: auto;
            display: block;
            margin: 0 auto 1.5mm auto;
        }
        .sub-inst-title {
            font-size: 7.5pt;
            font-weight: 700;
            color: #416f65;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
        }

        /* ROW 2: CERTIFICATE TITLE */
        .row-title {
            text-align: center;
            vertical-align: middle;
            height: 24mm;
        }
        .cert-main-title {
            font-size: 21pt;
            font-weight: 800;
            color: #17334F;
            text-transform: uppercase;
            letter-spacing: 3.5px;
            margin: 0;
            line-height: 1.1;
        }
        .cert-sub-title {
            font-size: 9pt;
            font-style: italic;
            color: #64748b;
            letter-spacing: 1px;
            margin: 1mm 0 0 0;
        }
        .cert-num {
            font-size: 7.5pt;
            color: #7188a3;
            letter-spacing: 1px;
            margin: 1.5mm 0 0 0;
        }

        /* ROW 3: RECIPIENT */
        .row-recipient {
            text-align: center;
            vertical-align: middle;
            height: 30mm;
        }
        .text-given-to {
            font-size: 9pt;
            color: #475569;
            margin: 0 0 1.5mm 0;
        }
        .recipient-name {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 21pt;
            font-weight: bold;
            color: #183669;
            letter-spacing: 1px;
            margin: 0 auto;
            padding-bottom: 1.5mm;
            border-bottom: 2px solid #C5A059;
            display: inline-block;
            min-width: 120mm;
            max-width: 220mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .recipient-meta {
            font-size: 9pt;
            font-weight: 600;
            color: #64748b;
            margin: 1.5mm 0 0 0;
            letter-spacing: 0.5px;
        }

        /* ROW 4: ACHIEVEMENT REASON */
        .row-achievement {
            text-align: center;
            vertical-align: middle;
            height: 28mm;
        }
        .text-achievement-reason {
            font-size: 8.5pt;
            color: #475569;
            line-height: 1.4;
            max-width: 210mm;
            margin: 0 auto 1.5mm auto;
        }
        .text-roadmap-title {
            font-size: 13pt;
            font-weight: 800;
            color: #17334F;
            margin: 1mm auto;
            max-width: 210mm;
            line-height: 1.25;
        }
        .badge-category {
            display: inline-block;
            background-color: #416f65;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 1mm 3.5mm;
            border-radius: 2mm;
            margin-top: 1mm;
        }

        /* ROW 5: FOOTER & SIGNATURES */
        .row-footer {
            height: 44mm;
            vertical-align: bottom;
            padding-bottom: 1mm;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .footer-col-left {
            width: 50%;
            text-align: left;
            vertical-align: bottom;
            padding-left: 2mm;
        }
        .footer-col-right {
            width: 50%;
            text-align: right;
            vertical-align: bottom;
            padding-right: 2mm;
        }

        /* Verification Badge Box */
        .verification-box {
            display: inline-block;
            border: 1px dashed #416f65;
            background-color: #f7faf9;
            padding: 2.5mm 4mm;
            border-radius: 2mm;
            text-align: left;
        }
        .v-box-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #416f65;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5mm;
        }
        .v-box-meta {
            font-size: 7pt;
            color: #64748b;
            line-height: 1.35;
        }

        /* Signature Box */
        .sig-container {
            display: inline-block;
            text-align: center;
            width: 75mm;
        }
        .sig-date {
            font-size: 8pt;
            color: #475569;
            margin-bottom: 1mm;
        }
        .sig-title {
            font-size: 8pt;
            font-weight: bold;
            color: #17334F;
            line-height: 1.2;
        }
        .sig-dept {
            font-size: 7pt;
            color: #64748b;
            margin-bottom: 13mm; /* Space for signature */
        }
        .sig-divider {
            width: 58mm;
            border-bottom: 1px solid #17334F;
            margin: 0 auto 1mm auto;
        }
        .sig-officer-name {
            font-size: 8.5pt;
            font-weight: bold;
            color: #17334F;
        }
        .sig-officer-role {
            font-size: 7pt;
            color: #64748b;
        }
    </style>
</head>
<body>
    <!-- 1. Outer Deep Navy Border -->
    <div class="frame-outer"></div>

    <!-- 2. Inner Gold Border -->
    <div class="frame-inner-gold"></div>

    <!-- 3. Subtle Inner Accent Line -->
    <div class="frame-inner-subtle"></div>

    <!-- 4. Corner Ornaments -->
    <div class="corner corner-tl"></div>
    <div class="corner corner-tr"></div>
    <div class="corner corner-bl"></div>
    <div class="corner corner-br"></div>

    <!-- 5. Content Container (Centered Perfectly on Page) -->
    <div class="content-container">
        <table class="master-table">
            <!-- 1. Header Row -->
            <tr>
                <td class="row-header">
                    @if(!empty($logoBase64))
                        <img src="data:image/png;base64,{{ $logoBase64 }}" class="logo-img" alt="Logo Departemen Manajemen Hutan IPB" />
                    @endif
                    <div class="sub-inst-title">Talenta Manajemen Hutan &bull; Institut Pertanian Bogor</div>
                </td>
            </tr>

            <!-- 2. Title Row -->
            <tr>
                <td class="row-title">
                    <h1 class="cert-main-title">Sertifikat Penyelesaian</h1>
                    <div class="cert-sub-title">Certificate of Completion</div>
                    <div class="cert-num">Nomor: {{ $certificateNumber }}</div>
                </td>
            </tr>

            <!-- 3. Recipient Row -->
            <tr>
                <td class="row-recipient">
                    <div class="text-given-to">Sertifikat ini secara resmi diberikan kepada:</div>
                    <div class="recipient-name">{{ $user->nama ?? $user->name }}</div>
                    <div class="recipient-meta">NIM: {{ $user->nim ?? '-' }}</div>
                </td>
            </tr>

            <!-- 4. Achievement Reason Row -->
            <tr>
                <td class="row-achievement">
                    <div class="text-achievement-reason">
                        Atas kelulusan dan keberhasilannya dalam menyelesaikan seluruh rangkaian modul pembelajaran, evaluasi, serta penugasan aktivitas pada roadmap:
                    </div>
                    <div class="text-roadmap-title">
                        &ldquo;{{ $roadmap->nama_roadmap }}&rdquo;
                    </div>
                    <div>
                        <span class="badge-category">Kategori {{ ucfirst($roadmap->kategori ?? 'Profesional') }}</span>
                    </div>
                </td>
            </tr>

            <!-- 5. Footer & Signatures Row -->
            <tr>
                <td class="row-footer">
                    <table class="footer-table">
                        <tr>
                            <!-- Left: Verification Info -->
                            <td class="footer-col-left">
                                <div class="verification-box">
                                    <div class="v-box-title">SERTIFIKAT TERVERIFIKASI RESMI</div>
                                    <div class="v-box-meta">
                                        Diterbitkan: {{ \Carbon\Carbon::parse($completionDate)->translatedFormat('d F Y') }}<br>
                                        Sistem Informasi Talenta Mahasiswa<br>
                                        Departemen Manajemen Hutan IPB University
                                    </div>
                                </div>
                            </td>

                            <!-- Right: Department Chair Signature -->
                            <td class="footer-col-right">
                                <div class="sig-container">
                                    <div class="sig-date">Bogor, {{ \Carbon\Carbon::parse($completionDate)->translatedFormat('d F Y') }}</div>
                                    <div class="sig-title">Ketua Departemen Manajemen Hutan</div>
                                    <div class="sig-dept">Fakultas Kehutanan dan Lingkungan IPB</div>
                                    <div class="sig-divider"></div>
                                    <div class="sig-officer-name">Dr. Ir. I Nengah Surati Jaya, M.Agr.</div>
                                    <div class="sig-officer-role">Departemen Manajemen Hutan IPB</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
