// DOM elementlerini seçme
const header = document.querySelector('.header');
const hamburger = document.querySelector('.hamburger');
const navLinks = document.querySelector('.nav-links');
const navLinksItems = document.querySelectorAll('.nav-links a');
const portfolioFilterBtns = document.querySelectorAll('.filter-btn');
const portfolioItems = document.querySelectorAll('.portfolio-item');
const contactForm = document.querySelector('.contact-form');

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function () {
    // Mobil menü toggle
    if (hamburger && navLinks) {
        hamburger.addEventListener('click', function () {
            this.classList.toggle('active');
            navLinks.classList.toggle('active');
        });

        // Menü linklerine tıklandığında mobil menüyü kapatma
        navLinksItems.forEach(item => {
            item.addEventListener('click', function () {
                hamburger.classList.remove('active');
                navLinks.classList.remove('active');
            });
        });
    }

    // Kaydırma olayı - header stilini değiştirme
    if (header) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // Portfolyo Filtreleme
    if (portfolioFilterBtns.length > 0) {
        portfolioFilterBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                // Aktif sınıfı kaldırma
                portfolioFilterBtns.forEach(btn => {
                    btn.classList.remove('active');
                });

                // Tıklanan butona active sınıfı ekleme
                this.classList.add('active');

                const filterValue = this.getAttribute('data-filter');

                // Portfolyo öğelerini filtreleme
                portfolioItems.forEach(item => {
                    if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    }

    // Form gönderimi
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Form verilerini alma
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const phoneInput = document.getElementById('phone');
            const serviceInput = document.getElementById('service');
            const locationInput = document.getElementById('location');
            const messageInput = document.getElementById('message');

            const name = nameInput ? nameInput.value : '';
            const email = emailInput ? emailInput.value : '';
            const phone = phoneInput ? phoneInput.value : '';
            const service = serviceInput ? serviceInput.value : '';
            const location = locationInput ? locationInput.value : '';
            const message = messageInput ? messageInput.value : '';

            // Basit doğrulama
            if (name && email && service) {
                // Yükleniyor mesajı göster
                const loadingMessage = document.createElement('div');
                loadingMessage.className = 'form-loading';
                loadingMessage.innerHTML = 'Form gönderiliyor...';

                // Önceki mesajları temizle
                const oldLoading = contactForm.querySelector('.form-loading');
                const oldError = contactForm.querySelector('.form-error');
                if (oldLoading) contactForm.removeChild(oldLoading);
                if (oldError) contactForm.removeChild(oldError);

                // Yükleniyor mesajını ekle
                contactForm.insertBefore(loadingMessage, contactForm.firstChild);

                // Form verilerini hazırla
                const formData = {
                    name: name,
                    email: email,
                    phone: phone,
                    service: service,
                    location: location,
                    message: message
                };

                // AJAX isteği gönder
                fetch('save-form.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Form gönderim sonrası görünüm
                            contactForm.innerHTML = `
                            <div class="form-success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#3a5e7c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <h3>Mesajınız Gönderildi!</h3>
                                <p>Teşekkür ederiz ${name}, en kısa sürede size dönüş yapacağız.</p>
                                <div class="form-actions">
                                    <button type="button" class="btn btn-primary" onclick="window.location.reload()">Yeni Mesaj Gönder</button>
                                </div>
                            </div>
                        `;
                        } else {
                            // Sunucu hata mesajı göster
                            const errorMessage = document.createElement('div');
                            errorMessage.className = 'form-error';
                            errorMessage.innerHTML = data.message || 'Form gönderilirken bir hata oluştu.';
                            contactForm.insertBefore(errorMessage, contactForm.firstChild);

                            // Yükleniyor mesajını kaldır
                            const loadingMsg = contactForm.querySelector('.form-loading');
                            if (loadingMsg) contactForm.removeChild(loadingMsg);
                        }
                    })
                    .catch(error => {
                        // Genel hata mesajı göster
                        console.error('Hata:', error);
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'form-error';
                        errorMessage.innerHTML = 'Bağlantı hatası. Lütfen daha sonra tekrar deneyin.';
                        contactForm.insertBefore(errorMessage, contactForm.firstChild);

                        // Yükleniyor mesajını kaldır
                        const loadingMsg = contactForm.querySelector('.form-loading');
                        if (loadingMsg) contactForm.removeChild(loadingMsg);
                    });
            } else {
                // Hata mesajı göster
                // ... logic can stay similar
            }
        });
    }

    // Services Carousel
    (function () {
        const track = document.querySelector('.services-carousel-track');
        // Only run if track exists
        if (!track) return;

        const cards = document.querySelectorAll('.services-carousel .service-card');
        const prevBtn = document.querySelector('.carousel-btn-prev');
        const nextBtn = document.querySelector('.carousel-btn-next');
        const dotsContainer = document.querySelector('.carousel-dots');

        if (!cards.length) return;

        let currentIndex = 0;
        let cardsPerView = 3;
        let totalSlides = Math.ceil(cards.length / cardsPerView);

        // Update cards per view based on screen size
        function updateCardsPerView() {
            if (window.innerWidth <= 768) {
                cardsPerView = 1;
            } else if (window.innerWidth <= 1024) {
                cardsPerView = 2;
            } else {
                cardsPerView = 3;
            }
            totalSlides = Math.ceil(cards.length / cardsPerView);
            updateCarousel();
            updateDots();
        }

        // Create dots
        function createDots() {
            if (!dotsContainer) return;
            dotsContainer.innerHTML = '';
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('button');
                dot.className = 'carousel-dot';
                if (i === 0) dot.classList.add('active');
                dot.setAttribute('aria-label', `Slide ${i + 1}`);
                dot.addEventListener('click', () => goToSlide(i));
                dotsContainer.appendChild(dot);
            }
        }

        function updateDots() {
            if (!dotsContainer) return;
            const dots = dotsContainer.querySelectorAll('.carousel-dot');
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentIndex);
            });
        }

        function updateCarousel() {
            const cardWidth = track.offsetWidth / cardsPerView;
            const gap = 30; // Matches CSS gap
            const offset = -currentIndex * (cardWidth + gap) * cardsPerView;
            track.style.transform = `translateX(${offset}px)`;
            updateDots();
        }

        function goToSlide(index) {
            if (index < 0 || index >= totalSlides) return;
            currentIndex = index;
            updateCarousel();
        }

        function nextSlide() {
            if (currentIndex < totalSlides - 1) {
                currentIndex++;
            } else {
                currentIndex = 0;
            }
            updateCarousel();
        }

        function prevSlide() {
            if (currentIndex > 0) {
                currentIndex--;
            } else {
                currentIndex = totalSlides - 1;
            }
            updateCarousel();
        }

        if (prevBtn) prevBtn.addEventListener('click', prevSlide);
        if (nextBtn) nextBtn.addEventListener('click', nextSlide);

        window.addEventListener('resize', updateCardsPerView);

        // Initial setup
        updateCardsPerView();
        createDots();
    })();

    // Global "Hemen Fiyat Al ✨" Button Handler
    document.addEventListener('click', function (e) {
        // Handle elements with specific text, potentially inside buttons or links
        const target = e.target.closest('button, a');
        if (target && target.innerText.includes('Hemen Fiyat Al ✨')) {
            e.preventDefault();
            if (typeof openQuoteWizard === 'function') {
                // Try to extract service type from context if possible
                let serviceHint = null;
                const h1 = document.querySelector('h1')?.innerText?.toLowerCase() || '';
                if (h1.includes('otel')) serviceHint = 'otel';
                else if (h1.includes('yemek')) serviceHint = 'yemek';
                else if (h1.includes('mimari') || h1.includes('mekan')) serviceHint = 'mimari';

                openQuoteWizard(serviceHint);
            }
        }
    });
});
