<?php
// Leads/Quotes Manager View

// Mark as read if ID provided
if (isset($_GET['mark_read'])) {
    $db->update('quotes', ['is_read' => true], ['id' => $_GET['mark_read']]);
    header('Location: /admin/?page=quotes');
    exit;
}

// Update status and note via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_quote') {
    $id = (int) $_POST['id'];
    $status = $_POST['status'];
    $note = $_POST['admin_note'];

    $db->update('quotes', [
        'status' => $status,
        'admin_note' => $note,
        'is_read' => true
    ], ['id' => $id]);

    header('Location: /admin/?page=quotes&success=1');
    exit;
}

// Fetch quotes from DB
$quotes = $db->query("SELECT * FROM quotes ORDER BY created_at DESC");

$statusLabels = [
    'beklemede' => ['label' => 'Beklemede', 'class' => 'bg-slate-100 text-slate-600'],
    'inceleniyor' => ['label' => 'İnceleniyor', 'class' => 'bg-amber-100 text-amber-700'],
    'fiyatlandirildi' => ['label' => 'Fiyatlandırıldı', 'class' => 'bg-blue-100 text-blue-700'],
    'tamamlandi' => ['label' => 'Tamamlandı', 'class' => 'bg-green-100 text-green-700'],
    'iptal' => ['label' => 'İptal', 'class' => 'bg-red-100 text-red-700'],
];
?>

