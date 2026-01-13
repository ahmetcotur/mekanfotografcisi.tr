-- Pexels Images Management Table
CREATE TABLE IF NOT EXISTS pexels_images (
    id SERIAL PRIMARY KEY,
    image_url TEXT NOT NULL,
    photographer TEXT,
    pexels_id INTEGER,
    is_visible BOOLEAN DEFAULT true,
    display_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for faster queries
CREATE INDEX IF NOT EXISTS idx_pexels_visible ON pexels_images(is_visible, display_order);

-- Insert default Pexels images from homepage
INSERT INTO pexels_images (image_url, photographer, is_visible, display_order) VALUES
('https://images.pexels.com/photos/35069538/pexels-photo-35069538.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', 'Ahmet ÇÖTÜR', true, 1),
('https://images.pexels.com/photos/35069535/pexels-photo-35069535.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', 'Ahmet ÇÖTÜR', true, 2),
('https://images.pexels.com/photos/35069532/pexels-photo-35069532.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', 'Ahmet ÇÖTÜR', true, 3),
('https://images.pexels.com/photos/35069529/pexels-photo-35069529.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', 'Ahmet ÇÖTÜR', true, 4),
('https://images.pexels.com/photos/35060260/pexels-photo-35060260.jpeg?auto=compress&cs=tinysrgb&h=650&w=940', 'Ahmet ÇÖTÜR', true, 5)
ON CONFLICT DO NOTHING;
