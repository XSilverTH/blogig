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

if ($postId > 0) {
    try {
        $stmt = $pdo->prepare('SELECT "Creator", "CoverImage" FROM "Post" WHERE "Id" = ?');
        $stmt->execute([$postId]);
        $post = $stmt->fetch();

        if ($post && (int)$post['Creator'] === (int)$_SESSION['user_id']) {
            if (!empty($post['CoverImage'])) {
                $filePath = 'uploads/' . $post['CoverImage'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $stmt = $pdo->prepare('DELETE FROM "Post" WHERE "Id" = ?');
            $stmt->execute([$postId]);
        }
    } catch (PDOException $e) {
        // Database exception, will redirect to index.php
    }
}

header('Location: index.php');
exit;
