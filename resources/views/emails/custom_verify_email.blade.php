<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email</title>
    <style>
        /* CSS Dasar untuk Email */
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 20px;
            color: #112340;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f4f9;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #183669; /* Warna biru aplikasi lu */
            margin: 0;
            font-size: 24px;
        }
        .btn {
            display: inline-block;
            background-color: #183669;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .link-text {
            word-break: break-all;
            color: #183669;
            font-size: 13px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #8c9eb5;
        }
    </style>
</head>
<body>

    <div class="email-container">
        <div class="header">
            <h1>Aplikasi Talenta</h1>
        </div>

        <p>Halo, <strong>{{ $nama }}</strong>!</p>
        
        <p>Terima kasih sudah melengkapi profil kamu. Tinggal satu langkah lagi nih! Silakan verifikasi alamat email ini untuk keamanan akun kamu dan agar bisa mulai mendaftar aktivitas.</p>
        
        <div style="text-align: center;">
            <!-- Tombol Verifikasi Utama -->
            <a href="{{ $url }}" class="btn">Verifikasi Email Saya</a>
        </div>

        <p style="font-size: 14px; margin-top: 20px;">
            Atau, salin dan tempel link berikut ke browser kamu jika tombol di atas tidak berfungsi:
        </p>
        <p>
            <a href="{{ $url }}" class="link-text">{{ $url }}</a>
        </p>

        <div class="footer">
            <p>Jika kamu tidak merasa mendaftar atau mengubah email di Aplikasi Talenta, abaikan saja pesan ini.</p>
            <p>&copy; {{ date('Y') }} Aplikasi Talenta. All rights reserved.</p>
        </div>
    </div>

</body>
</html>