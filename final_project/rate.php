<?php
require 'config.php';
require 'money.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['article_id']) || !isset($_POST['rate'])) {
    // 驗證請求是否包含所有必要數據
    exit('Invalid Request');
}

$user_id = $_SESSION['user_id'];
$article_id = $_POST['article_id'];
$rate = $_POST['rate'];

try {
    $conn->beginTransaction();

    // 檢查用戶是否已經對該文章投過票
    $check_sql = "SELECT rate FROM Votes WHERE user_id = :user_id AND article_id = :article_id";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bindParam(':user_id', $user_id);
    $check_stmt->bindParam(':article_id', $article_id);
    $check_stmt->execute();
    $vote = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($vote) {
        if ($vote['rate'] == $rate) {
            // 如果用戶之前的評分與當前評分相同，這意味著用戶想要取消他們的評分
            $sql = "UPDATE Articles SET rate = rate - :rate WHERE article_id = :article_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':rate', $rate);
            $stmt->bindParam(':article_id', $article_id);
            $stmt->execute();

            // 刪除 Votes 表中的記錄
            $delete_sql = "DELETE FROM Votes WHERE user_id = :user_id AND article_id = :article_id";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bindParam(':user_id', $user_id);
            $delete_stmt->bindParam(':article_id', $article_id);
            $delete_stmt->execute();

        } else {
            // 如果用戶想要從讚變倒讚或從倒讚變讚，我們需要更新為新的評分
            $sql = "UPDATE Articles SET rate = rate + :new_rate - :old_rate WHERE article_id = :article_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':new_rate', $rate);
            $stmt->bindParam(':old_rate', $vote['rate']);
            $stmt->bindParam(':article_id', $article_id);
            $stmt->execute();

            // 更新 Votes 表中的記錄
            $update_sql = "UPDATE Votes SET rate = :rate WHERE user_id = :user_id AND article_id = :article_id";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bindParam(':rate', $rate);
            $update_stmt->bindParam(':user_id', $user_id);
            $update_stmt->bindParam(':article_id', $article_id);
            $update_stmt->execute();

        }
    } else {
        // 如果沒有先前的評分，則新增一個評分
        $sql = "INSERT INTO Votes (user_id, article_id, rate) VALUES (:user_id, :article_id, :rate)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->bindParam(':rate', $rate);
        $stmt->execute();

        // 更新文章的總評分
        $update_sql = "UPDATE Articles SET rate = rate + :rate WHERE article_id = :article_id";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':rate', $rate);
        $update_stmt->bindParam(':article_id', $article_id);
        $update_stmt->execute();

    }

     // 根據評分更新用戶的點數
     rewardForAction($user_id, $rate == 1 ? 'like' : 'dislike', $conn);

    $conn->commit();
    echo " success";
} catch (Exception $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage();
}

// 查詢所有文章並按rate排序
$sql = "SELECT * FROM Articles ORDER BY rate DESC, ABS(rate) DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
