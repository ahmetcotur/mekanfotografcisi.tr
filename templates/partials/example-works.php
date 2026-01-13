<?php
/**
 * Partial: Example Works (4-image Pexels grid)
 */
$examplePhotos = get_random_pexels_photos(4);
if (!empty($examplePhotos)):
    ?>
    <section class="py-24 bg-white border-t border-slate-100">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="font-heading font-bold text-3xl md:text-4xl text-slate-900 mb-4">
                    Örnek Çalışmalar
                </h2>
                <div class="h-1 w-20 bg-brand-600 mx-auto rounded-full"></div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8">
                <?php foreach ($examplePhotos as $photo):
                    $imgUrl = $photo['src']['large'] ?? $photo['src'];
                    ?>
                    <div class="group relative aspect-square rounded-2xl overflow-hidden shadow-lg border border-slate-100">
                        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Mimari Fotoğraf"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div
                            class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <span
                                class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/40">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>