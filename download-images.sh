#!/bin/bash

# Görselleri indirmek için klasörü oluştur
mkdir -p assets/images

# Hero görseli
curl -o assets/images/hero-bg.jpg "https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=2000"

# Hakkımızda görseli
curl -o assets/images/about-us.jpg "https://images.unsplash.com/photo-1556761175-b413da4baf72?q=80&w=1000"

# Hizmet görselleri
curl -o assets/images/mimari-fotograf.jpg "https://images.unsplash.com/photo-1487958449943-2429e8be8625?q=80&w=400"
curl -o assets/images/ic-mekan.jpg "https://images.unsplash.com/photo-1618219908412-a29a1bb7b86e?q=80&w=400"
curl -o assets/images/emlak-fotograf.jpg "https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=400"
curl -o assets/images/otel-restoran.jpg "https://images.unsplash.com/photo-1414235077428-338989a2e8c0?q=80&w=400"

# Portfolyo görselleri
curl -o assets/images/portfolio-1.jpg "https://images.unsplash.com/photo-1479839672679-a46483c0e7c8?q=80&w=600"
curl -o assets/images/portfolio-2.jpg "https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?q=80&w=600"
curl -o assets/images/portfolio-3.jpg "https://images.unsplash.com/photo-1615874959474-d609969a20ed?q=80&w=600"
curl -o assets/images/portfolio-4.jpg "https://images.unsplash.com/photo-1564501049412-61c2a3083791?q=80&w=600"
curl -o assets/images/portfolio-5.jpg "https://images.unsplash.com/photo-1497366754035-f200968a6e72?q=80&w=600"
curl -o assets/images/portfolio-6.jpg "https://images.unsplash.com/photo-1515669097368-22e68427d265?q=80&w=600"

# Müşteri yorumları görselleri
curl -o assets/images/testimonial-1.jpg "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=200"
curl -o assets/images/testimonial-2.jpg "https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=200"
curl -o assets/images/testimonial-3.jpg "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=200"

echo "Tüm görseller indirildi." 