<?php
/**
 * AI Service API
 * Handles OpenAI integration for content generation and SEO suggestions.
 */
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/database.php';

addCorsHeaders();
$user = requireAuth();

$db = new DatabaseClient();

// Get settings
$apiKey = getSetting($db, 'openai_api_key');
$model = getSetting($db, 'openai_model', 'gpt-4o-mini');

if (empty($apiKey)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'OpenAI API Anahtarı ayarlanmamış. Lütfen ayarlardan ekleyin.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

try {
    if ($action === 'generate-content') {
        $title = $data['title'] ?? '';
        $keywords = $data['keywords'] ?? '';
        $existingContent = $data['existing_content'] ?? '';
        $type = $data['type'] ?? 'page';

        // Fetch active services for interlinking context
        $services = $db->select('posts', ['post_type' => 'service', 'post_status' => 'publish']);
        $serviceLinks = [];
        foreach ($services as $s) {
            $serviceLinks[] = "- {$s['title']}: https://mekanfotografcisi.tr/{$s['slug']}";
        }
        $context = "Mevcut Hizmetlerimiz ve Linkleri:\n" . implode("\n", $serviceLinks);

        if ($type === 'blog') {
            $wordCount = 1500;
            $prompt = "Sen bir profesyonel blog yazarı ve mekan fotoğrafçılığı konusunda uzman bir içerik üreticisisin. 
            
BELİRTİLEN KONU/BAŞLIK: '" . ($title ?: $keywords) . "'
HEDEF ANAHTAR KELİMELER: '{$keywords}'

{$context}

ÖNEMLİ KURALLAR:
1. Bu bir blog yazısıdır. Okuyuculara derinlemesine bilgi veren, sektörel gelişmeleri anlatan, rehber niteliğinde ve en az {$wordCount} kelimeden oluşan KAPSAMLI bir içerik üretmelisin.
2. Yazı akıcı, profesyonel ve SEO dostu olmalıdır.
3. İçerik içerisinde mekanfotografcisi.tr sitesindeki yukarıda listelenen hizmetlerden EN AZ 2 TANESİNE doğal bir şekilde atıfta bulun ve belirtilen linklere YÖNLENDİRME (link) yap.
4. Yazı başlığını (H1) en başta belirt.
5. Eğer sana bir başlık verilmediyse (boşsa), konuya uygun ilgi çekici bir başlık üret.
6. Yanıtını şu JSON formatında döndür (Başka hiçbir metin ekleme):
   {
     \"title\": \"Buraya oluşturduğun veya sana verilen başlık gelecek\",
     \"content\": \"Buraya HTML formatında içerik gelecek (p, h2, h3, ul, li etiketleri ile)\"
   }
";
        } else {
            $prompt = "Sen bir profesyonel SEO uzmanı ve mekan fotoğrafçılığı konusunda uzman bir içerik yazarısın. 

SAYFA BAŞLIĞI: '{$title}'
HEDEF ANAHTAR KELİMELER: '{$keywords}'

ÖNEMLİ: Sayfa başlığını dikkatlice analiz et ve SADECE o spesifik hizmet türü hakkında içerik üret. 
Örneğin:
- 'Lifestyle Fotoğrafçılığı' ise → Lifestyle çekimlerinin ne olduğu, hangi alanlarda kullanıldığı, doğal anlar, günlük yaşam sahneleri, marka hikayesi anlatımı gibi konulara odaklan.
- 'Mimari Fotoğrafçılık' ise → Bina ve yapı çekimleri, iç mekan detayları, perspektif ve ışık kullanımı gibi konulara odaklan.
- 'Otel Fotoğrafçılığı' ise → Otel odaları, lobiler, restoran alanları, havuz ve spa çekimleri gibi konulara odaklan.

";
        }

        $prompt .= (!empty($existingContent) ? "Aşağıda mevcut bir içerik var. Lütfen bu içeriği temel alarak SEO açısından güçlendir, daha kapsamlı hale getir (en az 400-500 kelimeye tamamla) ve profesyonel bir dille yeniden düzenle:\n\n{$existingContent}\n\n" : "");

        $prompt .= "Lütfen aşağıdaki kurallara uyarak içerik üret:
1. İçerik HTML formatında olmalı (sadece p, h2, h3, ul, li etiketlerini kullan).
2. Başlığı (Sayfa Başlığını) içeriğin en başında tekrar etme, doğrudan konuya gir.
3. En az 3 alt başlık (h2) kullan ve her biri '{$title}' hizmetinin farklı bir yönünü açıklasın.
4. Profesyonel, ikna edici ve samimi bir dil kullan. Türkiye'deki mekan sahiplerine hitap et.
5. Sadece gövde içeriğini döndür (html, head, body etiketleri olmasın).
6. İçeriği markdown kod blokları (```html gibi) içine alma, doğrudan ham metin olarak döndür.
7. İçeriği tırnak işaretleri içine alma.
8. Spesifik örnekler ver: '{$title}' hizmetinin hangi sektörlerde, nasıl kullanıldığını açıkla.
9. Eğer uygunsa, buton veya dikkat çekici alanlar için şu sınıfları kullanabilirsin:
   - Fiyat Teklifi Butonu: <a href=\"#\" onclick=\"openQuoteWizard('mimari')\" class=\"inline-block px-8 py-4 bg-brand-600 hover:bg-brand-500 text-white rounded-2xl font-black shadow-xl transition-all\">Hemen Fiyat Al</a>";

        $response = callOpenAI($apiKey, $model, $prompt);

        if ($type === 'blog') {
            // Clean up JSON if LLM added markdown backticks
            $jsonStr = preg_replace('/```json\s*|\s*```/', '', $response);
            $decoded = json_decode($jsonStr, true);
            if ($decoded) {
                echo json_encode(['success' => true, 'title' => $decoded['title'], 'content' => $decoded['content']]);
            } else {
                // Fallback if JSON decoding fails
                echo json_encode(['success' => true, 'content' => $response]);
            }
        } else {
            // Sanitize response: Strip markdown code blocks and wrapping quotes
            $cleanContent = preg_replace('/^```[a-z]*\s?|\s?```$/i', '', trim($response));
            $cleanContent = preg_replace('/^"|"$/', '', $cleanContent);

            echo json_encode(['success' => true, 'content' => $cleanContent]);
        }

    } elseif ($action === 'suggest-urls') {
        $industry = $data['industry'] ?? 'mekan fotoğrafçılığı';

        $prompt = "Sen bir SEO uzmanısın. '{$industry}' sektörü için Türkiye pazarında en iyi dönüşüm getirecek 10 adet URL yapısı öner. 
Öneriler sadece slug formatında olsun (örn: antalya-otel-cekimi). 
Sadece JSON listesi olarak döndür: [\"slug1\", \"slug2\"]";

        $response = callOpenAI($apiKey, $model, $prompt);
        // Clean up JSON if LLM added markdown backticks
        $jsonStr = preg_replace('/```json\s*|\s*```/', '', $response);
        echo json_encode(['success' => true, 'suggestions' => json_decode($jsonStr, true)]);

    } else {
        throw new Exception("Geçersiz işlem");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Call OpenAI API
 */
function callOpenAI($apiKey, $model, $prompt)
{
    $url = 'https://api.openai.com/v1/chat/completions';

    $postData = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => 'Sen yardımcı bir içerik üreticisisin.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception("CURL Hatası: " . $error);
    }

    $result = json_decode($response, true);
    if ($httpCode !== 200) {
        $errorMessage = $result['error']['message'] ?? 'Bilinmeyen OpenAI hatası';
        throw new Exception("OpenAI Hatası ($httpCode): " . $errorMessage);
    }

    return $result['choices'][0]['message']['content'];
}

function getSetting($db, $key, $default = '')
{
    $res = $db->select('settings', ['key' => $key]);
    return !empty($res) ? $res[0]['value'] : $default;
}
