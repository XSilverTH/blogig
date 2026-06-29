<?php
require_once 'db.php';

$errors = [];
$registered = isset($_GET['registered']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors[] = 'Email is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('SELECT * FROM "User" WHERE "Email" = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['PasswordHash'])) {
                $_SESSION['user_id'] = $user['Id'];
                $_SESSION['username'] = $user['Username'];
                
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Login failed: ' . $e->getMessage();
        }
    }
}

require_once 'header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg border border-slate-200 shadow-sm mt-12">
    <h1 class="text-2xl font-bold text-slate-900 mb-6 text-center">Login</h1>

    <?php if ($registered): ?>
        <div class="uk-alert-success mb-6 p-4 rounded-md" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p class="text-sm font-medium text-green-700">Registered successfully. Login below.</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="uk-alert-danger mb-6 p-4 rounded-md" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <ul class="list-disc list-inside text-sm text-red-700">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="email">Email</label>
            <input class="uk-input w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="password">Password</label>
            <input class="uk-input w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md transition shadow-sm mt-6">
            Login
        </button>
    </form>

    <p class="text-center text-sm text-slate-600 mt-6">
        Don't have an account? <a href="register.php" class="text-indigo-600 hover:text-indigo-800 font-semibold transition">Register</a>
    </p>
</div>

<?php
require_once 'footer.php';
?>
