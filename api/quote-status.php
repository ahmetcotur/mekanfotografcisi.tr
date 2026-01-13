<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$data = json_decode(file_get_contents('php://input'), true);
$quoteNumber = $data['quote_number'] ?? '';

if (empty($quoteNumber)) {
    echo json_encode(['success' => false, 'message' => 'Lütfen teklif numaranızı giriniz.']);
    exit;
}

// Extract ID from number (e.g., MF-00123 -> 123)
$id = (int) str_replace('MF-', '', strtoupper($quoteNumber));

if ($id === 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz teklif numarası formatı.']);
    exit;
}

try {
    $db = new DatabaseClient();
    $result = $db->query("SELECT id, name, status, admin_note, created_at FROM quotes WHERE id = ?", [$id]);

    if (empty($result)) {
        echo json_encode(['success' => false, 'message' => 'Teklif bulunamadı. Lütfen numarayı kontrol ediniz.']);
        exit;
    }

    $quote = $result[0];

    // Status translation
    $statuses = [
        'beklemede' => 'Beklemede (İnceleniyor)',
        'inceleniyor' => 'İnceleniyor',
        'fiyatlandirildi' => 'Fiyatlandırıldı',
        'tamamlandi' => 'Tamamlandı',
        'iptal' => 'İptal Edildi'
    ];

    echo json_encode([
        'success' => true,
        'data' => [
            'number' => 'MF-' . str_pad($quote['id'], 5, '0', STR_PAD_LEFT),
            'name' => $quote['name'],
            'status' => $statuses[$quote['status']] ?? $quote['status'],
            'note' => $quote['admin_note'] ?: 'Henüz bir not eklenmemiş.',
            'date' => date('d.m.Y H:i', strtotime($quote['created_at']))
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Bir hata oluştu.']);
}
