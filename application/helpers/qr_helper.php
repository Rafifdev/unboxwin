<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * QR Code Helper
 * Generates crisp SVG / Data-URI QR codes using TCPDF QR engine
 */

if (!function_exists('generate_qr_svg')) {
    function generate_qr_svg($text, $size = 140) {
        $tcpdf_qr_path = APPPATH . 'libraries/htmlpdf/_tcpdf_5.0.002/qrcode.php';
        if (file_exists($tcpdf_qr_path)) {
            require_once $tcpdf_qr_path;
        }

        if (!class_exists('QRcode')) {
            // Fallback to online image if class not found
            return '<img src="https://api.qrserver.com/v1/create-qr-code/?size='.$size.'x'.$size.'&data='.urlencode($text).'" width="'.$size.'" height="'.$size.'" alt="QR Code"/>';
        }

        try {
            $qrcode = new QRcode($text, 'M');
            $arr = $qrcode->getBarcodeArray();
            $rows = $arr['num_rows'];
            $cols = $arr['num_cols'];
            $scale = $size / $cols;

            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 '.$size.' '.$size.'">';
            $svg .= '<rect width="100%" height="100%" fill="#ffffff"/>';
            for ($r = 0; $r < $rows; $r++) {
                for ($c = 0; $c < $cols; $c++) {
                    if ($arr['bcode'][$r][$c] == 1) {
                        $x = round($c * $scale, 2);
                        $y = round($r * $scale, 2);
                        $w = ceil($scale);
                        $h = ceil($scale);
                        $svg .= '<rect x="'.$x.'" y="'.$y.'" width="'.$w.'" height="'.$h.'" fill="#000000"/>';
                    }
                }
            }
            $svg .= '</svg>';
            return $svg;
        } catch (Exception $e) {
            return '<img src="https://api.qrserver.com/v1/create-qr-code/?size='.$size.'x'.$size.'&data='.urlencode($text).'" width="'.$size.'" height="'.$size.'" alt="QR Code"/>';
        }
    }
}
