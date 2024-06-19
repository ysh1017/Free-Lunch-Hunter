<?php
// post_article.php
require 'config.php';
require 'money.php';
require 'ArticleClass.php';
require 'navbar.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    // 處理圖片上傳
    $img = null;
    if (isset($_FILES['img']) && $_FILES['img']['error'] == UPLOAD_ERR_OK) {
        $img = $_FILES['img'];
    }

    $article = new ArticleClass($conn);
    $result = $article->insertArticle($user_id, $title, $content, $img);

    if ($result) {
        // 發布貼文成功，獎勵用戶
        rewardForAction($user_id, 'post', $conn);
        echo "Post published successfully.";
    } else {
        echo "Failed to publish post.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>發佈文章</title>
    <link rel="stylesheet" href="styles_post.css">
</head>

<body>
    <div class="wrapper">
        <div class="container">
            <header class="fixed-header">
                <h1>發佈文章</h1>
            </header>
            <main>
                <form method="post" action="post_article.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="text" name="title" required placeholder="標題......" oninput="validateForm()">
                    </div>
                    <div class="form-group">
                        <textarea name="content" required placeholder="想說什麼?" oninput="validateForm()"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="image">插入圖片：</label>
                        <input type="file" name="img" accept="image/*">
                    </div>
                    <button name="publishBtn" class="disabled" onclick="publishArticle()" disabled>發佈</button>
                </form>
            </main>
            <div class="preview">
                <h2>文章預覽</h2>
                <div class="previewTitle"></div>
                <div class="previewContent"></div>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>

</html>