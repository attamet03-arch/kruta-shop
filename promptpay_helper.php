<?php
// promptpay_helper.php - Thai PromptPay QR Code Payload Generator (EMVCo Standard)
// Generates standard payload string compatible with all Thai mobile banking apps

class PromptPayQR {
    private static function crc16($data) {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $x = (($crc >> 8) ^ ord($data[$i])) & 0xFF;
            $x ^= $x >> 4;
            $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xFFFF;
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    private static function formatTLV($tag, $value) {
        $length = str_pad(strlen($value), 2, '0', STR_PAD_LEFT);
        return $tag . $length . $value;
    }

    public static function generatePayload($target, $amount = null) {
        // Sanitize target (mobile number or national ID / tax ID)
        $target = preg_replace('/[^0-9]/', '', $target);

        // Format target
        if (strlen($target) >= 13) {
            // National ID or Tax ID (02)
            $formattedTarget = self::formatTLV('02', $target);
        } else {
            // Mobile Number (01) -> format to 66xxxxxxxxx (11 digits)
            if (substr($target, 0, 1) === '0') {
                $target = '66' . substr($target, 1);
            }
            $target = str_pad($target, 13, '0', STR_PAD_LEFT);
            $formattedTarget = self::formatTLV('01', $target);
        }

        // Sub-tags for 29 (PromptPay AID: A000000677010111)
        $sub29 = self::formatTLV('00', 'A000000677010111') . $formattedTarget;

        $payload = self::formatTLV('00', '01'); // Format Indicator
        $payload .= self::formatTLV('01', $amount ? '12' : '11'); // Point of Initiation: 12 (Dynamic), 11 (Static)
        $payload .= self::formatTLV('29', $sub29); // Merchant Account Information
        $payload .= self::formatTLV('53', '764'); // Currency: THB (764)
        
        if ($amount && floatval($amount) > 0) {
            $payload .= self::formatTLV('54', number_format(floatval($amount), 2, '.', '')); // Amount
        }
        
        $payload .= self::formatTLV('58', 'TH'); // Country Code: TH
        
        // Checksum calculation (Tag 63, length 04)
        $dataForCrc = $payload . '6304';
        $crc = self::crc16($dataForCrc);

        return $dataForCrc . $crc;
    }

    public static function getQrImageUrl($target, $amount = null, $size = 300) {
        $payload = self::generatePayload($target, $amount);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&margin=10&data=" . urlencode($payload);
    }
}
