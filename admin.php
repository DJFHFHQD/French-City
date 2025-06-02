<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'assets/inc/db_connect.php';

// Gestion des actions
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === 'settings') {
        $site_title = $_POST['site_title'];
        $logo_type = $_POST['logo_type'];
        $logo_text = $_POST['logo_text'] ?? '';
        $logo_image = '';

        if ($logo_type === 'image' && isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            $logo_image = $upload_dir . basename($_FILES['logo_image']['name']);
            move_uploaded_file($_FILES['logo_image']['tmp_name'], $logo_image);
        }

        $pdo->prepare("UPDATE settings SET site_title = ?, logo_type = ?, logo_text = ?, logo_image = ? WHERE id = 1")
            ->execute([$site_title, $logo_type, $logo_text, $logo_image]);
        $message = 'Paramètres mis à jour avec succès.';
    } elseif ($page === 'nav') {
        $pdo->exec("TRUNCATE TABLE nav_links");
        foreach ($_POST['nav_name'] as $index => $name) {
            $section_id = $_POST['nav_section_id'][$index];
            $pdo->prepare("INSERT INTO nav_links (name, section_id) VALUES (?, ?)")
                ->execute([$name, $section_id]);
        }
        $message = 'Liens de navigation mis à jour avec succès.';
    } elseif ($page === 'hero') {
        $title = $_POST['hero_title'];
        $description = $_POST['hero_description'];
        $server_ip = $_POST['server_ip'];
        $pdo->prepare("UPDATE hero SET title = ?, description = ?, server_ip = ? WHERE id = 1")
            ->execute([$title, $description, $server_ip]);
        $message = 'Section Hero mise à jour avec succès.';
    } elseif ($page === 'rules') {
        $pdo->exec("TRUNCATE TABLE rules");
        foreach ($_POST['rule_title'] as $index => $title) {
            $description = $_POST['rule_description'][$index];
            $pdo->prepare("INSERT INTO rules (title, description) VALUES (?, ?)")
                ->execute([$title, $description]);
        }
        $message = 'Règles mises à jour avec succès.';
    } elseif ($page === 'gallery') {
        if (isset($_FILES['gallery_images'])) {
            $upload_dir = 'uploads/';
            foreach ($_FILES['gallery_images']['name'] as $index => $name) {
                if ($_FILES['gallery_images']['error'][$index] === UPLOAD_ERR_OK) {
                    $image_url = $upload_dir . basename($name);
                    move_uploaded_file($_FILES['gallery_images']['tmp_name'][$index], $image_url);
                    $alt_text = $_POST['gallery_alt_text'][$index];
                    $pdo->prepare("INSERT INTO gallery (image_url, alt_text) VALUES (?, ?)")
                        ->execute([$image_url, $alt_text]);
                }
            }
            $message = 'Images ajoutées à la galerie.';
        }
    } elseif ($page === 'staff') {
        $pdo->exec("TRUNCATE TABLE staff");
        foreach ($_POST['staff_name'] as $index => $name) {
            $role = $_POST['staff_role'][$index];
            $image_url = '';
            if (isset($_FILES['staff_image']['name'][$index]) && $_FILES['staff_image']['error'][$index] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                $image_url = $upload_dir . basename($_FILES['staff_image']['name'][$index]);
                move_uploaded_file($_FILES['staff_image']['tmp_name'][$index], $image_url);
            }
            $pdo->prepare("INSERT INTO staff (name, role, image_url) VALUES (?, ?, ?)")
                ->execute([$name, $role, $image_url]);
        }
        $message = 'Staff mis à jour avec succès.';
    } elseif ($page === 'steam') {
        $api_key = $_POST['api_key'];
        $server_ip = $_POST['server_ip'];
        $server_port = $_POST['server_port'];
        $pdo->prepare("UPDATE steam_api SET api_key = ?, server_ip = ?, server_port = ? WHERE id = 1")
            ->execute([$api_key, $server_ip, $server_port]);
        $message = 'Paramètres Steam API mis à jour avec succès.';
    } elseif ($page === 'social') {
        $pdo->exec("TRUNCATE TABLE social_links");
        foreach ($_POST['social_icon'] as $index => $icon) {
            $url = $_POST['social_url'][$index];
            $pdo->prepare("INSERT INTO social_links (icon_class, url) VALUES (?, ?)")
                ->execute([$icon, $url]);
        }
        $message = 'Réseaux sociaux mis à jour avec succès.';
    } elseif ($page === 'footer') {
        $copyright = $_POST['copyright'];
        $pdo->prepare("UPDATE footer SET copyright = ? WHERE id = 1")
            ->execute([$copyright]);
        $message = 'Footer mis à jour avec succès.';
    }
}

