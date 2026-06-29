<?php
require_once 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $pfpName = null;

    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM "User" WHERE "Email" = ?');
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Email is already registered.';
        }
    }

    // Handle Profile Picture Upload
    if (empty($errors) && isset($_FILES['pfp']) && $_FILES['pfp']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['pfp'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Failed to upload profile picture.';
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($file['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = 'Only JPG, PNG, GIF, and WEBP images are allowed for profile picture.';
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                if (empty($ext)) {
                    $ext = 'jpg';
                }
                $pfpName = 'pfp_' . uniqid() . '.' . $ext;
                $targetPath = 'uploads/' . $pfpName;
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $errors[] = 'Failed to save uploaded profile picture.';
                }
            }
        }
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        try {
            $stmt = $pdo->prepare('
                INSERT INTO "User" ("Username", "Email", "PasswordHash", "Description", "Pfp", "CreatedAt")
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([$username, $email, $passwordHash, $description ?: null, $pfpName]);
            
            header('Location: login.php?registered=1');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Registration failed: ' . $e->getMessage();
            if ($pfpName && file_exists('uploads/' . $pfpName)) {
                unlink('uploads/' . $pfpName);
            }
        }
    }
}

require_once 'header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg border border-slate-200 shadow-sm mt-8">
    <h1 class="text-2xl font-bold text-slate-900 mb-6 text-center">Register</h1>

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

    <form action="register.php" method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="username">Username</label>
            <input class="uk-input w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="email">Email</label>
            <input class="uk-input w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="password">Password</label>
            <input class="uk-input w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" type="password" id="password" name="password" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="description">Bio</label>
            <textarea class="uk-textarea w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" id="description" name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Profile Picture</label>
            <div class="w-full" uk-form-custom>
                <input type="file" name="pfp" accept="image/*">
                <button class="uk-button uk-button-default w-full border border-dashed border-slate-300 text-slate-600 hover:bg-slate-50 py-4 rounded-md flex flex-col items-center justify-center gap-1" type="button" tabindex="-1">
                    <span uk-icon="icon: cloud-upload; ratio: 1.2"></span>
                    <span class="text-xs font-medium">Select Image</span>
                </button>
            </div>
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md transition shadow-sm mt-6">
            Register
        </button>
    </form>

    <p class="text-center text-sm text-slate-600 mt-6">
        Already have an account? <a href="login.php" class="text-indigo-600 hover:text-indigo-800 font-semibold transition">Login</a>
    </p>
</div>

<?php
require_once 'footer.php';
?>
