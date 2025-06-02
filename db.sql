-- Table pour les configurations générales (nom du serveur, logo, etc.)
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_title VARCHAR(255) NOT NULL,
    logo_type ENUM('text', 'image') DEFAULT 'text',
    logo_text TEXT,
    logo_image VARCHAR(255)
);

-- Table pour les liens de navigation
CREATE TABLE nav_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    section_id VARCHAR(100) NOT NULL
);

-- Table pour la section Hero
CREATE TABLE hero (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    server_ip VARCHAR(100) NOT NULL
);

-- Table pour les règles
CREATE TABLE rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL
);

-- Table pour la galerie
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_url VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255)
);

-- Table pour le staff
CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100) NOT NULL,
    image_url VARCHAR(255)
);

-- Table pour les paramètres Steam API
CREATE TABLE steam_api (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key VARCHAR(100) NOT NULL,
    server_ip VARCHAR(100) NOT NULL,
    server_port VARCHAR(10) NOT NULL,
    app_id VARCHAR(10) DEFAULT '4000'
);

-- Table pour les réseaux sociaux
CREATE TABLE social_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    icon_class VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL
);

-- Table pour le footer
CREATE TABLE footer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    copyright TEXT NOT NULL
);

-- Table pour les utilisateurs admin
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Configuration par défaut
INSERT INTO settings (site_title, logo_type, logo_text) 
VALUES ('Serveur GMod | NomDuServeur', 'text', 'GMod<span style="color: #fff;">Server</span>');

-- Liens de navigation par défaut
INSERT INTO nav_links (name, section_id) VALUES 
('Accueil', 'home'),
('Règles', 'rules'),
('Galerie', 'gallery'),
('Staff', 'staff'),
('Serveur', 'server');

-- Hero par défaut
INSERT INTO hero (title, description, server_ip) 
VALUES ('Bienvenue sur notre serveur Garry\'s Mod !', 
'Rejoignez une communauté active et profitez d\'une expérience de jeu unique avec des modes variés et un staff à l\'écoute.', 
'ipduserveur:port');

-- Règles par défaut
INSERT INTO rules (title, description) VALUES 
('Respect', 'Tous les joueurs doivent se respecter mutuellement. Aucune forme de harcèlement ou de discrimination n\'est tolérée.'),
('No Cheat', 'L\'utilisation de cheats, hacks ou exploits est strictement interdite et entraînera un bannissement permanent.'),
('RP Serious', 'Le RP sérieux est obligatoire dans les modes concernés. Le troll et le non-RP seront sanctionnés.');

-- Galerie par défaut
INSERT INTO gallery (image_url, alt_text) VALUES 
('https://placehold.co/800x400', 'Screenshot 1'),
('https://placehold.co/800x400', 'Screenshot 2'),
('https://placehold.co/800x400', 'Screenshot 3');

-- Staff par défaut
INSERT INTO staff (name, role, image_url) VALUES 
('NeyCruzz', 'Fondateur', 'https://placehold.co/300x200'),
('Sarah', 'Administratrice', 'https://placehold.co/300x200'),
('Max', 'Modérateur', 'https://placehold.co/300x200');

-- Steam API par défaut
INSERT INTO steam_api (api_key, server_ip, server_port, app_id) 
VALUES ('000000000000000', '0000.00.000.00', '27015', '4000');

-- Réseaux sociaux par défaut
INSERT INTO social_links (icon_class, url) VALUES 
('fab fa-discord', 'https://discord.gg/fUQnwYwV3K'),
('fab fa-steam', '#'),
('fab fa-youtube', '#');

-- Footer par défaut
INSERT INTO footer (copyright) 
VALUES ('© 2025 GmodWeb. Tous droits réservés. neycruzz.fr');

-- Admin par défaut (mot de passe : password, haché avec password_hash)
INSERT INTO admins (username, password) 
VALUES ('admin', '$2y$10$TRRdMV3hTPSm9/i7cLLe1.cUhoNBjI9XB3Ay.xN683eexYI1WuMsy');