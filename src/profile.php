<?php
require_once 'db.php';

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$profileUser = null;
$posts = [];
$error = null;

try {
    if ($userId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM "User" WHERE "Id" = ?');
        $stmt->execute([$userId]);
        $profileUser = $stmt->fetch();

        if (!$profileUser) {
            $error = 'User not found.';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM "Post" WHERE "Creator" = ? ORDER BY "CreatedAt" DESC');
            $stmt->execute([$userId]);
            $posts = $stmt->fetchAll();
        }
    } else {
        $error = 'Invalid user ID.';
    }
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

require_once 'header.php';
?>

<div class="space-y-8">
    <?php if ($error || !$profileUser): ?>
        <div class="uk-alert-danger p-4 rounded-md" uk-alert>
            <p><?= htmlspecialchars($error ?? 'User not found.') ?></p>
        </div>
        <a href="index.php" class="text-indigo-600 hover:text-indigo-800 font-semibold">&larr; Back to Home</a>
    <?php else: ?>
        <!-- Profile info section -->
        <div class="bg-white border border-slate-200 shadow-sm rounded-lg p-8 flex flex-col md:flex-row items-center gap-6">
            <div class="flex-shrink-0">
                <?php if (!empty($profileUser['Pfp'])): ?>
                    <img src="uploads/<?= htmlspecialchars($profileUser['Pfp']) ?>" alt="<?= htmlspecialchars($profileUser['Username']) ?>" class="w-24 h-24 rounded-full object-cover border-2 border-slate-200">
                <?php else: ?>
                    <div class="w-24 h-24 rounded-full bg-slate-200 flex items-center justify-center border-2 border-slate-200 text-slate-500 font-bold text-3xl">
                        <?= strtoupper(substr($profileUser['Username'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex-grow text-center md:text-left space-y-2">
                <div class="flex flex-col md:flex-row md:items-center gap-3 justify-center md:justify-start">
                    <h1 class="text-3xl font-extrabold text-slate-900"><?= htmlspecialchars($profileUser['Username']) ?></h1>
                    <?php if ($isLoggedIn && $userId === (int)$_SESSION['user_id']): ?>
                        <a href="edit_profile.php" class="inline-block px-3 py-1 text-xs font-semibold bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded text-slate-700 transition">
                            Edit Profile
                        </a>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-slate-500 font-medium"><?= htmlspecialchars($profileUser['Email']) ?></p>
                <p class="text-slate-600 italic">
                    <?= !empty($profileUser['Description']) ? nl2br(htmlspecialchars($profileUser['Description'])) : 'No bio.' ?>
                </p>
            </div>
        </div>

        <!-- User posts listing -->
        <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-6">Posts</h2>
            
            <?php if (empty($posts)): ?>
                <div class="text-center py-12 bg-white rounded-lg border border-slate-200 shadow-sm text-slate-500">
                    <p>No posts.</p>
                </div>
            <?php else: ?>
                <div class="grid gap-6">
                    <?php foreach ($posts as $post): ?>
                        <article class="bg-white border border-slate-200 shadow-sm rounded-lg p-6 flex flex-col md:flex-row gap-6 hover:shadow-md transition">
                            <?php if (!empty($post['CoverImage'])): ?>
                                <div class="w-full md:w-48 h-32 flex-shrink-0 overflow-hidden rounded-md bg-slate-100">
                                    <img src="uploads/<?= htmlspecialchars($post['CoverImage']) ?>" alt="<?= htmlspecialchars($post['Title']) ?>" class="w-full h-full object-cover">
                                </div>
                            <?php endif; ?>
                            <div class="flex-grow flex flex-col justify-between">
                                <div>
                                    <span class="text-xs text-slate-500"><?= date('M d, Y', strtotime($post['CreatedAt'])) ?></span>
                                    <h3 class="text-xl font-bold text-slate-900 mt-1 hover:text-indigo-600 transition">
                                        <a href="post.php?id=<?= $post['Id'] ?>"><?= htmlspecialchars($post['Title']) ?></a>
                                    </h3>
                                    <p class="text-slate-600 text-sm line-clamp-2 mt-2">
                                        <?= htmlspecialchars(mb_strimwidth(strip_tags($post['Content']), 0, 150, '...')) ?>
                                    </p>
                                </div>
                                <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-100">
                                    <a href="post.php?id=<?= $post['Id'] ?>" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold flex items-center gap-1 transition">
                                        Read <span uk-icon="icon: arrow-right; ratio: 0.8"></span>
                                    </a>
                                    <?php if ($isLoggedIn && $userId === (int)$_SESSION['user_id']): ?>
                                        <div class="flex items-center gap-3">
                                            <a href="edit.php?id=<?= $post['Id'] ?>" class="text-xs text-slate-600 hover:text-slate-950 font-medium px-2 py-1 bg-slate-100 border border-slate-200 rounded transition">Edit</a>
                                            <a href="delete.php?id=<?= $post['Id'] ?>" class="text-xs text-rose-600 hover:text-rose-800 font-medium px-2 py-1 bg-rose-50 border border-rose-100 rounded transition" onclick="return confirm('Are you sure?');">Delete</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'footer.php';
?>
