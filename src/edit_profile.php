<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$user = null;
$errors = [];

try {
    $stmt = $pdo->prepare('SELECT * FROM "User" WHERE "Id" = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        $errors[] = 'User not found.';
    }
} catch (PDOException $e) {
    $errors[] = 'Database error: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $description = trim($_POST['description'] ?? '');
    $removePfp = isset($_POST['remove_pfp']);
    $pfpName = $user['Pfp'];

    // Handle Profile Picture Removal/Replacement
    if (empty($errors)) {
        if ($removePfp && $pfpName) {
            $oldPath = 'uploads/' . $pfpName;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
            $pfpName = null;
        }

        if (isset($_FILES['pfp']) && $_FILES['pfp']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['pfp'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Failed to upload profile picture.';
            } else {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = mime_content_type($file['tmp_name']);
                if (!in_array($fileType, $allowedTypes)) {
                    $errors[] = 'Only JPG, PNG, GIF, and WEBP images are allowed.';
                } else {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    if (empty($ext)) {
                        $ext = 'jpg';
                    }
                    $newPfpName = 'pfp_' . uniqid() . '.' . $ext;
                    $targetPath = 'uploads/' . $newPfpName;
                    
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        if ($pfpName) {
                            $oldPath = 'uploads/' . $pfpName;
                            if (file_exists($oldPath)) {
                                unlink($oldPath);
                            }
                        }
                        $pfpName = $newPfpName;
                    } else {
                        $errors[] = 'Failed to save uploaded profile picture.';
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('
                UPDATE "User"
                SET "Description" = ?, "Pfp" = ?
                WHERE "Id" = ?
            ');
            $stmt->execute([$description ?: null, $pfpName, $userId]);

            header('Location: profile.php?id=' . $userId);
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Failed to update profile: ' . $e->getMessage();
        }
    }
}

require_once 'header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg border border-slate-200 shadow-sm mt-8">
    <h1 class="text-2xl font-bold text-slate-900 mb-6 text-center">Edit Profile</h1>

    <?php if (!empty($errors) && !$user): ?>
        <div class="uk-alert-danger mb-6 p-4 rounded-md" uk-alert>
            <ul class="list-disc list-inside text-sm text-red-700">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <a href="index.php" class="text-indigo-600 hover:text-indigo-800 font-semibold">&larr; Back to Home</a>
    <?php else: ?>
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

        <form action="edit_profile.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="username">Username (Non-editable)</label>
                <input class="uk-input w-full px-3 py-2 border border-slate-200 bg-slate-50 text-slate-500 rounded-md cursor-not-allowed" type="text" id="username" value="<?= htmlspecialchars($user['Username']) ?>" disabled>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="email">Email (Non-editable)</label>
                <input class="uk-input w-full px-3 py-2 border border-slate-200 bg-slate-50 text-slate-500 rounded-md cursor-not-allowed" type="email" id="email" value="<?= htmlspecialchars($user['Email']) ?>" disabled>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="description">Bio</label>
                <textarea class="uk-textarea w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" id="description" name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? $user['Description']) ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Profile Picture</label>
                
                <?php if (!empty($user['Pfp'])): ?>
                    <div class="mb-4 flex items-center gap-4 p-4 bg-slate-50 border border-slate-200 rounded-md">
                        <img src="uploads/<?= htmlspecialchars($user['Pfp']) ?>" alt="Current Avatar" class="w-16 h-16 object-cover rounded-full border border-slate-200">
                        <div>
                            <span class="text-xs font-semibold text-slate-500 block">Current Picture</span>
                            <label class="inline-flex items-center gap-2 mt-1 text-sm text-rose-600 cursor-pointer font-medium">
                                <input type="checkbox" name="remove_pfp" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                Remove Picture
                            </label>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="w-full" uk-form-custom>
                    <input type="file" name="pfp" accept="image/*">
                    <button class="uk-button uk-button-default w-full border border-dashed border-slate-300 text-slate-600 hover:bg-slate-50 py-4 rounded-md flex flex-col items-center justify-center gap-1" type="button" tabindex="-1">
                        <span uk-icon="icon: image; ratio: 1.2"></span>
                        <span class="text-xs font-medium">Replace Picture</span>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 mt-6">
                <a href="profile.php?id=<?= $user['Id'] ?>" class="px-4 py-2 border border-slate-300 rounded-md text-slate-700 hover:bg-slate-50 font-medium transition">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-md transition shadow-sm">
                    Save
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php
require_once 'footer.php';
?>
