<?php
session_start();
require_once 'assets/inc/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Identifiants incorrects.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Admin GMod</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <style>
    </style>
</head>
<body>
    <div class="login-container" id="loginContainer">
        <div class="login-header">
            <img src="https://placehold.co/150" alt="Logo Serveur">
            <h1>Connexion Admin</h1>
        </div>

        <?php if (isset($error)): ?>
            <p style="color: #f44336; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form id="loginForm" method="POST">
            <div class="form-group" id="usernameGroup">
                <label for="username">Nom d'utilisateur</label>
                <div class="input-with-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" class="form-control" placeholder="admin" required>
                </div>
                <span class="error-message">Veuillez saisir un nom d'utilisateur valide</span>
            </div>

            <div class="form-group" id="passwordGroup">
                <label for="password">Mot de passe</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                    <i class="fas fa-eye toggle-password"></i>
                </div>
                <span class="error-message">Le mot de passe doit contenir au moins 8 caractères</span>
            </div>

            <button type="submit" class="btn-login">
                <span id="loginText">Se connecter</span>
                <span id="loginSpinner" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Connexion...
                </span>
            </button>
        </form>
    </div>

    <script>
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        const loginForm = document.getElementById('loginForm');
        const loginContainer = document.getElementById('loginContainer');
        const loginText = document.getElementById('loginText');
        const loginSpinner = document.getElementById('loginSpinner');

        loginForm.addEventListener('submit', function(e) {
            document.getElementById('usernameGroup').classList.remove('error');
            document.getElementById('passwordGroup').classList.remove('error');

            let isValid = true;
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (username.length < 3) {
                document.getElementById('usernameGroup').classList.add('error');
                isValid = false;
            }

            if (password.length < 8) {
                document.getElementById('passwordGroup').classList.add('error');
                isValid = false;
            }

            if (!isValid) {
                loginContainer.classList.add('shake');
                setTimeout(() => loginContainer.classList.remove('shake'), 500);
                e.preventDefault();
            } else {
                loginText.style.display = 'none';
                loginSpinner.style.display = 'inline';
            }
        });

        setTimeout(() => {
            loginContainer.style.opacity = '1';
        }, 100);
    </script>
</body>
</html>