-- Update site-wide SEO defaults to reflect the dual (client + photographer)
-- collective positioning. Uses DO UPDATE (not DO NOTHING) since the goal is to
-- actually overwrite the old single-agency default text, consistent with how
-- migration 008 already overwrote posts.content wholesale for the homepage.

INSERT INTO settings ("key", value, "group") VALUES
('seo_default_desc', 'Mekanını çektirmek isteyenler için bölgesine ve kategorisine uygun fotoğrafçı bulma, fotoğrafçılar için açık çekim taleplerine erişme platformu. Antalya ve Muğla başta olmak üzere Türkiye genelinde.', 'SEO'),
('site_tagline', 'Mekan Sahipleri ve Fotoğrafçılar İçin Kolektif', 'General')
ON CONFLICT ("key") DO UPDATE SET value = EXCLUDED.value;
