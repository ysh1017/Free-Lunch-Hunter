<?php
// navbar.php

// 確認會話是否已啟動
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<nav class="navbar">
    <ul class="navbar-menu">
        <li class="navbar-item"><a href="index.php" class="navbar-link">🏠首頁</a></li>
        <li class="navbar-item"><a href="manage_articles.php" class="navbar-link">✒️管理文章</a></li>
        <li class="navbar-item"><a href="post_article.php" class="navbar-link">🗳️發佈文章</a></li>
        <li class="navbar-item"><a href="mailto: graetpurposeteam@gmail.com" class="navbar-link">📮聯絡我們</a></li>
        <li class="navbar-item"><a href="about.php" class="navbar-link">📖關於我們</a></li>
        <li class="navbar-item"><a href="logout.php" class="navbar-link">登出</a></li>
    </ul>
</nav>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .navbar {
        width: 100%;
        background-color: #333;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
    }

    .navbar-menu {
        list-style-type: none;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: flex-end;
    }

    .navbar-item {
        float: left;
    }

    .navbar-link {
        display: block;
        color: white;
        text-align: center;
        padding: 14px 20px;
        text-decoration: none;
    }

    .navbar-link:hover {
        background-color: #ddd;
        color: black;
    }

    .container {
        padding-top: 60px;
        /* 確保內容不會被導航欄遮蓋 */
    }
</style>