<?php
// manage_articles.php
require 'config.php';
require 'ArticleClass.php';
require 'navbar.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$article = new ArticleClass($conn);
$articles = $article->getUserArticles($user_id);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete'])) {
        $article_id = $_POST['article_id'];
        $result = $article->deleteArticle($article_id);
        echo $result;
    } elseif (isset($_POST['update'])) {
        $article_id = $_POST['article_id'];
        $title = $_POST['title'];
        $content = $_POST['content'];

        $img = null;
        if (isset($_FILES['img']) && $_FILES['img']['error'] == UPLOAD_ERR_OK) {
            $img = $_FILES['img'];
        }

        $result = $article->updateArticle($article_id, $title, $content, $img);
        echo $result;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理文章</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <header class="fixed-header">
            <h1>管理文章</h1>
        </header>
        <main>
            <?php foreach ($articles as $art): ?>
                <form method="post" action="manage_articles.php" enctype="multipart/form-data">
                    <input type="hidden" name="article_id" value="<?php echo $art['article_id']; ?>">
                    <div class="form-group">
                        <input type="text" name="title" value="<?php echo htmlspecialchars($art['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <textarea name="content" required><?php echo htmlspecialchars($art['content']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="image">更新圖片：</label>
                        <input type="file" name="img" accept="image/*">
                    </div>
                    <div class="form-group button-group">
                    <button type="submit" name="update">更新</button>
                    <button type="submit" name="delete">刪除</button>
                    </div>
                </form>
            <?php endforeach; ?>
        </main>
    </div>
    <footer>
        <p>CopyRight © 2024 Great Purpose Team All Rights Reserved</p>
    </footer>
</body>

</html>