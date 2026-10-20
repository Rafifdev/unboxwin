<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Putar Roda Keberuntungan - Spin Wheel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Canvas Confetti -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <style>
        :root {
            --primary: #4f46e5;
            --accent: #f59e0b;
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.9);
            --border-color: rgba(255, 255, 255, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: radial-gradient(circle at center, #1e1b4b 0%, #0f172a 70%, #050811 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px;
            color: #f8fafc;
            overflow-x: hidden;
            position: relative;
        }

        .header-info {
            text-align: center;
            margin-bottom: 20px;
            z-index: 2;
        }
        .header-info h1 {
            font-size: 26px;
            font-weight: 800;
            background: linear-gradient(135deg, #f59e0b, #ec4899, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }
        .header-info p {
            font-size: 13px;
            color: #94a3b8;
        }
        .user-tag {
            display: inline-block;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            color: #cbd5e1;
            margin-top: 6px;
        }

        /* Wheel Container */
        .wheel-wrapper {
            position: relative;
            width: 360px;
            height: 360px;
            max-width: 90vw;
            max-height: 90vw;
            margin: 10px auto;
            z-index: 2;
        }

        /* Outer Glow Ring */
        .wheel-wrapper::before {
            content: '';
            position: absolute;
            top: -12px;
            left: -12px;
            right: -12px;
            bottom: -12px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b, #ec4899, #6366f1, #10b981);
            z-index: -1;
            box-shadow: 0 0 40px rgba(245, 158, 11, 0.4);
            animation: pulseGlow 3s ease-in-out infinite alternate;
        }
        @keyframes pulseGlow {
            0% { transform: scale(0.99); opacity: 0.8; }
            100% { transform: scale(1.02); opacity: 1; filter: blur(2px); }
        }

        #wheelCanvas {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6);
            cursor: pointer;
        }

        /* Pointer Marker at the Top */
        .pointer {
            position: absolute;
            top: -18px;
            left: 50%;
            transform: translateX(-50%);
            width: 34px;
            height: 44px;
            z-index: 10;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.5));
        }
        .pointer svg {
            width: 100%;
            height: 100%;
        }

        /* Spin Center Hub Button */
        .center-hub {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: 4px solid #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.5), inset 0 2px 4px rgba(255,255,255,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 800;
            color: #fff;
            cursor: pointer;
            z-index: 8;
            transition: all 0.2s;
            user-select: none;
            letter-spacing: 0.5px;
        }
        .center-hub:hover:not(.disabled) {
            transform: translate(-50%, -50%) scale(1.08);
            box-shadow: 0 0 25px rgba(245, 158, 11, 0.8);
        }
        .center-hub.disabled {
            background: #64748b;
            cursor: not-allowed;
            border-color: #94a3b8;
        }

        /* Bottom Action Button */
        .btn-action-spin {
            margin-top: 25px;
            background: linear-gradient(135deg, #f59e0b, #ea580c);
            color: #fff;
            border: none;
            padding: 14px 45px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 30px;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.5);
            transition: all 0.3s;
            z-index: 2;
            letter-spacing: 0.5px;
        }
        .btn-action-spin:hover:not(:disabled) {
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 15px 30px -5px rgba(245, 158, 11, 0.7);
        }
        .btn-action-spin:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Modal Hadiah Popup */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 15px;
            animation: fadeIn 0.4s ease;
        }

        .modal-card {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            max-width: 480px;
            width: 100%;
            padding: 30px 25px;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7);
            position: relative;
            animation: popUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes popUp {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }

        .trophy-badge {
            width: 75px;
            height: 75px;
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            border-radius: 50%;
            margin: -60px auto 15px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            color: #fff;
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.6);
            border: 4px solid #1e293b;
        }

        .prize-title-congrats {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #f59e0b;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .prize-won-name {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .urgent-timer-box {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 12px;
            color: #fca5a5;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .claim-form {
            text-align: left;
        }
        .claim-form .form-group {
            margin-bottom: 14px;
        }
        .claim-form label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 5px;
        }
        .claim-form .form-control {
            width: 100%;
            padding: 10px 12px;
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            color: #fff;
            font-size: 13px;
        }
        .claim-form .form-control:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
        }
        .claim-form textarea.form-control {
            resize: vertical;
            min-height: 70px;
        }

        .btn-claim-wa {
            width: 100%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 18px;
            transition: all 0.3s;
        }
        .btn-claim-wa:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(16, 185, 129, 0.6);
        }
        .btn-claim-wa:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

    <div class="header-info">
        <h1>RODA KEBERUNTUNGAN</h1>
        <p>Putar roda dan menangkan hadiah eksklusif hari ini!</p>
        <div class="user-tag">
            <i class="fa fa-user"></i> <?= htmlspecialchars($session_data['nama_pemenang']); ?> 
            &nbsp;|&nbsp; <i class="fa fa-shopping-bag"></i> <?= htmlspecialchars($session_data['no_pesanan']); ?>
        </div>
    </div>

    <div class="wheel-wrapper">
        <div class="pointer">
            <svg viewBox="0 0 34 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M17 44L0 12C0 5.37258 7.61116 0 17 0C26.3888 0 34 5.37258 34 12L17 44Z" fill="#F59E0B"/>
                <path d="M17 38L4 12C4 7 9.8 3 17 3C24.2 3 30 7 30 12L17 38Z" fill="#DC2626"/>
            </svg>
        </div>
        <canvas id="wheelCanvas" width="700" height="700"></canvas>
        <div class="center-hub" id="centerHubBtn">SPIN</div>
    </div>

    <button class="btn-action-spin" id="btnActionSpin">
        <i class="fa fa-play"></i> PUTAR SEKARANG
    </button>

    <!-- Modal Hadiah Popup -->
    <div class="modal-overlay" id="modalReward">
        <div class="modal-card">
            <div class="trophy-badge">
                <i class="fa fa-trophy"></i>
            </div>
            <div class="prize-title-congrats">SELAMAT! ANDA MEMENANGKAN</div>
            <div class="prize-won-name" id="displayPrizeName">-</div>

            <div class="urgent-timer-box">
                <i class="fa fa-clock-o fa-lg"></i>
                <div>Batas Waktu Klaim: <strong><span id="displayBatasWaktu">2x24 Jam</span></strong></div>
            </div>

            <form class="claim-form" id="formClaimReward">
                <input type="hidden" name="history_id" id="claimHistoryId" value="">
                
                <div class="form-group">
                    <label>Nama Penerima</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($session_data['nama_pemenang']); ?>" readonly style="background: rgba(15, 23, 42, 0.4);">
                </div>

                <div class="form-group">
                    <label>No. WhatsApp</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($session_data['no_whatsapp']); ?>" readonly style="background: rgba(15, 23, 42, 0.4);">
                </div>

                <div class="form-group">
                    <label>No. Pesanan</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($session_data['no_pesanan']); ?>" readonly style="background: rgba(15, 23, 42, 0.4);">
                </div>

                <div class="form-group">
                    <label>Alamat Lengkap Pengiriman <span style="color:#ef4444;">*</span></label>
                    <textarea class="form-control" name="alamat_pengiriman" id="alamat_pengiriman" placeholder="Tuliskan jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota/kabupaten, dan kode pos..." required></textarea>
                </div>

                <button type="submit" class="btn-claim-wa" id="btnClaimSubmit">
                    <i class="fa fa-whatsapp fa-lg"></i>
                    <span>Klaim Hadiah via WhatsApp</span>
                </button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Data Hadiah dari Database
        var prizes = <?= json_encode(array_values($prizes)); ?>;
        var numPrizes = prizes.length;
        var canvas = document.getElementById('wheelCanvas');
        var ctx = canvas.getContext('2d');
        var arc = Math.PI * 2 / numPrizes;

        var startAngle = 0;
        var isSpinning = false;
        var spinTimeout = null;

        // Draw Wheel Function
        function drawWheel() {
            var outsideRadius = 330;
            var textRadius = 240;
            var insideRadius = 75;

            ctx.clearRect(0, 0, 700, 700);

            for (var i = 0; i < numPrizes; i++) {
                var angle = startAngle + i * arc;
                var color = prizes[i].warna_slice || (i % 2 === 0 ? '#3b82f6' : '#10b981');

                // Slice path
                ctx.fillStyle = color;
                ctx.beginPath();
                ctx.arc(350, 350, outsideRadius, angle, angle + arc, false);
                ctx.arc(350, 350, insideRadius, angle + arc, angle, true);
                ctx.fill();

                // Slice border
                ctx.strokeStyle = 'rgba(255, 255, 255, 0.3)';
                ctx.lineWidth = 2;
                ctx.stroke();

                // Draw Slice Text
                ctx.save();
                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 22px Poppins, Arial';
                ctx.shadowColor = 'rgba(0, 0, 0, 0.7)';
                ctx.shadowBlur = 6;
                ctx.translate(
                    350 + Math.cos(angle + arc / 2) * textRadius,
                    350 + Math.sin(angle + arc / 2) * textRadius
                );
                ctx.rotate(angle + arc / 2 + Math.PI / 2);

                var text = prizes[i].nama_hadiah;
                if (text.length > 14) {
                    text = text.substring(0, 12) + '...';
                }
                ctx.fillText(text, -ctx.measureText(text).width / 2, 0);
                ctx.restore();
            }

            // Outer border ring
            ctx.strokeStyle = '#f59e0b';
            ctx.lineWidth = 14;
            ctx.beginPath();
            ctx.arc(350, 350, outsideRadius, 0, Math.PI * 2, false);
            ctx.stroke();
        }

        drawWheel();

        // Trigger Spin Function
        function startSpin() {
            if (isSpinning) return;
            isSpinning = true;

            $('#btnActionSpin, #centerHubBtn').addClass('disabled').prop('disabled', true);

            // Request pemenang ke backend
            $.ajax({
                url: '<?= site_url("spin/play_spin"); ?>',
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    if (!res.success) {
                        alert(res.message);
                        isSpinning = false;
                        $('#btnActionSpin, #centerHubBtn').removeClass('disabled').prop('disabled', false);
                        return;
                    }

                    // Hitung sudut putar agar berhenti tepat di slice target
                    // Pointer berada di atas (jam 12 = sudut -Math.PI / 2 atau 270 deg = 3 * Math.PI / 2)
                    var targetIndex = res.target_index;
                    var sliceAngle = Math.PI * 2 / numPrizes;

                    // Sudut tengah slice target saat startAngle = 0
                    var sliceCenter = (targetIndex * sliceAngle) + (sliceAngle / 2);

                    // Posisi pointer adalah 3 * PI / 2 (top = 270 deg)
                    // We want: (startAngle + sliceCenter) % (2*PI) = 3 * PI / 2
                    var desiredEndAngle = (3 * Math.PI / 2) - sliceCenter;

                    // Buat putaran banyak (antara 6 sampai 8 putaran penuh)
                    var totalRotations = 7 * (Math.PI * 2);
                    var currentNormalized = startAngle % (Math.PI * 2);
                    var targetTotalAngle = startAngle + totalRotations + (desiredEndAngle - currentNormalized);
                    if (targetTotalAngle < startAngle + totalRotations) {
                        targetTotalAngle += (Math.PI * 2);
                    }

                    var spinTime = 6500; // 6.5 detik
                    var startTime = null;
                    var initialAngle = startAngle;

                    function animateSpin(timestamp) {
                        if (!startTime) startTime = timestamp;
                        var progress = (timestamp - startTime) / spinTime;

                        if (progress < 1) {
                            // Easing out cubic: 1 - Math.pow(1 - progress, 3)
                            var ease = 1 - Math.pow(1 - progress, 4);
                            startAngle = initialAngle + (targetTotalAngle - initialAngle) * ease;
                            drawWheel();
                            requestAnimationFrame(animateSpin);
                        } else {
                            startAngle = targetTotalAngle;
                            drawWheel();
                            isSpinning = false;

                            // Efek Confetti Kemenangan!
                            confetti({
                                particleCount: 120,
                                spread: 80,
                                origin: { y: 0.6 }
                            });

                            // Buka Modal Klaim
                            setTimeout(function() {
                                $('#displayPrizeName').text(res.prize_name);
                                $('#displayBatasWaktu').text(res.batas_waktu_klaim);
                                $('#claimHistoryId').val(res.history_id);
                                $('#modalReward').css('display', 'flex').hide().fadeIn(300);
                            }, 600);
                        }
                    }

                    requestAnimationFrame(animateSpin);
                },
                error: function() {
                    alert('Gagal terhubung ke server saat memutar roda. Silakan coba kembali.');
                    isSpinning = false;
                    $('#btnActionSpin, #centerHubBtn').removeClass('disabled').prop('disabled', false);
                }
            });
        }

        $('#btnActionSpin, #centerHubBtn').on('click', startSpin);

        // Submit Klaim Hadiah
        $('#formClaimReward').on('submit', function(e) {
            e.preventDefault();

            var $btn = $('#btnClaimSubmit');
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan Alamat...');

            $.ajax({
                url: '<?= site_url("spin/claim_prize"); ?>',
                type: 'POST',
                dataType: 'json',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        $btn.html('<i class="fa fa-check"></i> Mengalihkan ke WhatsApp...');
                        setTimeout(function() {
                            window.location.href = res.wa_url;
                        }, 700);
                    } else {
                        alert(res.message);
                        $btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat memproses klaim. Silakan coba kembali.');
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });
    </script>
</body>
</html>