<div class="quotes-container">
    <div class="card">
        <div class="card-header flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Gelen Teklif Talepleri</h3>
            <span class="badge badge-info"><?= count($quotes) ?> Toplam</span>
        </div>
        <div class="card-body p-0 text-[13px]">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">Durum</th>
                        <th>Teklif No</th>
                        <th>Müşteri</th>
                        <th>Hizmet / Bölge</th>
                        <th>Görüşme Notları & Durum</th>
                        <th>Tarih</th>
                        <th style="width: 120px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quotes)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-10 text-slate-400 italic">Henüz bir talep bulunmuyor.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($quotes as $q): ?>
                            <?php $st = $statusLabels[$q['status']] ?? ['label' => $q['status'], 'class' => 'bg-slate-100 text-slate-600']; ?>
                            <tr class="<?= $q['is_read'] ? 'read' : 'unread' ?>">
                                <td>
                                    <?php if (!$q['is_read']): ?>
                                        <span class="status-dot pulse bg-brand-500" title="Yeni Mesaj"></span>
                                    <?php else: ?>
                                        <span class="status-dot bg-slate-300" title="Okundu"></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span
                                        class="font-bold text-slate-400">#MF-<?= str_pad($q['id'], 5, '0', STR_PAD_LEFT) ?></span>
                                </td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-800"><?= htmlspecialchars($q['name']) ?></span>
                                        <span
                                            class="text-xs text-slate-500 line-clamp-1"><?= htmlspecialchars($q['email']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="badge badge-brand mb-1 w-fit"><?= htmlspecialchars($q['service']) ?></span>
                                        <span class="text-xs text-slate-500"><i class="fas fa-map-marker-alt"></i>
                                            <?= htmlspecialchars($q['location']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="quote-message-box">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $st['class'] ?>"><?= $st['label'] ?></span>
                                            <?php if ($q['admin_note']): ?>
                                                <i class="fas fa-comment-dots text-slate-400"
                                                    title="<?= htmlspecialchars($q['admin_note']) ?>"></i>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-xs text-slate-500 italic line-clamp-1">
                                            <?= $q['admin_note'] ?: 'Not bulunmuyor...' ?></p>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="text-xs text-slate-500"><?= date('d.m.Y H:i', strtotime($q['created_at'])) ?></span>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <button class="btn btn-sm btn-light"
                                            onclick="viewQuoteDetails(<?= htmlspecialchars(json_encode($q)) ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary"
                                            onclick="updateQuoteStatus(<?= htmlspecialchars(json_encode($q)) ?>)"
                                            title="Durum Güncelle">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .quotes-container {
        padding-bottom: 50px;
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: block;
        margin: 0 auto;
    }

    .pulse {
        animation: status-pulse 2s infinite;
    }

    @keyframes status-pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(14, 165, 233, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(14, 165, 233, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(14, 165, 233, 0);
        }
    }

    tr.unread {
        background-color: #f0f9ff;
    }

    .quote-message-box {
        min-width: 200px;
    }

    .badge-brand {
        background: #f0f9ff;
        color: #0284c7;
        border: 1px solid #bae6fd;
    }

    .swal2-html-container {
        text-align: left !important;
    }
</style>

<script>
    function updateQuoteStatus(quote) {
        Swal.fire({
            title: 'Durum Güncelle',
            html: `
                <form id="update-form" method="POST" class="space-y-4 pt-4">
                    <input type="hidden" name="action" value="update_quote">
                    <input type="hidden" name="id" value="${quote.id}">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Başvuru Sahibi</label>
                        <p class="text-slate-900 font-bold">${quote.name}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Teklif Durumu</label>
                        <select name="status" class="w-full border p-2 rounded-lg">
                            <option value="beklemede" ${quote.status === 'beklemede' ? 'selected' : ''}>Beklemede</option>
                            <option value="inceleniyor" ${quote.status === 'inceleniyor' ? 'selected' : ''}>İnceleniyor</option>
                            <option value="fiyatlandirildi" ${quote.status === 'fiyatlandirildi' ? 'selected' : ''}>Fiyatlandırıldı</option>
                            <option value="tamamlandi" ${quote.status === 'tamamlandi' ? 'selected' : ''}>Tamamlandı</option>
                            <option value="iptal" ${quote.status === 'iptal' ? 'selected' : ''}>İptal</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Müşteriye Görünecek Not</label>
                        <textarea name="admin_note" class="w-full border p-2 rounded-lg" rows="4">${quote.admin_note || ''}</textarea>
                        <p class="text-[10px] text-slate-400 mt-1 italic">* Bu not müşteri tarafından takip sayfasında görülecektir.</p>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Güncelle',
            cancelButtonText: 'İptal',
            preConfirm: () => {
                document.getElementById('update-form').submit();
            }
        });
    }

    function viewQuoteDetails(quote) {
        let detailsHtml = `
            <div class="text-left space-y-4">
                <div class="grid grid-cols-2 gap-4 pb-4 border-b">
                    <div>
                        <label class="text-xs text-slate-400 uppercase font-bold">Müşteri</label>
                        <p class="font-bold">${quote.name}</p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 uppercase font-bold">Tarih</label>
                        <p>${new Date(quote.created_at).toLocaleString('tr-TR')}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 pb-4 border-b">
                    <div>
                        <label class="text-xs text-slate-400 uppercase font-bold">E-posta</label>
                        <p><a href="mailto:${quote.email}" class="text-brand-600">${quote.email}</a></p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 uppercase font-bold">Telefon</label>
                        <p><a href="tel:${quote.phone}" class="text-brand-600">${quote.phone}</a></p>
                    </div>
                </div>

                <div>
                    <label class="text-xs text-slate-400 uppercase font-bold">İş Detayı</label>
                    <p class="bg-slate-50 p-3 rounded-lg border text-sm leading-relaxed whitespace-pre-wrap">${quote.message}</p>
                </div>

                ${quote.wizard_details ? `
                    <div>
                        <label class="text-xs text-slate-400 uppercase font-bold">Sihirbaz Parametreleri</label>
                        <div class="bg-slate-900 p-4 rounded-lg mt-2 font-mono text-xs text-brand-400 overflow-x-auto">
                            <pre>${JSON.stringify(JSON.parse(quote.wizard_details), null, 4)}</pre>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;

        Swal.fire({
            title: `Teklif Talebi Detayları (#MF-${quote.id})`,
            html: detailsHtml,
            width: '600px',
            showCloseButton: true,
            confirmButtonText: 'Kapat',
            confirmButtonColor: '#64748b'
        });
    }
</script>