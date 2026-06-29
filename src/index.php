<?php
require_once 'db.php';
require_once 'header.php';

try {
    $stmt = $pdo->query('
        SELECT p."Id", p."Title", p."Content", p."CreatedAt", p."CoverImage", p."Creator",
               u."Username", u."Pfp"
        FROM "Post" p
        LEFT JOIN "User" u ON p."Creator" = u."Id"
        ORDER BY p."CreatedAt" DESC
    ');
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
    $error = $e->getMessage();
}
?>

<div class="space-y-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Posts</h1>
        </div>
        <?php if ($isLoggedIn): ?>
            <a href="create.php" class="bg-indigo-600 text-white hover:bg-indigo-700 font-medium px-4 py-2 rounded-md transition shadow-sm">
                New Post
            </a>
        <?php endif; ?>
    </div>

    <?php if (isset($error)): ?>
        <div class="uk-alert-danger" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p>Database Error: <?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <?php if (empty($posts)): ?>
        <div class="text-center py-16 bg-white rounded-lg border border-slate-200 shadow-sm">
            <span uk-icon="icon: future; ratio: 2.5" class="text-slate-400"></span>
            <p class="text-slate-500 mt-4 text-lg">No posts yet.</p>
            <?php if ($isLoggedIn): ?>
                <a href="create.php" class="mt-4 inline-block bg-indigo-600 text-white hover:bg-indigo-700 font-medium px-4 py-2 rounded-md transition shadow-sm">
                    Write Post
                </a>
            <?php else: ?>
                <a href="register.php" class="mt-4 inline-block bg-indigo-600 text-white hover:bg-indigo-700 font-medium px-4 py-2 rounded-md transition shadow-sm">
                    Register
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="grid gap-6 md:grid-cols-1">
            <?php foreach ($posts as $post): ?>
                <article class="uk-card uk-card-default uk-card-body shadow-sm rounded-lg border border-slate-200 bg-white p-6 transition hover:shadow-md">
                    <?php if (!empty($post['CoverImage'])): ?>
                        <div class="mb-4 overflow-hidden rounded-md max-h-80 bg-slate-100">
                            <img src="uploads/<?= htmlspecialchars($post['CoverImage']) ?>" alt="<?= htmlspecialchars($post['Title']) ?>" class="w-full h-full object-cover">
                        </div>
                    <?php endif; ?>

                    <div class="flex items-center gap-3 mb-3">
                        <a href="profile.php?id=<?= $post['Creator'] ?>" class="flex items-center gap-2 group">
                            <?php if (!empty($post['Pfp'])): ?>
                                <img src="uploads/<?= htmlspecialchars($post['Pfp']) ?>" alt="<?= htmlspecialchars($post['Username']) ?>" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                            <?php else: ?>
                                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center border border-slate-200 text-slate-500 font-bold text-sm">
                                    <?= strtoupper(substr($post['Username'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <span class="text-sm font-semibold text-slate-700 group-hover:text-indigo-600 transition">
                                <?= htmlspecialchars($post['Username']) ?>
                            </span>
                        </a>
                        <span class="text-slate-300">•</span>
                        <time class="text-xs text-slate-500" datetime="<?= $post['CreatedAt'] ?>">
                            <?= date('M d, Y', strtotime($post['CreatedAt'])) ?>
                        </time>
                    </div>

                    <h2 class="text-2xl font-bold text-slate-900 leading-tight mb-2 hover:text-indigo-600 transition">
                        <a href="post.php?id=<?= $post['Id'] ?>"><?= htmlspecialchars($post['Title']) ?></a>
                    </h2>

                    <p class="text-slate-600 line-clamp-3 mb-4">
                        <?= htmlspecialchars(mb_strimwidth(strip_tags($post['Content']), 0, 200, '...')) ?>
                    </p>

                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-100">
                        <a href="post.php?id=<?= $post['Id'] ?>" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm flex items-center gap-1 transition">
                            Read More <span uk-icon="icon: arrow-right; ratio: 0.8"></span>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'footer.php';
?>
