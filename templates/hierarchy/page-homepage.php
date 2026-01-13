<?php
/**
 * Homepage template with dynamic hero slider
 */
include __DIR__ . '/../page-header.php';

// Prepare Pexels Slider Images
require_once __DIR__ . '/../../includes/Core/PexelsService.php';
$pexels = new \Core\PexelsService();
$sliderPhotos = $pexels->getRandomPhotos(5);
$sliderImagesJson = json_encode(array_map(function ($p) {
    return $p['src']; }, $sliderPhotos));
?>

<?= $post->content ?>

<!-- Dynamic Slider Logic Footer -->
<script>
    (function () {
        const images = <?= $sliderImagesJson ?>;
        const container = document.getElementById('hero-slides');
        if (!container) return;

        // Clear existing placeholder slides
        container.innerHTML = '';
        let current = 0;

        if (images.length === 0) {
            // Fallback image if Pexels fails
            images.push('https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg');
        }

        // Initialize slides
        images.forEach((img, index) => {
            const div = document.createElement('div');
            div.className = 'absolute inset-0 w-full h-full bg-cover bg-center transition-opacity duration-1000 ease-in-out';
            div.style.backgroundImage = `url('${img}')`;
            div.style.opacity = index === 0 ? '1' : '0';
            container.appendChild(div);
        });

        // Loop slider
        if (images.length > 1) {
            setInterval(() => {
                const slides = container.children;
                if (!slides || !slides[current]) return;
                slides[current].style.opacity = '0';
                current = (current + 1) % slides.length;
                if (slides[current]) {
                    slides[current].style.opacity = '1';
                }
            }, 5000);
        }
    })();
</script>

<?php include __DIR__ . '/../page-footer.php'; ?>