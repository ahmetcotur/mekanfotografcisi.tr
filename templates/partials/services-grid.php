<?php
/**
 * Partial: Other Services Grid
 * Displays a grid of available photography services with Pexels backgrounds
 */

global $db;

// Fetch active services
// Exclude current page if possible (passed via $currentServiceId variable if needed)
$services = $db->select('posts', ['post_type' => 'service', 'post_status' => 'publish', 'limit' => 6]);

if (!empty($services)):
    // Get unique photos for each service
    $photos = get_random_pexels_photos(count($services));
    ?>
    <section class="py-24 bg-slate-50 border-t border-slate-200">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="font-heading font-bold text-3xl md:text-4xl text-slate-900 mb-4">
                    Diğer Fotoğrafçılık Hizmetlerimiz
                </h2>
                <p class="text-slate-600 text-lg">
                    İhtiyacınıza uygun profesyonel çözümler sunuyoruz.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($services as $index => $svc):
                    $photo = $photos[$index] ?? null;
                    $bgImage = $photo ? ($photo['src']['large'] ?? $photo['src']) : '/assets/images/hero-bg.jpg';
                    $currentId = $post->id ?? 0;
                    if ($svc['id'] == $currentId)
                        continue; // Skip current service
                    ?>
                    <a href="/<?= htmlspecialchars($svc['slug']) ?>"
                        class="group relative h-80 rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-500 block">
                        <!-- Background Image -->
                        <div class="absolute inset-0">
                            <img src="<?= htmlspecialchars($bgImage) ?>" alt="<?= htmlspecialchars($svc['title']) ?>"
                                class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-110">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent opacity-80 group-hover:opacity-70 transition-opacity">
                            </div>
                        </div>

                        <!-- Content -->
                        <div
                            class="absolute bottom-0 left-0 w-full p-8 translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                            <div
                                class="w-12 h-1 bg-brand-500 mb-4 rounded-full w-0 group-hover:w-12 transition-all duration-500">
                            </div>
                            <h3 class="font-bold text-2xl text-white mb-2 leading-tight">
                                <?= htmlspecialchars($svc['title']) ?>
                            </h3>
                            <div
                                class="flex items-center text-brand-300 font-medium text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300 transform translate-y-4 group-hover:translate-y-0 delay-75">
                                İncele <span class="ml-2 text-lg">→</span>
                            </div>
                        </div>

                        <!-- Icon Overlay (Optional visual flare) -->
                        <div
                            class="absolute top-6 right-6 w-10 h-10 rounded-full bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20 opacity-0 group-hover:opacity-100 transition-all duration-300 transform rotate-45 group-hover:rotate-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="text-white">
                                <line x1="7" y1="17" x2="17" y2="7"></line>
                                <polyline points="7 7 17 7 17 17"></polyline>
                            </svg>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-12">
                <a href="/services"
                    class="inline-flex items-center justify-center px-8 py-3 bg-white border border-slate-200 text-slate-700 rounded-xl font-semibold hover:bg-slate-50 hover:border-brand-200 hover:text-brand-600 transition-all shadow-sm">
                    Tüm Hizmetler
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>