// Récupérer les données pour les formulaires
$settings = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();
$nav_links = $pdo->query("SELECT * FROM nav_links ORDER BY id")->fetchAll();
$hero = $pdo->query("SELECT * FROM hero LIMIT 1")->fetch();
$rules = $pdo->query("SELECT * FROM rules ORDER BY id")->fetchAll();
$gallery = $pdo->query("SELECT * FROM gallery ORDER BY id")->fetchAll();
$staff = $pdo->query("SELECT * FROM staff ORDER BY id")->fetchAll();
$steam_api = $pdo->query("SELECT * FROM steam_api LIMIT 1")->fetch();
$social_links = $pdo->query("SELECT * FROM social_links ORDER BY id")->fetchAll();
$footer = $pdo->query("SELECT * FROM footer LIMIT 1")->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Serveur GMod</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-server"></i>
            <span>Admin GMod</span>
            <span class="close-sidebar" id="closeSidebar">×</span>
        </div>
        <div class="sidebar-menu">
            <div class="sidebar-item">
                <a href="admin.php?page=dashboard" class="sidebar-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="admin.php?page=settings" class="sidebar-link <?php echo $page === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres généraux</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="admin.php?page=nav" class="sidebar-link <?php echo $page === 'nav' ? 'active' : ''; ?>">
                    <i class="fas fa-link"></i>
                    <span>Navigation</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="admin.php?page=hero" class="sidebar-link <?php echo $page === 'hero' ? 'active' : ''; ?>">
                    <i class="fas fa-scroll"></i>
                    <span>Hero</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="admin.php?page=rules" class="sidebar-link <?php echo $page === 'rules' ? 'active' : ''; ?>">
                    <i class="fas fa-book"></i>
                    <span>Règles</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="admin.php?page=gallery" class="sidebar-link <?php echo $page === 'gallery' ? 'active' : ''; ?>">
                    <i class="fas fa-camera"></i>
                    <span>Galerie</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="admin.php?page=staff" class="sidebar-link <?php echo $page === 'staff' ? 'active' : ''; ?>">
                    <i class="fas fa-user-shield"></i>
                    <span>Staff</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="admin.php?page=steam" class="sidebar-link <?php echo $page === 'steam' ? 'active' : ''; ?>">
                    <i class="fab fa-steam"></i>
                    <span>Serveur</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="admin.php?page=social" class="sidebar-link <?php echo $page === 'social' ? 'active' : ''; ?>">
                    <i class="fas fa-share-alt"></i>
                    <span>Réseaux sociaux</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="admin.php?page=footer" class="sidebar-link <?php echo $page === 'footer' ? 'active' : ''; ?>">
                    <i class="fas fa-copyright"></i>
                    <span>Footer</span>
                </a>
            </div>
            <div class="sidebar-item">
                <a href="logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Overlay Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Top Bar -->
    <div class="topbar">
        <div class="hamburger" id="hamburger">
            <i class="fas fa-bars"></i>
        </div>
        <div class="user-menu">
                <?php if ($settings['logo_type'] === 'image' && !empty($settings['logo_image'])): ?>
                    <img src="<?php echo htmlspecialchars($settings['logo_image']); ?>" alt="Logo" style="max-height: 50px;">
                <?php else: ?>
                    <?php echo $settings['logo_text']; ?>
                <?php endif; ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($page === 'dashboard'): ?>
            <!-- Dashboard -->
            <div class="card">
                <div class="card-header">
                    <h3>Joueurs Connectés</h3>
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-body">
                    <?php
                    $apiUrl = "https://api.steampowered.com/IGameServersService/GetServerList/v1/?key={$steam_api['api_key']}&filter=\\appid\\{$steam_api['app_id']}\\addr\\{$steam_api['server_ip']}:{$steam_api['server_port']}";
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
                    ?>
                    <div class="stat">
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-info">
                            <h4><?php echo $players; ?></h4>
                            <p>/ <?php echo $maxPlayers; ?> slots</p>
                        </div>
                    </div>
                    <p>Serveur: <span style="color: <?php echo $status === 'En ligne' ? '#4CAF50' : ($status === 'Erreur de connexion à l\'API Steam' ? '#ff9800' : '#f44336'); ?>; font-weight: bold;"><?php echo htmlspecialchars($status); ?></span></p>
                </div>
            </div>

            <!-- Tableau Staff -->
            <div class="card" style="margin-top: 50px;">
            <div class="card-header">
                <h3>Staff enregistré</h3>
                                    <i class="fas fa-user-shield"></i>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Pseudo</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff as $member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                                <td><span class="badge badge-success"><?php echo htmlspecialchars($member['role']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 15px; text-align: center; opacity: 20%;">
            <p><a href="https://neycruzz.fr" style="text-decoration: none;">&copy; GmodWeb – Admin v1.0</a></p>
            </div>

        <?php elseif ($page === 'settings'): ?>
            <!-- Paramètres généraux -->
            <div class="card">
                <div class="card-header">
                <h3>Paramètres généraux</h3>
                    <i class="fas fa-cog"></i>
                </div>
                
                <div class="card-body">
                <form method="POST" enctype="multipart/form-data" style="background-color: #fafafa; margin-top: 3px; padding: 5px; border-radius: 10px;">
                    <div class="form-group" style="margin-bottom: 5px;">
                        <label for="site_title">Titre du site</label>
                        <input type="text" id="site_title" name="site_title" value="<?php echo htmlspecialchars($settings['site_title']); ?>" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 5px;">
                        <label for="logo_type">Type de logo</label>
                        <select id="logo_type" name="logo_type" onchange="toggleLogoFields()">
                            <option value="text" <?php echo $settings['logo_type'] === 'text' ? 'selected' : ''; ?>>Texte</option>
                            <option value="image" <?php echo $settings['logo_type'] === 'image' ? 'selected' : ''; ?>>Image</option>
                        </select>
                    </div>
                    <div class="form-group" id="logo_text_group" style="<?php echo $settings['logo_type'] === 'text' ? '' : 'display: none;'; ?>">
                        <label for="logo_text">Texte du logo (HTML autorisé)</label>
                        <textarea id="logo_text" name="logo_text"><?php echo htmlspecialchars($settings['logo_text']); ?></textarea>
                    </div>
                    <div class="form-group" id="logo_image_group" style="<?php echo $settings['logo_type'] === 'image' ? '' : 'display: none;'; ?>">
                        <label for="logo_image">Image du logo</label>
                        <input type="file" id="logo_image" name="logo_image" accept="image/*">
                        <?php if ($settings['logo_image']): ?>
                            <img src="<?php echo htmlspecialchars($settings['logo_image']); ?>" alt="Logo" style="max-width: 100px; margin-top: 10px;">
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn" style="margin-top: 7px;">Enregistrer</button>
                </form>
            </div>
        </div>
        <?php elseif ($page === 'nav'): ?>
            <!-- Navigation -->
            <div class="card">
                <div class="card-header">
                <h3>Lien de navigation</h3>
                    <i class="fas fa-link"></i>
                </div>
                
                <div class="card-body">
                <form method="POST">
                    <div class="dynamic-list" id="nav-list">
                        <?php foreach ($nav_links as $link): ?>
                            <div class="item" style="background-color: #fafafa; margin-top: 3px; padding: 5px; border-radius: 10px;">
                                <input type="text" name="nav_name[]" value="<?php echo htmlspecialchars($link['name']); ?>" placeholder="Nom" required>
                                <input type="text" name="nav_section_id[]" value="<?php echo htmlspecialchars($link['section_id']); ?>" placeholder="ID de section" required>
                                <button type="button" class="btn btn-remove" onclick="removeItem(this)">×</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn" onclick="addNavItem()">Ajouter un lien</button>
                    <button type="submit" class="btn">Enregistrer</button>
                </form>
            </div>
         </div>

        <?php elseif ($page === 'hero'): ?>
            <!-- Hero -->
            <div class="card">
                <div class="card-header">
                <h3>Hero</h3>
                    <i class="fas fa-scroll"></i>
                </div>
                
                <div class="card-body">
                <form method="POST" style="background-color: #fafafa; margin-top: 3px; padding: 5px; border-radius: 10px;">
                    <div class="form-group" style="display: flex; align-items: center; margin-bottom: 10px;">
                        <label for="hero_title" style="margin-right: 5px;">Titre</label>
                        <input type="text" id="hero_title" name="hero_title" value="<?php echo htmlspecialchars($hero['title']); ?>" required>
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; margin-bottom: 10px;">
                        <label for="hero_description" style="margin-right: 5px;">Description</label>
                        <textarea id="hero_description" name="hero_description" required><?php echo htmlspecialchars($hero['description']); ?></textarea>
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; margin-bottom: 10px;">
                        <label for="server_ip" style="margin-right: 5px;">IP Serveur</label>
                        <input type="text" id="server_ip" name="server_ip" value="<?php echo htmlspecialchars($hero['server_ip']); ?>" required>
                    </div>
                    <button type="submit" class="btn">Enregistrer</button>
                </form>
            </div>
        </div>

        <?php elseif ($page === 'rules'): ?>
            <!-- Règles -->
            <div class="card">
                <div class="card-header">
                <h3>Règles</h3>
                    <i class="fas fa-book"></i>
                </div>
                
                <div class="card-body">
                <form method="POST">
                    <div class="dynamic-list" id="rules-list">
                        <?php foreach ($rules as $rule): ?>
                            <div class="item" style="background-color: #fafafa; margin-top: 3px; padding: 5px; border-radius: 10px; display: flex; align-items: center; justify-content: space-between;">
                                <input type="text" name="rule_title[]" value="<?php echo htmlspecialchars($rule['title']); ?>" placeholder="Titre" required>
                                <textarea name="rule_description[]" placeholder="Description" required><?php echo htmlspecialchars($rule['description']); ?></textarea>
                                <button type="button" class="btn btn-remove" onclick="removeItem(this)">×</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn" onclick="addRuleItem()">Ajouter une règle</button>
                    <button type="submit" class="btn">Enregistrer</button>
                </form>
            </div>
        </div>

        <?php elseif ($page === 'gallery'): ?>
            <!-- Galerie -->
            <div class="card">
                <div class="card-header">
                <h3>Galerie</h3>
                    <i class="fas fa-camera"></i>
                </div>
                                
                <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="dynamic-list" id="gallery-list">
                        <div class="item">
                            <input type="file" name="gallery_images[]" accept="image/*" required>
                            <input type="text" name="gallery_alt_text[]" placeholder="Texte alternatif" required>
                            <button type="button" class="btn btn-remove" onclick="removeItem(this)">×</button>
                        </div>
                    </div>
                    <button type="button" class="btn" onclick="addGalleryItem()">Ajouter une image</button>
                    <button type="submit" class="btn">Enregistrer</button>
                </form>
                <hr style="margin: 10px;">
                <h4 style="text-align: center; padding-bottom: 3px;">Images existantes</h4>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <?php foreach ($gallery as $image): ?>
                        <div style="position: relative;">
                            <img src="<?php echo htmlspecialchars($image['image_url']); ?>" alt="<?php echo htmlspecialchars($image['alt_text']); ?>" style="max-width: 100px;">
                            <form method="POST" action="admin.php?page=gallery&action=delete_image&id=<?php echo $image['id']; ?>">
                                <button type="submit" class="btn btn-remove" style="position: absolute; top: 0; right: 0;">×</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php elseif ($page === 'staff'): ?>
            <!-- Staff -->
            <div class="card">
                <div class="card-header">
                <h3>Staff</h3>
                    <i class="fas fa-user-shield"></i>
                </div>
                
                <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="dynamic-list" id="staff-list">
                        <?php foreach ($staff as $member): ?>
                            <div class="item" style="background-color: #fafafa; margin-top: 3px; padding: 5px; border-radius: 10px;">
                                <input type="text" name="staff_name[]" value="<?php echo htmlspecialchars($member['name']); ?>" placeholder="Nom" required>
                                <input type="text" name="staff_role[]" value="<?php echo htmlspecialchars($member['role']); ?>" placeholder="Rôle" required>
                                <input type="file" name="staff_image[]" accept="image/*">
                                <button type="button" class="btn btn-remove" onclick="removeItem(this)">×</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="margin-top: 3px;">
                    <button type="button" class="btn" onclick="addStaffItem()">Ajouter un membre</button>
                    <button type="submit" class="btn">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <?php elseif ($page === 'steam'): ?>
            <!-- Steam API -->
            <div class="card">
                <div class="card-header">
                <h3>Paramètres API</h3>
                    <i class="fab fa-steam"></i>
                </div>
                
                <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="api_key">Clé API Steam</label>
                        <input type="text" id="api_key" name="api_key" value="<?php echo htmlspecialchars($steam_api['api_key']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="server_ip">IP du serveur</label>
                        <input type="text" id="server_ip" name="server_ip" value="<?php echo htmlspecialchars($steam_api['server_ip']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="server_port">Port du serveur</label>
                        <input type="text" id="server_port" name="server_port" value="<?php echo htmlspecialchars($steam_api['server_port']); ?>" required>
                    </div>
                    <button type="submit" class="btn">Enregistrer</button>
                </form>
            </div>
        </div>
        <center>
<p style="
background-color: orange; color: white; max-width: 250px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); margin-top: 5px;
">
<i class="fa fa-warning" style="max-width:250px; margin-top: 5px;"> ATTENTION <i class="fa fa-warning"> </i></i><br>
Pour avoir votre Clé steam, vous devez aller sur le lien suivant: <a href="https://steamcommunity.com/dev/apikey">API Steam</a>, et suivez les instructions par Steam.
<br><br>
Ensuite, mettez l'IP de votre serveur dans la case IP, et le port (par defaut 27015, mais peut être different selon les hébergeurs de serveur) dans port
</p>
</center>

        <?php elseif ($page === 'social'): ?>
            <!-- Réseaux sociaux -->
            <div class="card">
                <div class="card-header">
                <h3>Réseaux Sociaux</h3>
                    <i class="fas fa-share-alt"></i>
                </div>
                
                <div class="card-body">
                <form method="POST">
                    <div class="dynamic-list" id="social-list">
                        <?php foreach ($social_links as $social): ?>
                            <div class="item" style="background-color: #fafafa; margin-top: 3px; padding: 5px; border-radius: 10px;">
                                <input type="text" name="social_icon[]" value="<?php echo htmlspecialchars($social['icon_class']); ?>" placeholder="Classe FontAwesome (ex: fab fa-discord)" required>
                                <input type="url" name="social_url[]" value="<?php echo htmlspecialchars($social['url']); ?>" placeholder="URL" required>
                                <button type="button" class="btn btn-remove" onclick="removeItem(this)">×</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn" onclick="addSocialItem()">Ajouter un réseau</button>
                    <button type="submit" class="btn">Enregistrer</button>
                </form>
            </div>
        </div>

        <?php elseif ($page === 'footer'): ?>
            <!-- Footer -->
            <div class="card">
                <div class="card-header">
                <h3>Footer</h3>
                    <i class="fas fa-copyright"></i>
                </div>
                
                <div class="card-body">
                <form method="POST">
                    <div class="form-group" style="text-align: center;">
                        <label for="copyright">Copyright (HTML autorisé)</label>
                        <textarea id="copyright" name="copyright" style="width: 100%; margin-top: 3px;" required><?php echo htmlspecialchars($footer['copyright']); ?></textarea>
                    </div>
                    <button type="submit" class="btn">Enregistrer</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Gestion du menu hamburger
        const sidebar = document.getElementById('sidebar');
        const hamburger = document.getElementById('hamburger');
        const closeSidebar = document.getElementById('closeSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        hamburger.addEventListener('click', () => {
            sidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        closeSidebar.addEventListener('click', () => {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        });

        // Gestion des champs de logo
        function toggleLogoFields() {
            const logoType = document.getElementById('logo_type').value;
            document.getElementById('logo_text_group').style.display = logoType === 'text' ? 'block' : 'none';
            document.getElementById('logo_image_group').style.display = logoType === 'image' ? 'block' : 'none';
        }

        // Gestion des listes dynamiques
        function addNavItem() {
            const list = document.getElementById('nav-list');
            const item = document.createElement('div');
            item.className = 'item';
            item.innerHTML = `
                <input type="text" name="nav_name[]" placeholder="Nom" required>
                <input type="text" name="nav_section_id[]" placeholder="ID de section" required>
                <button type="button" class="btn btn-remove" onclick="removeItem(this)">×</button>
            `;
            list.appendChild(item);
        }

        function addRuleItem() {
            const list = document.getElementById('rules-list');
            const item = document.createElement('div');
            item.className = 'item';
            item.innerHTML = `
                <input type="text" name="rule_title[]" placeholder="Titre" required>
                <textarea name="rule_description[]" placeholder="Description" required></textarea>
                <button type="button" class="btn btn-remove" onclick="removeItem(this)">×</button>
            `;
            list.appendChild(item);
        }

        function addGalleryItem() {
            const list = document.getElementById('gallery-list');
            const item = document.createElement('div');
            item.className = 'item';
            item.innerHTML = `
                <input type="file" name="gallery_images[]" accept="image/*" required>
                <input type="text" name="gallery_alt_text[]" placeholder="Texte alternatif" required>
                <button type="button" class="btn btn-remove" onclick="removeItem(this)">×</button>
            `;
            list.appendChild(item);
        }

        function addStaffItem() {
            const list = document.getElementById('staff-list');
            const item = document.createElement('div');
            item.className = 'item';
            item.innerHTML = `
                <input type="text" name="staff_name[]" placeholder="Nom" required>
                <input type="text" name="staff_role[]" placeholder="Rôle" required>
                <input type="file" name="staff_image[]" accept="image/*">
                <button type="button" class="btn btn-remove" onclick="removeItem(this)">×</button>
            `;
            list.appendChild(item);
        }

        function addSocialItem() {
            const list = document.getElementById('social-list');
            const item = document.createElement('div');
            item.className = 'item';
            item.innerHTML = `
                <input type="text" name="social_icon[]" placeholder="Classe FontAwesome (ex: fab fa-discord)" required>
                <input type="url" name="social_url[]" placeholder="URL" required>
                <button type="button" class="btn btn-remove" onclick="removeItem(this)">×</button>
            `;
            list.appendChild(item);
        }

        function removeItem(button) {
            button.parentElement.remove();
        }

        // Adaptation dynamique au resize
        function handleResize() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        }

        window.addEventListener('resize', handleResize);
    </script>
</body>
</html>