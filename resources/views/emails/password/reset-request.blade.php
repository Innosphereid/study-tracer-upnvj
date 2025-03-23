<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password UPN Veteran Jakarta Tracer Study</title>
    <style>
    /* Base */
    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f5f5f5;
        color: #333;
        line-height: 1.6;
    }

    .email-container {
        max-width: 600px;
        margin: 0 auto;
        background-color: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    /* Header */
    .email-header {
        background: linear-gradient(135deg, #3949ab 0%, #d32f2f 100%);
        padding: 30px 20px;
        text-align: center;
    }

    .logo {
        width: 120px;
        margin-bottom: 15px;
    }

    .header-title {
        color: #ffffff;
        font-size: 24px;
        font-weight: 700;
        margin: 0;
        line-height: 1.3;
    }

    .header-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 16px;
        margin: 5px 0 0;
    }

    /* Content */
    .email-content {
        padding: 30px;
    }

    .greeting {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .message {
        margin-bottom: 25px;
        color: #4b5563;
    }

    .instructions {
        margin-bottom: 15px;
        font-weight: 500;
    }

    .otp-container {
        background-color: #f3f4f6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        margin: 20px 0;
        border: 1px solid #e5e7eb;
    }

    .otp-code {
        font-family: 'Courier New', monospace;
        font-size: 32px;
        font-weight: 700;
        letter-spacing: 5px;
        color: #3949ab;
    }

    .security-note {
        background-color: #fff8e6;
        border-left: 4px solid #fbbf24;
        padding: 12px 15px;
        margin: 20px 0;
        font-size: 14px;
        color: #92400e;
    }

    .additional-help {
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
        font-size: 14px;
        color: #6b7280;
    }

    /* Footer */
    .email-footer {
        background-color: #f9fafb;
        padding: 20px;
        text-align: center;
        font-size: 12px;
        color: #6b7280;
        border-top: 1px solid #e5e7eb;
    }

    .footer-address {
        margin: 10px 0;
    }

    .footer-note {
        font-style: italic;
        margin-top: 15px;
    }

    /* Button */
    .button {
        display: inline-block;
        background-color: #3949ab;
        color: white;
        padding: 12px 24px;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        margin: 15px 0;
        text-align: center;
    }

    /* Responsive */
    @media screen and (max-width: 600px) {
        .email-container {
            width: 100%;
            border-radius: 0;
        }

        .email-header,
        .email-content,
        .email-footer {
            padding: 20px 15px;
        }
    }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <img src="{{ asset('logo-upnvj.png') }}" alt="UPN Veteran Jakarta" class="logo">
            <h1 class="header-title">Permintaan Reset Password Anda</h1>
            <p class="header-subtitle">Satu langkah lagi untuk mengamankan akun Anda</p>
        </div>

        <!-- Content -->
        <div class="email-content">
            <p class="greeting">Hai {{ $name }},</p>

            <p class="message">
                Kami menerima permintaan untuk mereset password akun Tracer Study UPN Veteran Jakarta Anda.
                Jika Anda tidak meminta ini, silakan abaikan email ini.
            </p>

            <p class="instructions">Gunakan kode verifikasi berikut untuk melanjutkan proses reset password:</p>

            <div class="otp-container">
                <div class="otp-code">{{ $otp }}</div>
            </div>

            <div class="security-note">
                Kode ini hanya berlaku selama 10 menit dan hanya dapat digunakan sekali.
            </div>

            <p>
                Masukkan kode di atas pada halaman verifikasi yang terbuka di browser Anda. Jika Anda tidak berhasil
                menggunakan kode ini dalam 10 menit, silakan minta kode baru melalui sistem.
            </p>

            <div class="additional-help">
                <p>
                    Jika Anda memerlukan bantuan tambahan, silakan hubungi tim CDE UPN Veteran Jakarta melalui email
                    <a href="mailto:support@tracer.upnvj.ac.id">support@tracer.upnvj.ac.id</a>.
                </p>
                <p>
                    Terima kasih atas partisipasi Anda dalam meningkatkan kualitas pendidikan UPN Veteran Jakarta
                    melalui Tracer Study.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>&copy; UPN Veteran Jakarta - Tim CDE {{ date('Y') }}</p>
            <p class="footer-address">
                Jl. RS. Fatmawati Raya, Pd. Labu, Kec. Cilandak, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta
                12450
            </p>
            <p class="footer-note">
                Email ini dikirimkan secara otomatis, mohon tidak membalas email ini.
            </p>
        </div>
    </div>
</body>

</html>