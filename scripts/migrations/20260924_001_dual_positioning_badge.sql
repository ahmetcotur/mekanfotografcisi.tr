-- Update the homepage hero badge to hint at the dual (client + photographer)
-- collective positioning. Safe/idempotent REPLACE() on a substring, same
-- pattern as migrations 005/007 - no-ops harmlessly if the badge text has
-- already drifted from what migration 008 wrote.

UPDATE posts
SET content = REPLACE(
    content,
    'Profesyonel Mimari &amp; Mekan Fotoğrafçılığı',
    'Mekan Sahipleri &amp; Fotoğrafçılar İçin Kolektif'
)
WHERE slug = 'homepage';
