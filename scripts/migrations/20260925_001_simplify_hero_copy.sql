-- Shorten the verbose hero/CTA paragraph copy to something punchier, as part
-- of the general landing-page decluttering pass. Safe/idempotent REPLACE()
-- on substrings, same pattern as prior homepage-content migrations - no-ops
-- harmlessly if the text has already drifted in production.

UPDATE posts
SET content = REPLACE(
    content,
    'Mekanlarınızın ruhunu ve estetiğini profesyonel karelerle ölümsüzleştiriyoruz. İşletmenize değer katan prestijli çekim çözümleri.',
    'Mekanınızı en iyi ışıkla, doğru fotoğrafçıyla buluşturuyoruz.'
)
WHERE slug = 'homepage';

-- STATEMENT

UPDATE posts
SET content = REPLACE(
    content,
    'Profesyonel çekimler ve markanızın ihtiyacı olan prestijli görseller için bizimle hemen iletişime geçebilirsiniz.',
    'Bölgenize en uygun fotoğrafçıyı bulmak iki dakika sürer.'
)
WHERE slug = 'homepage';
