<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'assets/inc/db_connect.php'; // Fichier de connexion à la base de données

// Récupérer les configurations générales
$settings = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();

// Récupérer les liens de navigation
$nav_links = $pdo->query("SELECT * FROM nav_links ORDER BY id")->fetchAll();

// Récupérer la section Hero
$hero = $pdo->query("SELECT * FROM hero LIMIT 1")->fetch();

// Récupérer les règles
$rules = $pdo->query("SELECT * FROM rules ORDER BY id")->fetchAll();

// Récupérer la galerie
$gallery = $pdo->query("SELECT * FROM gallery ORDER BY id")->fetchAll();

// Récupérer le staff
$staff = $pdo->query("SELECT * FROM staff ORDER BY id")->fetchAll();

// Récupérer les paramètres Steam API
$steam_api = $pdo->query("SELECT * FROM steam_api LIMIT 1")->fetch();

// Récupérer les réseaux sociaux
$social_links = $pdo->query("SELECT * FROM social_links ORDER BY id")->fetchAll();

// Récupérer le footer
$footer = $pdo->query("SELECT * FROM footer LIMIT 1")->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<!-- 
#################
Site web by NeyCruzz (neycruzz.fr)
#################
-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_title']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <?php if ($settings['logo_type'] === 'image' && !empty($settings['logo_image'])): ?>
                    <img src="<?php echo htmlspecialchars($settings['logo_image']); ?>" alt="Logo" style="max-height: 50px;">
                <?php else: ?>
                    <?php echo $settings['logo_text']; ?>
                <?php endif; ?>
            </div>
            <div class="nav-links">
                <?php foreach ($nav_links as $link): ?>
                    <a href="#<?php echo htmlspecialchars($link['section_id']); ?>">
                        <?php echo htmlspecialchars($link['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1><?php echo htmlspecialchars($hero['title']); ?></h1>
            <p><?php echo htmlspecialchars($hero['description']); ?></p>
            <a href="steam://connect/<?php echo htmlspecialchars($hero['server_ip']); ?>" class="btn">Rejoindre le serveur</a>
        </div>
    </section>

    <!-- Règles -->
    <section id="rules">
        <h2 class="section-title">Règles du serveur</h2>
        <div class="rules-container">
            <?php foreach ($rules as $rule): ?>
                <div class="rule-card">
                    <h3><?php echo htmlspecialchars($rule['title']); ?></h3>
                    <p><?php echo htmlspecialchars($rule['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Galerie -->
    <section class="gallery" id="gallery">
        <h2 class="section-title">Galerie</h2>
        <div class="slider">
            <div class="slides">
                <?php foreach ($gallery as $image): ?>
                    <div class="slide">
                        <img src="<?php echo htmlspecialchars($image['image_url']); ?>" alt="<?php echo htmlspecialchars($image['alt_text']); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="slider-btn" id="prevBtn"><i class="fas fa-chevron-left"></i></button>
            <button class="slider-btn" id="nextBtn"><i class="fas fa-chevron-right"></i></button>
        </div>
    </section>

    <!-- Staff -->
    <section id="staff">
        <h2 class="section-title">Notre Staff</h2>
        <div class="staff-grid">
            <?php foreach ($staff as $member): ?>
                <div class="staff-card">
                    <img src="<?php echo htmlspecialchars($member['image_url']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
                    <div class="staff-info">
                        <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                        <p><?php echo htmlspecialchars($member['role']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Countdown -->
    <section id="server">
        <h2 class="section-title">Serveur</h2>
        <?php
        $apiKey = $steam_api['api_key'];
        $serverIp = $steam_api['server_ip'];
        $serverPort = $steam_api['server_port'];
        $appId = $steam_api['app_id'];

        $apiUrl = "https://api.steampowered.com/IGameServersService/GetServerList/v1/?key={$apiKey}&filter=\\appid\\{$appId}\\addr\\{$serverIp}:{$serverPort}";

        $response = @file_get_contents($apiUrl);

        if ($response === false) {
            $status = "Erreur de connexion à l'API Steam";
            $players = 0;
            $maxPlayers = 1;
            $percentage = 0;
        } else {
            $data = json_decode($response, true);

            if ($data && isset($data['response']['servers'][0])) {
                $serverInfo = $data['response']['servers'][0];
                $status = "En ligne";
                $players = isset($serverInfo['players']) ? intval($serverInfo['players']) : 0;
                $maxPlayers = isset($serverInfo['max_players']) ? intval($serverInfo['max_players']) : 1;
                $percentage = ($maxPlayers > 0) ? round(($players / $maxPlayers) * 100) : 0;
            } else {
                $status = "Hors ligne";
                $players = 0;
                $maxPlayers = 1;
                $percentage = 0;
            }
        }

        // Affichage dans le HTML
        echo '<section class="status-section">';
        echo '    <div class="status-container" style="text-align: center;">';
        echo '        <p style="font-size: 1.2rem; color: #fff;">';
        echo '            Statut: <span id="server-status" style="color: ';
        echo $status === 'En ligne' ? '#4CAF50' : ($status === "Erreur de connexion à l'API Steam" ? '#ff9800' : '#f44336');
        echo '; font-weight: bold;">' . htmlspecialchars($status) . '</span>';
        echo '        </p>';
        echo '        <div class="progress-bar-container">';
        echo '            <div class="progress-bar" style="width: ' . $percentage . '%;"></div>';
        echo '            <span class="progress-text">' . $players . ' / ' . $maxPlayers . '</span>';
        echo '        </div>';
        echo '    </div>';
        echo '</section>';
        
// Style CSS
echo '<style>';
echo '.status-section {';
echo '    padding: 2rem 2rem;';
echo '    background: #16213e;';
echo '    text-align: center;';
echo '    margin-top: 20px;';
echo '}';

echo '.status-container {';
echo '    max-width: 600px;';
echo '    margin: 0 auto;';
echo '}';

echo '.progress-bar-container {';
echo '    background-color: #2c3e50;';
echo '    border-radius: 5px;';
echo '    height: 20px;';
echo '    margin: 10px 0;';
echo '    position: relative;';
echo '    overflow: hidden;'; /* Empêche le dépassement du texte arrondi */
echo '}';

echo '.progress-bar {';
echo '    background-color: #e94560;';
echo '    height: 100%;';
echo '    border-radius: 5px;';
echo '    width: 0%; /* La largeur sera définie dynamiquement */';
echo '    transition: width 0.3s ease-in-out;'; /* Animation de la barre */
echo '}';

echo '.progress-text {';
echo '    position: absolute;';
echo '    top: 50%;';
echo '    left: 50%;';
echo '    transform: translate(-50%, -50%);';
echo '    color: #fff;';
echo '    font-size: 0.9rem;';
echo '    font-weight: bold;';
echo '    white-space: nowrap; /* Empêche le texte de passer sur plusieurs lignes */';
echo '}';
echo '</style>';
        ?>
    </section>

    <!-- Footer -->
    <footer>
        <div class="social-links">
            <?php foreach ($social_links as $social): ?>
                <a href="<?php echo htmlspecialchars($social['url']); ?>">
                    <i class="<?php echo htmlspecialchars($social['icon_class']); ?>"></i>
                </a>
            <?php endforeach; ?>
        </div>
        <p><?php echo $footer['copyright']; ?></p>
    </footer>

    <!-- JavaScript -->
    <script>
        // Menu Mobile
        const menuBtn = document.querySelector('.menu-btn');
        const navLinks = document.querySelector('.nav-links');

        menuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });

        // Slider
        const slides = document.querySelector('.slides');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        let slideIndex = 0;
        const slideCount = <?php echo count($gallery); ?>;

        function showSlide(index) {
            slides.style.transform = `translateX(-${index * 100}%)`;
        }

        if (slideCount > 0) {
            nextBtn.addEventListener('click', () => {
                slideIndex = (slideIndex + 1) % slideCount;
                showSlide(slideIndex);
            });

            prevBtn.addEventListener('click', () => {
                slideIndex = (slideIndex - 1 + slideCount) % slideCount;
                showSlide(slideIndex);
            });
        }
    </script>
</body>
</html>