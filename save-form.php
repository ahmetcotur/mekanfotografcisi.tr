<?php
// CORS ayarları (gerekirse kısıtlayabilirsiniz)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Admin paneli için güvenli URL - bu değeri güvenli ve tahmin edilemez bir değerle değiştirin
$adminToken = "5f7a9d3e2c1b8a6"; // Bu değeri değiştirin ve güvenli bir yerde saklayın
$adminPassword = "MekanFoto2023!"; // Bu şifreyi değiştirin ve güvenli bir yerde saklayın

// CSV dosya adı - doğrudan erişimi engellemek için farklı bir isim verilebilir
$csvFileName = 'form_data_' . md5('mekanfotografcisi_gizli_anahtar') . '.csv';

// Sadece POST isteklerini işle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        fputcsv($fp, $headers);
        fclose($fp);
    }
    
    // IP adresini al
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    
    // CSV için verileri hazırla
    $csvData = [
        $date,
        $data['name'],
        $data['email'],
        isset($data['phone']) ? $data['phone'] : '',
        $data['service'],
        isset($data['location']) ? $data['location'] : '',
        $data['message'],
        $ipAddress
    ];
    
    // Verileri CSV'ye ekle
    $fp = fopen($csvFile, 'a');
    fputcsv($fp, $csvData);
    fclose($fp);
    
    // Başarılı yanıt döndür
    echo json_encode(['success' => true, 'message' => 'Form verileri başarıyla kaydedildi']);
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['token']) && $_GET['token'] === $adminToken) {
    // Basit admin görünümü için şifre koruması
    if (!isset($_GET['password']) || $_GET['password'] !== $adminPassword) {
        echo '<form method="GET" style="max-width: 400px; margin: 100px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; text-align: center; font-family: Arial, sans-serif;">';
        echo '<h2 style="color: #3a5e7c;">Yönetici Girişi Gerekli</h2>';
        echo '<p>Form verilerini görüntülemek için şifre gerekli.</p>';
        echo '<input type="hidden" name="token" value="' . htmlspecialchars($adminToken) . '">';
        echo '<input type="password" name="password" placeholder="Şifre" style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<button type="submit" style="background: #3a5e7c; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Giriş</button>';
        echo '</form>';
        exit;
    }
    
    // CSV dosyasının varlığını kontrol et
    $csvFile = $csvFileName;
    if (!file_exists($csvFile)) {
        echo '<h2>Henüz form verisi bulunmuyor</h2>';
        exit;
    }
    
    // Basit bir HTML tablo gösterimi
    echo '<!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Form Verileri - Yönetici Paneli</title>
        <meta name="robots" content="noindex, nofollow">
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            tr:nth-child(even) { background-color: #f9f9f9; }
            .actions { margin: 20px 0; }
            .actions a { display: inline-block; margin-right: 10px; padding: 8px 15px; background: #3a5e7c; color: white; text-decoration: none; border-radius: 4px; }
            .actions a.danger { background: #e74c3c; }
            h1 { color: #3a5e7c; }
            .logout { float: right; color: #e74c3c; text-decoration: none; }
            .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
            .success { background-color: #d4edda; color: #155724; }
            .filter-form { margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px; }
            .filter-form input, .filter-form select { padding: 8px; margin-right: 10px; }
        </style>
    </head>
    <body>
        <h1>Mekan Fotoğrafçısı - Form Verileri Yönetim Paneli <a href="?token=' . htmlspecialchars($adminToken) . '" class="logout">Çıkış</a></h1>';
    
    // Başarılı mesajı göster
    if (isset($_GET['cleared']) && $_GET['cleared'] === 'true') {
        echo '<p class="message success">Veriler başarıyla temizlendi!</p>';
    }
    
    echo '<div class="actions">
            <a href="?token=' . htmlspecialchars($adminToken) . '&password=' . htmlspecialchars($adminPassword) . '&download=csv">CSV İndir</a>
            <a href="?token=' . htmlspecialchars($adminToken) . '&password=' . htmlspecialchars($adminPassword) . '&clear=true" class="danger" onclick="return confirm(\'Tüm verileri silmek istediğinize emin misiniz?\')">Verileri Temizle</a>
        </div>';
    
    // Filtreleme formu
    echo '<div class="filter-form">
            <form method="GET">
                <input type="hidden" name="token" value="' . htmlspecialchars($adminToken) . '">
                <input type="hidden" name="password" value="' . htmlspecialchars($adminPassword) . '">
                <label>Tarih aralığı: </label>
                <input type="date" name="start_date">
                <input type="date" name="end_date">
                <select name="service">
                    <option value="">Tüm Hizmetler</option>
                    <option value="mimari">Mimari Fotoğrafçılık</option>
                    <option value="ic-mekan">İç Mekan Fotoğrafçılığı</option>
                    <option value="emlak">Emlak Fotoğrafçılığı</option>
                    <option value="otel-restoran">Otel ve Restoran Fotoğrafçılığı</option>
                </select>
                <button type="submit">Filtrele</button>
            </form>
        </div>';
    
    // CSV'yi indir
    if (isset($_GET['download']) && $_GET['download'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=iletisim_formu_verileri.csv');
        readfile($csvFile);
        exit;
    }
    
    // Verileri temizle
    if (isset($_GET['clear']) && $_GET['clear'] === 'true') {
        $fp = fopen($csvFile, 'w');
        $headers = ['Tarih', 'Ad Soyad', 'E-posta', 'Telefon', 'Hizmet', 'Lokasyon', 'Mesaj', 'IP Adresi'];
        fputcsv($fp, $headers);
        fclose($fp);
        header('Location: ?token=' . urlencode($adminToken) . '&password=' . urlencode($adminPassword) . '&cleared=true');
        exit;
    }
    
    // CSV dosyasını oku ve tablo olarak göster
    echo '<table>
        <tr>
            <th>Tarih</th>
            <th>Ad Soyad</th>
            <th>E-posta</th>
            <th>Telefon</th>
            <th>Hizmet</th>
            <th>Lokasyon</th>
            <th>Mesaj</th>
            <th>IP Adresi</th>
        </tr>';
    
    $file = fopen($csvFile, 'r');
    
    // Başlık satırını atla
    fgetcsv($file);
    
    // Filtreleme parametreleri
    $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? new DateTime($_GET['start_date']) : null;
    $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? new DateTime($_GET['end_date']) : null;
    $serviceFilter = isset($_GET['service']) && !empty($_GET['service']) ? $_GET['service'] : null;
    
    // Verileri göster
    while (($data = fgetcsv($file)) !== false) {
        // Tarih filtresi
        if ($startDate || $endDate) {
            $rowDate = DateTime::createFromFormat('d.m.Y H:i:s', $data[0]);
            if (!$rowDate) continue;
            
            if ($startDate && $rowDate < $startDate) continue;
            if ($endDate && $rowDate > $endDate) continue;
        }
        
        // Hizmet filtresi
        if ($serviceFilter && $data[4] !== $serviceFilter) continue;
        
        echo '<tr>';
        foreach ($data as $index => $value) {
            // Mesaj sütununu kısalt
            if ($index === 6 && strlen($value) > 100) {
                $value = htmlspecialchars(substr($value, 0, 100)) . '...';
            } else {
                $value = htmlspecialchars($value);
            }
            echo '<td>' . $value . '</td>';
        }
        echo '</tr>';
    }
    fclose($file);
    
    echo '</table>
    </body>
    </html>';
} else {
    // Yanlış istek tipi veya sayfa bulunamadı
    header("HTTP/1.0 404 Not Found");
    echo '<h1>404 - Sayfa Bulunamadı</h1>';
    exit;
}
?> 