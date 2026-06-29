<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : '';
$userId = $isLoggedIn ? $_SESSION['user_id'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlogIg</title>
    <!-- FrankenUI CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/franken-ui@2.1.2/dist/css/core.min.css" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <!-- UIkit JS -->
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.21.6/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.21.6/dist/js/uikit-icons.min.js"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 font-sans antialiased">

<nav class="uk-navbar-container bg-white border-b border-slate-200">
    <div class="max-w-5xl mx-auto px-4" uk-navbar>
        <div class="uk-navbar-left">
            <a class="uk-navbar-item uk-logo font-bold text-xl tracking-tight text-slate-800" href="index.php">
                BlogIg
            </a>
        </div>
        <div class="uk-navbar-right">
            <ul class="uk-navbar-nav flex items-center gap-1">
                <li><a href="index.php" class="text-slate-600 hover:text-slate-900 font-medium px-3 py-2 rounded-md hover:bg-slate-100 transition">Home</a></li>
                <?php if ($isLoggedIn): ?>
                    <li><a href="create.php" class="text-slate-600 hover:text-slate-900 font-medium px-3 py-2 rounded-md hover:bg-slate-100 transition">Write Post</a></li>
                    <li><a href="profile.php?id=<?= $userId ?>" class="text-slate-600 hover:text-slate-900 font-medium px-3 py-2 rounded-md hover:bg-slate-100 transition">Profile (<?= htmlspecialchars($username) ?>)</a></li>
                    <li><a href="logout.php" class="text-rose-600 hover:text-rose-700 font-medium px-3 py-2 rounded-md hover:bg-rose-50 transition">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="text-slate-600 hover:text-slate-900 font-medium px-3 py-2 rounded-md hover:bg-slate-100 transition">Login</a></li>
                    <li><a href="register.php" class="bg-indigo-600 text-white hover:bg-indigo-700 font-medium px-4 py-2 rounded-md transition flex items-center">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="max-w-4xl mx-auto px-4 py-8">
