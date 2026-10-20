<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Tokens - <?= htmlspecialchars($batch->batch_produksi); ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }
        body {
            background-color: #f4f6f9;
            color: #333;
            padding: 20px;
        }
        .header-bar {
            background: #fff;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-title h2 {
            font-size: 18px;
            margin-bottom: 4px;
        }
        .header-title p {
            font-size: 13px;
            color: #666;
        }
        .btn-print {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 8px 18px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-print:hover {
            background: #0056b3;
        }
        .token-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .token-card {
            background: #fff;
            border: 2px dashed #999;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            page-break-inside: avoid;
        }
        .token-card .batch-tag {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #555;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .token-card .qr-wrapper {
            margin: 8px auto;
            display: inline-block;
        }
        .token-card .prod-no {
            font-size: 14px;
            font-weight: bold;
            color: #111;
            margin-top: 4px;
        }
        .token-card .token-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            background: #eee;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            margin: 4px 0;
            color: #000;
            font-weight: bold;
        }
        .token-card .instructions {
            font-size: 11px;
            color: #e65100;
            font-weight: bold;
            margin-top: 4px;
        }
        .token-card .exp-date {
            font-size: 10px;
            color: #888;
            margin-top: 2px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .header-bar {
                display: none !important;
            }
            .token-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }
            .token-card {
                border: 1px dashed #444;
                padding: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="header-bar">
        <div class="header-title">
            <h2>Batch: <?= htmlspecialchars($batch->batch_produksi); ?> (<?= $with_spin ? 'Mode Spin Wheel' : 'Mode Validasi Standar'; ?>)</h2>
            <p>Total Token: <strong><?= count($tokens); ?></strong> | Rentang No: <strong>#<?= $batch->no_awal; ?> - #<?= $batch->no_akhir; ?></strong> | Berlaku s/d: <strong><?= date('d-m-Y', strtotime($batch->tgl_expired)); ?></strong></p>
        </div>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    </div>

    <div class="token-grid">
        <?php foreach ($tokens as $t): 
            $target_url = site_url('spin/index/' . $t->qr_token);
        ?>
            <div class="token-card">
                <div class="batch-tag"><?= htmlspecialchars($batch->batch_produksi); ?></div>
                <div class="qr-wrapper">
                    <?= generate_qr_svg($target_url, 120); ?>
                </div>
                <div class="prod-no">Produk No: #<?= $t->no_produk; ?></div>
                <div class="token-code"><?= $t->qr_token; ?></div>
                <div class="instructions">
                    <?= $with_spin ? '⚡ Scan QR untuk Main Spin Wheel' : '🔍 Scan QR untuk Cek Keaslian'; ?>
                </div>
                <div class="exp-date">Exp: <?= date('d M Y', strtotime($batch->tgl_expired)); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>
