<?php
require_once 'db.php';

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;
$error = null;

try {
    if ($postId > 0) {
        $stmt = $pdo->prepare('
            SELECT p.*, u."Username", u."Pfp"
            FROM "Post" p
            LEFT JOIN "User" u ON p."Creator" = u."Id"
            WHERE p."Id" = ?
        ');
        $stmt->execute([$postId]);
        $post = $stmt->fetch();

        if (!$post) {
            $error = 'Post not found.';
        }
    } else {
        $error = 'Invalid post ID.';
    }
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

require_once 'header.php';
?>

<div class="max-w-3xl mx-auto bg-white p-8 rounded-lg border border-slate-200 shadow-sm">
    <?php if ($error || !$post): ?>
        <div class="uk-alert-danger p-4 rounded-md" uk-alert>
            <p><?= htmlspecialchars($error ?? 'Post not found.') ?></p>
        </div>
        <a href="index.php" class="text-indigo-600 hover:text-indigo-800 font-semibold">&larr; Back to Home</a>
    <?php else: ?>
        <div class="space-y-6">
            <!-- Back to home -->
            <a href="index.php" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition">
                <span uk-icon="icon: arrow-left; ratio: 0.8"></span> Back to Home
            </a>

            <!-- Title -->
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                <?= htmlspecialchars($post['Title']) ?>
            </h1>

            <!-- Meta info: Author & Date & Edit/Delete -->
            <div class="flex flex-wrap items-center justify-between gap-4 py-4 border-y border-slate-100">
                <div class="flex items-center gap-3">
                    <a href="profile.php?id=<?= $post['Creator'] ?>" class="flex items-center gap-2 group">
                        <?php if (!empty($post['Pfp'])): ?>
                            <img src="uploads/<?= htmlspecialchars($post['Pfp']) ?>" alt="<?= htmlspecialchars($post['Username']) ?>" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center border border-slate-200 text-slate-500 font-bold">
                                <?= strtoupper(substr($post['Username'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <span class="block text-sm font-bold text-slate-700 group-hover:text-indigo-600 transition">
                                <?= htmlspecialchars($post['Username']) ?>
                            </span>
                            <span class="block text-xs text-slate-500">
                                <?= date('M d, Y', strtotime($post['CreatedAt'])) ?>
                            </span>
                        </div>
                    </a>
                </div>

                <?php if ($isLoggedIn && (int)$_SESSION['user_id'] === (int)$post['Creator']): ?>
                    <div class="flex items-center gap-3">
                        <a href="edit.php?id=<?= $post['Id'] ?>" class="px-4 py-1.5 text-xs font-semibold bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded-md text-slate-700 transition">
                            Edit
                        </a>
                        <a href="delete.php?id=<?= $post['Id'] ?>" class="px-4 py-1.5 text-xs font-semibold bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-md text-rose-600 transition" onclick="return confirm('Are you sure?');">
                            Delete
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Cover Image -->
            <?php if (!empty($post['CoverImage'])): ?>
                <div class="overflow-hidden rounded-lg bg-slate-100 max-h-[450px]">
                    <img src="uploads/<?= htmlspecialchars($post['CoverImage']) ?>" alt="<?= htmlspecialchars($post['Title']) ?>" class="w-full h-full object-cover">
                </div>
            <?php endif; ?>

            <!-- Rendered Markdown Content -->
            <div id="post-content" class="prose prose-slate max-w-none mt-6"></div>
        </div>

        <!-- Marked.js library -->
        <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
        <script>
            // Parse Markdown content safely
            const rawMarkdown = <?= json_encode($post['Content']) ?>;
            document.getElementById('post-content').innerHTML = marked.parse(rawMarkdown);
        </script>
    <?php endif; ?>
</div>

<?php
require_once 'footer.php';
?>
