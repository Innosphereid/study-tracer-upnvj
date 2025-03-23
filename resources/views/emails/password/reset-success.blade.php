<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Berhasil Direset - UPN Veteran Jakarta Tracer Study</title>
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

    .success-icon-container {
        text-align: center;
        margin: 20px 0;
    }

    .success-icon {
        width: 80px;
        height: 80px;
        background-color: #d1fae5;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }

    .success-icon svg {
        width: 40px;
        height: 40px;
        fill: #059669;
    }

    .success-message {
        font-size: 18px;
        font-weight: 600;
        color: #059669;
        margin-top: 10px;
    }

    .confirmation-details {
        background-color: #f3f4f6;
        border-radius: 8px;
        padding: 15px 20px;
        margin: 20px 0;
        color: #4b5563;
    }

    .security-section {
        margin-top: 30px;
        padding: 20px;
        border-radius: 8px;
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
    }

    .security-title {
        font-weight: 600;
        color: #1e40af;
        margin-top: 0;
        margin-bottom: 10px;
    }

    .security-tips {
        margin-top: 15px;
    }

    .security-tips ul {
        margin: 0;
        padding-left: 20px;
    }

    .security-tips li {
        margin-bottom: 5px;
    }

    .report-button {
        display: inline-block;
        background-color: #dc2626;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        margin: 15px 0;
        text-align: center;
    }

    .login-button {
        display: block;
        background-color: #3949ab;
        color: white;
        padding: 15px 30px;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        margin: 25px auto;
        text-align: center;
        max-width: 250px;
    }

    .closing {
        margin-top: 30px;
        border-top: 1px solid #e5e7eb;
        padding-top: 20px;
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

    .social-links {
        margin: 15px 0;
    }

    .social-link {
        display: inline-block;
        margin: 0 5px;
    }

    .social-link img {
        width: 24px;
        height: 24px;
    }

    .footer-note {
        font-style: italic;
        margin-top: 15px;
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

        .login-button {
            width: 100%;
            box-sizing: border-box;
        }
    }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <img src="{{ asset('logo-upnvj.png') }}" alt="UPN Veteran Jakarta" class="logo">
            <h1 class="header-title">Password Berhasil Diubah!</h1>
            <p class="header-subtitle">Akun Anda kini aman kembali</p>
        </div>

        <!-- Content -->
        <div class="email-content">
            <p class="greeting">Hai {{ $name }},</p>

            <div class="success-icon-container">
                <div class="success-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <p class="success-message">Password Berhasil Diubah!</p>
            </div>

            <p class="message">
                Selamat! Password akun Tracer Study UPN Veteran Jakarta Anda telah berhasil diubah pada
                {{ $reset_time->format('d F Y, H:i') }} WIB.
            </p>

            <div class="confirmation-details">
                <p>Anda sekarang dapat login menggunakan password baru Anda untuk mengakses sistem Tracer Study.</p>
            </div>

            <div class="security-section">
                <h3 class="security-title">Informasi Keamanan</h3>
                <p>
                    Jika Anda tidak melakukan perubahan ini, segera hubungi kami di
                    <a href="mailto:support@tracer.upnvj.ac.id">support@tracer.upnvj.ac.id</a>
                    atau klik tombol di bawah ini:
                </p>

                <a href="{{ url('/security/report') }}" class="report-button">Laporkan Aktivitas Mencurigakan</a>

                <div class="security-tips">
                    <p>Untuk keamanan akun, pastikan Anda:</p>
                    <ul>
                        <li>Tidak membagikan password ke siapapun</li>
                        <li>Gunakan password yang unik dan kuat</li>
                        <li>Lakukan perubahan password secara berkala</li>
                    </ul>
                </div>
            </div>

            <a href="{{ url('/login') }}" class="login-button">Login ke Sistem Tracer Study</a>

            <div class="closing">
                <p>
                    Terima kasih telah menjadi bagian dari ekosistem digital UPN Veteran Jakarta.
                    Data dan keamanan Anda adalah prioritas kami.
                </p>
                <p>
                    Bersama-sama membangun jejak karir alumni untuk kemajuan almamater.
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

            <div class="social-links">
                <a href="https://facebook.com/upnvj" class="social-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#4267B2">
                        <path
                            d="M12 2C6.477 2 2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.879V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.989C18.343 21.129 22 16.99 22 12c0-5.523-4.477-10-10-10z" />
                    </svg>
                </a>
                <a href="https://instagram.com/upnvj" class="social-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#E1306C">
                        <path
                            d="M12 2c2.717 0 3.056.01 4.122.06 1.065.05 1.79.217 2.428.465.66.254 1.216.598 1.772 1.153.509.5.902 1.105 1.153 1.772.247.637.415 1.363.465 2.428.047 1.066.06 1.405.06 4.122 0 2.717-.01 3.056-.06 4.122-.05 1.065-.218 1.79-.465 2.428a4.883 4.883 0 01-1.153 1.772c-.5.509-1.105.902-1.772 1.153-.637.247-1.363.415-2.428.465-1.066.047-1.405.06-4.122.06-2.717 0-3.056-.01-4.122-.06-1.065-.05-1.79-.218-2.428-.465a4.89 4.89 0 01-1.772-1.153 4.904 4.904 0 01-1.153-1.772c-.247-.637-.415-1.363-.465-2.428C2.013 15.056 2 14.717 2 12c0-2.717.01-3.056.06-4.122.05-1.066.217-1.79.465-2.428.247-.67.598-1.227 1.153-1.772A4.911 4.911 0 015.45 2.525c.638-.248 1.362-.415 2.428-.465C8.944 2.013 9.283 2 12 2zm0 1.802c-2.67 0-2.986.01-4.04.058-.976.045-1.505.207-1.858.344-.466.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.048 1.055-.058 1.37-.058 4.041 0 2.67.01 2.986.058 4.04.045.976.207 1.505.344 1.858.182.466.398.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058 2.67 0 2.987-.01 4.04-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041 0-2.67-.01-2.986-.058-4.04-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.055-.048-1.37-.058-4.041-.058zm0 3.063a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 8.468a3.333 3.333 0 100-6.666 3.333 3.333 0 000 6.666zm6.538-8.469a1.2 1.2 0 11-2.4 0 1.2 1.2 0 012.4 0z" />
                    </svg>
                </a>
                <a href="https://twitter.com/upnvj" class="social-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#1DA1F2">
                        <path
                            d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z" />
                    </svg>
                </a>
            </div>

            <p class="footer-note">
                Email ini dikirimkan secara otomatis, mohon tidak membalas email ini.
            </p>
        </div>
    </div>
</body>

</html>