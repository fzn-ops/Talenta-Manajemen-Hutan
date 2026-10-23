<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #fcfcfb; margin: 0; padding: 40px 0;">
    <div style="max-width: 500px; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
        
        <!-- Judul -->
        <h2 style="color: #152c5b; text-align: center; font-size: 24px; margin-bottom: 20px;">
            Atur Ulang Password
        </h2>

        <!-- Deskripsi -->
        <p style="color: #5c6b89; font-size: 14px; line-height: 1.6; margin-bottom: 10px;">
            Halo!
        </p>
        <p style="color: #5c6b89; font-size: 14px; line-height: 1.6; margin-bottom: 30px;">
            Anda menerima email ini karena kami menerima permintaan pengaturan ulang password untuk akun Anda. Klik tombol di bawah ini untuk melanjutkan:
        </p>

        <!-- Tombol Aksi -->
        <div style="text-align: center; margin-bottom: 35px;">
            <a href="{{ $url }}" style="background-color: #4b857a; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 15px; display: inline-block; box-shadow: 0 4px 10px rgba(75, 133, 122, 0.3);">
                Reset Password Sekarang
            </a>
        </div>

        <p style="color: #8c9bb5; font-size: 12.5px; line-height: 1.5; margin-bottom: 20px;">
            Jika Anda tidak merasa melakukan permintaan ini, abaikan saja email ini; tidak ada tindakan lebih lanjut yang diperlukan.
        </p>

        <hr style="border: none; border-top: 1px solid #eaeaea; margin: 25px 0;">

        <!-- Footer -->
        <p style="color: #5c6b89; font-size: 13px; text-align: center; margin: 0;">
            Salam hangat,<br>
            <strong style="color: #152c5b;">{{ config('app.name') }}</strong>
        </p>

    </div>
</body>
</html>