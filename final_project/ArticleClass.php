<?php
//ArticleClass.php
require_once 'config.php';

class ArticleClass
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function insertArticle($user_id, $title, $content, $img)
    {
        try {
            $img_path = null;

            // Handle image upload
            if (!empty($img)) {
                // Check if image is uploaded via form
                if (is_array($img) && isset($img['tmp_name']) && is_uploaded_file($img['tmp_name'])) {
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                    $imgExtension = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));

                    if (in_array($imgExtension, $allowedExtensions)) {
                        $timestamp = time();
                        $img_path = "/final_project/src/img/" . $timestamp . "." . $imgExtension;
                        $output_file = $_SERVER["DOCUMENT_ROOT"] . $img_path;

                        if (!move_uploaded_file($img['tmp_name'], $output_file)) {
                            return "Failed to move uploaded file.";
                        }
                    } else {
                        return "Invalid image file extension.";
                    }
                } else {
                    return "Invalid image file.";
                }
            }

            $sql = "INSERT INTO Articles (user_id, title, content, img, art_date, rate)
                VALUES (:user_id, :title, :content, :img, NOW(), 0)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt->bindParam(':img', $img_path, PDO::PARAM_STR);
            $stmt->execute();

            return "文章發佈成功！";
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }
    public function getUserArticles($user_id)
    {
        try {
            $sql = "SELECT article_id, title, content, img FROM Articles WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updateArticle($article_id, $title, $content, $img)
    {
        try {
            $img_path = null;

            if (!empty($img)) {
                if (is_array($img) && isset($img['tmp_name']) && is_uploaded_file($img['tmp_name'])) {
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                    $imgExtension = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));

                    if (in_array($imgExtension, $allowedExtensions)) {
                        $timestamp = time();
                        $img_path = "/final_project/src/img/" . $timestamp . "." . $imgExtension;
                        $output_file = $_SERVER["DOCUMENT_ROOT"] . $img_path;

                        if (!move_uploaded_file($img['tmp_name'], $output_file)) {
                            return "Failed to move uploaded file.";
                        }
                    } else {
                        return "Invalid image file extension.";
                    }
                } else {
                    return "Invalid image file.";
                }
            }

            $sql = "UPDATE Articles SET title = :title, content = :content, img = COALESCE(:img, img) WHERE article_id = :article_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt->bindParam(':img', $img_path, PDO::PARAM_STR);
            $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
            $stmt->execute();

            return "文章更新成功！";
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }


    public function deleteArticle($article_id)
    {
        try {
            $sql = "DELETE FROM Articles WHERE article_id = :article_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
            $stmt->execute();

            return "文章刪除成功！";
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }

}
