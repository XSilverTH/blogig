<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $coverName = null;

    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    if (empty($content)) {
        $errors[] = 'Content is required.';
    }

    // Handle Cover Image Upload
    if (empty($errors) && isset($_FILES['cover']) && $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['cover'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Failed to upload cover image.';
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($file['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = 'Only JPG, PNG, GIF, and WEBP images are allowed for cover image.';
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                if (empty($ext)) {
                    $ext = 'jpg';
                }
                $coverName = 'cover_' . uniqid() . '.' . $ext;
                $targetPath = 'uploads/' . $coverName;
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $errors[] = 'Failed to save uploaded cover image.';
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('
                INSERT INTO "Post" ("Title", "Content", "Creator", "CoverImage", "CreatedAt")
                VALUES (?, ?, ?, ?, NOW())
                RETURNING "Id"
            ');
            $stmt->execute([$title, $content, $_SESSION['user_id'], $coverName]);
            $newPostId = $stmt->fetchColumn();

            header('Location: post.php?id=' . $newPostId);
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Failed to create post: ' . $e->getMessage();
            if ($coverName && file_exists('uploads/' . $coverName)) {
                unlink('uploads/' . $coverName);
            }
        }
    }
}

require_once 'header.php';
?>

<div class="max-w-3xl mx-auto bg-white p-8 rounded-lg border border-slate-200 shadow-sm">
    <h1 class="text-3xl font-extrabold text-slate-900 mb-6">New Post</h1>

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

    <form action="create.php" method="POST" enctype="multipart/form-data" class="space-y-6">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="title">Title</label>
            <input class="uk-input w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-lg font-semibold" type="text" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" placeholder="Title" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Cover Image</label>
            <div class="w-full" uk-form-custom>
                <input type="file" name="cover" accept="image/*">
                <button class="uk-button uk-button-default w-full border border-dashed border-slate-300 text-slate-600 hover:bg-slate-50 py-6 rounded-md flex flex-col items-center justify-center gap-1" type="button" tabindex="-1">
                    <span uk-icon="icon: image; ratio: 1.4"></span>
                    <span class="text-xs font-semibold">Upload Image</span>
                </button>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1" for="content">Content</label>
            <textarea class="uk-textarea w-full px-4 py-3 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-sm" id="content" name="content" rows="15" placeholder="Content..." required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="index.php" class="px-4 py-2 border border-slate-300 rounded-md text-slate-700 hover:bg-slate-50 font-medium transition">
                Cancel
            </a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-md transition shadow-sm">
                Publish
            </button>
        </div>
    </form>
</div>

<?php
require_once 'footer.php';
?>
