<?php
// CORS ayarları (gerekirse kısıtlayabilirsiniz)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// CSV dosya adı - doğrudan erişimi engellemek için farklı bir isim verilebilir
$csvFileName = 'form_data_' . md5('mekanfotografcisi_gizli_anahtar') . '.csv';

// Sadece POST isteklerini işle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Yanıtın JSON olduğunu belirt
    header('Content-Type: application/json');

    // Hataları bastır (JSON çıktısını bozmasın)
    error_reporting(0);
    ini_set('display_errors', 0);

    // Form verilerini al
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        // AJAX olmayan form gönderimi için
        $data = $_POST;
    }

    // Tüm gerekli veriler var mı kontrol et
    if (empty($data['name']) || empty($data['email']) || empty($data['service']) || empty($data['message'])) {
        echo json_encode(['success' => false, 'message' => 'Lütfen tüm zorunlu alanları doldurun']);
        exit;
    }

    // Zamanı ayarla
    date_default_timezone_set('Europe/Istanbul');
    $date = date('d.m.Y H:i:s');

    // CSV dosyasının yolu
    $csvFile = $csvFileName;
    $isNewFile = !file_exists($csvFile);

    // CSV başlıklarını oluştur (yeni dosya ise)
    if ($isNewFile) {
        $headers = ['Tarih', 'Ad Soyad', 'E-posta', 'Telefon', 'Hizmet', 'Lokasyon', 'Mesaj', 'IP Adresi'];
        $fp = fopen($csvFile, 'a');
        fputcsv($fp, $headers, ',', '"', '\\');
        fclose($fp);
    }

    // IP adresini al
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    // Load helpers for sanitization
    require_once __DIR__ . '/includes/helpers.php';

    // Sanitize input data
    $name = sanitizeString($data['name']);
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $phone = isset($data['phone']) ? sanitizeString($data['phone']) : '';
    $service = sanitizeString($data['service']);
    $location = isset($data['location']) ? sanitizeString($data['location']) : '';
    $message = sanitizeString($data['message']);

    // Append Wizard Details if present
    if (!empty($data['wizard_details']) && is_array($data['wizard_details'])) {
        $details = "\n\n--- TEKLİF SİHİRBAZI DETAYLARI ---\n";
        foreach ($data['wizard_details'] as $key => $val) {
            $details .= ucfirst(str_replace('_', ' ', $key)) . ": " . sanitizeString($val) . "\n";
        }
        $message .= $details;
    }

    // Validate email
    if (!isValidEmail($email)) {
        echo json_encode(['success' => false, 'message' => 'Geçerli bir e-posta adresi giriniz']);
        exit;
    }

    // CSV için verileri hazırla
    $csvData = [
        $date,
        $name,
        $email,
        $phone,
        $service,
        $location,
        $message,
        $ipAddress
    ];

    // Verileri CSV'ye ekle
    $fp = fopen($csvFile, 'a');
    if ($fp) {
        fputcsv($fp, $csvData, ',', '"', '\\');
        fclose($fp);
    }

    // Database insertion
    $quoteId = null;
    try {
        require_once __DIR__ . '/includes/database.php';

        // If a logged-in client submitted this (Authorization: Bearer <jwt>),
        // attach their user_id so the request shows up in their /musteri
        // dashboard. Anonymous submissions (no header, or a freelancer/admin
        // token) keep working exactly as before - user_id stays null.
        $clientUserId = null;
        require_once __DIR__ . '/api/middleware.php';
        $authPayload = validateAuthToken();
        if ($authPayload && ($authPayload['role'] ?? '') === 'client') {
            $clientUserId = $authPayload['user_id'];
        }

        $db_local = new DatabaseClient();
        $inserted = $db_local->insert('quotes', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'location' => $location,
            'service' => $service,
            'message' => $message,
            'wizard_details' => !empty($data['wizard_details']) ? json_encode($data['wizard_details']) : null,
            'ip_address' => $ipAddress,
            'user_id' => $clientUserId
        ]);
        $quoteId = $inserted['id'] ?? null;
    } catch (Exception $e) {
        error_log("Quote DB insertion failed: " . $e->getMessage());
    }

    // E-posta bildirimini gönder (Opsiyonel)
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        try {
            require_once __DIR__ . '/vendor/autoload.php';

            // Core\MailService sınıfının varlığını kontrol et
            if (class_exists('\\Core\\MailService')) {
                $mailService = new \Core\MailService();

                $to = "info@mekanfotografcisi.tr";
                $isWizard = !empty($data['wizard_details']);
                $subject = $isWizard ? "Yeni Teklif İstemi (Sihirbaz) - " . $name : "Yeni İletişim Formu Mesajı - " . $name;

                $emailContent = "Merhaba,\n\nWeb sitenizden yeni bir form gönderildi.\n\n";
                $emailContent .= "Tarih: " . $date . "\n";
                $emailContent .= "Ad Soyad: " . $name . "\n";
                $emailContent .= "E-posta: " . $email . "\n";
                $emailContent .= "Telefon: " . $phone . "\n";
                $emailContent .= "Hizmet: " . $service . "\n";
                $emailContent .= "Lokasyon: " . $location . "\n";
                $emailContent .= "Mesaj: " . $message . "\n";
                $emailContent .= "IP Adresi: " . $ipAddress . "\n";

                $mailService->send($to, $subject, $emailContent, $email);
            }
        } catch (Exception $e) {
            // Mail gönderilemezse logla ama işlemi durdurma
            error_log("Email sending failed (Optional): " . $e->getMessage());
        }
    }

    // Form kaydedildikten sonra n8n'e de gönder
    $webhook_data = json_encode(array_merge([
        'source'       => 'form',
        'website_uuid' => '1be2f821-28cd-4c86-aeb0-dabe0c05aa0a',
        'page_url'     => $_SERVER['HTTP_REFERER'] ?? ''
    ], $data));

    $ch = curl_init('https://n8n.ahmetcotur.com/webhook/chat');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $webhook_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    curl_close($ch);

    // Başarılı yanıt döndür
    echo json_encode([
        'success' => true,
        'message' => 'Form verileri başarıyla kaydedildi',
        'quote_id' => $quoteId,
        'quote_number' => $quoteId ? 'MF-' . str_pad($quoteId, 5, '0', STR_PAD_LEFT) : null
    ]);
    exit;
} else {
    // Yanlış istek tipi veya sayfa bulunamadı
    header("HTTP/1.0 404 Not Found");
    echo '<h1>404 - Sayfa Bulunamadı</h1>';
    exit;
}
?>