<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Token & Spin Wheel - <?= get_option('site_name', 'Spin Wheel'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --accent: #f59e0b;
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.85);
            --border-color: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #311042 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-main);
            position: relative;
            overflow-x: hidden;
        }

        /* Decorative Background Elements */
        body::before, body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            z-index: 0;
            pointer-events: none;
        }
        body::before {
            width: 350px;
            height: 350px;
            background: rgba(99, 102, 241, 0.25);
            top: 10%;
            left: 10%;
        }
        body::after {
            width: 300px;
            height: 300px;
            background: rgba(244, 63, 94, 0.2);
            bottom: 10%;
            right: 10%;
        }

        .container {
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 1;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 35px 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #fff;
            margin: 0 auto 15px auto;
            box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.5);
        }

        .title {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
        }

        .subtitle {
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .alert-box {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 12px;
            line-height: 1.4;
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #6ee7b7;
        }

        .token-badge {
            background: rgba(99, 102, 241, 0.2);
            border: 1px solid rgba(99, 102, 241, 0.4);
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .token-badge .label {
            font-size: 12px;
            color: #a5b4fc;
        }
        .token-badge .code {
            font-family: monospace;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #cbd5e1;
            margin-bottom: 6px;
        }
        .form-group label span.required {
            color: #ef4444;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 15px;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px 12px 42px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            font-size: 14px;
            color: #fff;
            transition: all 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: #6366f1;
            background: rgba(15, 23, 42, 0.85);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }
        .form-control::placeholder {
            color: #64748b;
        }
        .help-text {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 25px;
        }
        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(99, 102, 241, 0.6);
        }
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .footer-note {
            text-align: center;
            font-size: 11px;
            color: #64748b;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="card">
            <div class="header-icon">
                <i class="fa fa-gift"></i>
            </div>
            <h1 class="title">Validasi Hadiah</h1>
            <p class="subtitle">Masukkan data pesanan Anda untuk membuka roda keberuntungan Spin Wheel.</p>

            <div id="alertContainer">
                <?php if (!empty($error_msg)): ?>
                    <div class="alert-box alert-danger">
                        <i class="fa fa-exclamation-circle fa-lg"></i>
                        <div><?= $error_msg; ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <form id="formVerification">
                <?php if (!empty($token_valid) && !empty($token_data)): ?>
                    <div class="token-badge">
                        <div>
                            <div class="label">KODE TOKEN TERDETEKSI</div>
                            <div class="code"><?= htmlspecialchars($token_data->qr_token); ?></div>
                        </div>
                        <i class="fa fa-check-circle text-success" style="font-size: 22px; color: #10b981;"></i>
                    </div>
                    <input type="hidden" name="qr_token" id="qr_token" value="<?= htmlspecialchars($token_data->qr_token); ?>">
                <?php else: ?>
                    <div class="form-group">
                        <label for="qr_token">Kode QR Token <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <i class="fa fa-qrcode"></i>
                            <input type="text" class="form-control" name="qr_token" id="qr_token" placeholder="Masukkan 16 digit token QR" value="<?= htmlspecialchars($qr_token); ?>" required>
                        </div>
                        <div class="help-text">Ditemukan pada stiker/kartu produk yang Anda terima.</div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="no_pesanan">Nomor Pesanan / Order ID <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fa fa-shopping-bag"></i>
                        <input type="text" class="form-control" name="no_pesanan" id="no_pesanan" placeholder="Contoh: 260812903741829" required>
                    </div>
                    <div class="help-text">Nomor invoice/pesanan dari marketplace (TikTok/Shopee/Tokopedia).</div>
                </div>

                <div class="form-group">
                    <label for="nama_pemenang">Nama Lengkap <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fa fa-user"></i>
                        <input type="text" class="form-control" name="nama_pemenang" id="nama_pemenang" placeholder="Nama lengkap Anda" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="no_whatsapp">Nomor WhatsApp Aktif <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fa fa-whatsapp"></i>
                        <input type="tel" class="form-control" name="no_whatsapp" id="no_whatsapp" placeholder="Contoh: 081234567890" required>
                    </div>
                    <div class="help-text">Digunakan untuk konfirmasi & klaim pengiriman hadiah.</div>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">
                    <span>Lanjut ke Spin Wheel</span>
                    <i class="fa fa-arrow-right"></i>
                </button>
            </form>

            <div class="footer-note">
                &copy; <?= date('Y'); ?> <?= get_option('site_name', 'Spin Wheel'); ?>. All rights reserved.
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#formVerification').on('submit', function(e) {
                e.preventDefault();

                var $btn = $('#btnSubmit');
                var originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memvalidasi Data...');

                $('#alertContainer').empty();

                $.ajax({
                    url: '<?= site_url("spin/submit_verification"); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            $('#alertContainer').html(
                                '<div class="alert-box alert-success">' +
                                '<i class="fa fa-check-circle fa-lg"></i>' +
                                '<div>' + res.message + '</div>' +
                                '</div>'
                            );
                            setTimeout(function() {
                                window.location.href = res.redirect;
                            }, 1000);
                        } else {
                            $('#alertContainer').html(
                                '<div class="alert-box alert-danger">' +
                                '<i class="fa fa-exclamation-circle fa-lg"></i>' +
                                '<div>' + res.message + '</div>' +
                                '</div>'
                            );
                            $btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function() {
                        $('#alertContainer').html(
                            '<div class="alert-box alert-danger">' +
                            '<i class="fa fa-exclamation-circle fa-lg"></i>' +
                            '<div>Terjadi gangguan koneksi ke server. Silakan coba kembali.</div>' +
                            '</div>'
                        );
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });
        });
    </script>
</body>
</html>
