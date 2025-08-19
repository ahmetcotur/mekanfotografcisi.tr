// DOM elementlerini seçme
const header = document.querySelector('.header');
const hamburger = document.querySelector('.hamburger');
const navLinks = document.querySelector('.nav-links');
const navLinksItems = document.querySelectorAll('.nav-links a');
const portfolioFilterBtns = document.querySelectorAll('.filter-btn');
const portfolioItems = document.querySelectorAll('.portfolio-item');
const contactForm = document.querySelector('.contact-form');

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    // Mobil menü toggle
    hamburger.addEventListener('click', function() {
        this.classList.toggle('active');
        navLinks.classList.toggle('active');
    });

    // Menü linklerine tıklandığında mobil menüyü kapatma
    navLinksItems.forEach(item => {
        item.addEventListener('click', function() {
            hamburger.classList.remove('active');
            navLinks.classList.remove('active');
        });
    });

    // Kaydırma olayı - header stilini değiştirme
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Portfolyo Filtreleme
    portfolioFilterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
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

    // Form gönderimi
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Form verilerini alma
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const service = document.getElementById('service').value;
            const location = document.getElementById('location').value;
            const message = document.getElementById('message').value;
            
            // Basit doğrulama
            if (name && email && service && message) {
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
                                <p>Seçilen hizmet: ${service}</p>
                                <p>Seçilen lokasyon: ${location || 'Belirtilmedi'}</p>
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
                const errorMessage = document.createElement('div');
                errorMessage.className = 'form-error';
                errorMessage.innerHTML = 'Lütfen tüm zorunlu alanları doldurunuz.';
                
                // Önceki hata mesajlarını temizle
                const oldError = contactForm.querySelector('.form-error');
                if (oldError) {
                    contactForm.removeChild(oldError);
                }
                
                // Hata mesajını formun üstüne ekle
                contactForm.insertBefore(errorMessage, contactForm.firstChild);
                
                // Doldurulmamış alanları işaretle
                const requiredFields = contactForm.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value) {
                        field.classList.add('error');
                    } else {
                        field.classList.remove('error');
                    }
                });
            }
        });
        
        // Input alanları değiştiğinde hata sınıfını kaldır
        const formInputs = contactForm.querySelectorAll('input, textarea, select');
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.value) {
                    this.classList.remove('error');
                }
            });
        });
    }

    // Sayfa içi bağlantılarda yumuşak kaydırma
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            
            if (targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    const headerHeight = header.offsetHeight;
                    const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
                    
                    window.scrollTo({
                        top: targetPosition - headerHeight,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Animasyonlar için Görünür olma kontrolü
    function checkVisible() {
        const elements = document.querySelectorAll('.section');
        
        elements.forEach(element => {
            const position = element.getBoundingClientRect();
            
            if (position.top < window.innerHeight - 100) {
                element.classList.add('visible');
            }
        });
    }
    
    // Sayfa yüklendiğinde ve kaydırıldığında görünürlük kontrolü
    checkVisible();
    window.addEventListener('scroll', checkVisible);
}); 