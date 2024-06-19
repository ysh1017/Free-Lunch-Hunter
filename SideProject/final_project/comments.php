<?php
//comments.php
require 'config.php';
require 'money.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $article_id = $_POST['article_id'];
    $content = $_POST['content'];
    $scroll_position = $_POST['scroll_position']; // Get the scroll position

    $sql = "INSERT INTO Comments (article_id, user_id, content, comm_date) VALUES (:article_id, :user_id, :content, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':article_id', $article_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':content', $content);
    $stmt->execute();

    // 獎勵用戶留言
    rewardForAction($user_id, 'comment', $conn);

    // Redirect back with the scroll position
    
    header('Location: index.php#' . $article_id . '&scroll_position=' . $scroll_position);
    exit;
}

