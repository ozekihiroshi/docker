<?php
// update_status.php: リクエストのステータスを更新
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    if ($id) {
        $stmt = $pdo->prepare('UPDATE password_reset_requests SET status = ? WHERE id = ?');
        $stmt->execute(['completed', $id]);
        echo "リクエストID $id を対応済みに更新しました。<br><a href='admin_dashboard.php'>ダッシュボードに戻る</a>";
    } else {
        echo "無効なリクエストです。<br><a href='admin_dashboard.php'>ダッシュボードに戻る</a>";
    }
}
?>
