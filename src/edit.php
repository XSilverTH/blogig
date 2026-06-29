<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;
$errors = [];

try {
    if ($postId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM "Post" WHERE "Id" = ?');
        $stmt->execute([$postId]);
        $post = $stmt->fetch();

        if (!$post) {
            $errors[] = 'Post not found.';
        } elseif ((int)$post['Creator'] !== (int)$_SESSION['user_id']) {
            header('Location: index.php');
            exit;
        }
    } else {
        $errors[] = 'Invalid post ID.';
    }
} catch (PDOException $e) {
    $errors[] = 'Database error: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $post) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $removeCover = isset($_POST['remove_cover']);
    $coverName = $post['CoverImage'];

    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    if (empty($content)) {
        $errors[] = 'Content is required.';
    }

    // Handle Cover Image Upload/Replacement
    if (empty($errors)) {
        if ($removeCover && $coverName) {
            $oldPath = 'uploads/' . $coverName;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
            $coverName = null;
        }

        if (isset($_FILES['cover']) && $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {
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
                    $newCoverName = 'cover_' . uniqid() . '.' . $ext;
                    $targetPath = 'uploads/' . $newCoverName;
                    
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        if ($coverName) {
                            $oldPath = 'uploads/' . $coverName;
                            if (file_exists($oldPath)) {
                                unlink($oldPath);
                            }
                        }
                        $coverName = $newCoverName;
                    } else {
                        $errors[] = 'Failed to save uploaded cover image.';
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('
                UPDATE "Post"
                SET "Title" = ?, "Content" = ?, "CoverImage" = ?
                WHERE "Id" = ?
            ');
            $stmt->execute([$title, $content, $coverName, $postId]);

            header('Location: post.php?id=' . $postId);
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Failed to update post: ' . $e->getMessage();
        }
    }
}

require_once 'header.php';
?>

<div class="max-w-3xl mx-auto bg-white p-8 rounded-lg border border-slate-200 shadow-sm">
    <h1 class="text-3xl font-extrabold text-slate-900 mb-6">Edit Post</h1>

    <?php if (!empty($errors) && !$post): ?>
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

        <form action="edit.php?id=<?= $post['Id'] ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="title">Title</label>
                <input class="uk-input w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-lg font-semibold" type="text" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? $post['Title']) ?>" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Cover Image</label>
                
                <?php if (!empty($post['CoverImage'])): ?>
                    <div class="mb-4 flex items-center gap-4 p-4 bg-slate-50 border border-slate-200 rounded-md">
                        <img src="uploads/<?= htmlspecialchars($post['CoverImage']) ?>" alt="Current cover" class="w-20 h-20 object-cover rounded-md border border-slate-200">
                        <div>
                            <span class="text-xs font-semibold text-slate-500 block">Current Image</span>
                            <label class="inline-flex items-center gap-2 mt-1 text-sm text-rose-600 cursor-pointer font-medium">
                                <input type="checkbox" name="remove_cover" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                Remove Image
                            </label>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="w-full" uk-form-custom>
                    <input type="file" name="cover" accept="image/*">
                    <button class="uk-button uk-button-default w-full border border-dashed border-slate-300 text-slate-600 hover:bg-slate-50 py-6 rounded-md flex flex-col items-center justify-center gap-1" type="button" tabindex="-1">
                        <span uk-icon="icon: image; ratio: 1.4"></span>
                        <span class="text-xs font-semibold">Replace Image</span>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1" for="content">Content</label>
                <textarea class="uk-textarea w-full px-4 py-3 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-sm" id="content" name="content" rows="15" required><?= htmlspecialchars($_POST['content'] ?? $post['Content']) ?></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="post.php?id=<?= $post['Id'] ?>" class="px-4 py-2 border border-slate-300 rounded-md text-slate-700 hover:bg-slate-50 font-medium transition">
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
