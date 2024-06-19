<?php
require 'config.php';
require 'premium_check.php';
require 'money.php';

// 確認會話是否已啟動
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 確認用戶已登入
$user_id = $_SESSION['user_id'];

// 用戶是否為 premium
if (isPremium($user_id, $conn) == 0) {
    header('Location: index.php');
    exit;
} elseif (isPremium($user_id, $conn) == 2) {
    header('Location: admin_dashboard.php');
    exit;
}

// 獲取用戶資訊
$sql = "SELECT username FROM user_account WHERE user_id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 獲取用戶餘額
$userBalance = getUserBalance($user_id, $conn);

// 假設這裡定義了一個 `comm` 變數
$comm = 10; // 這裡是根據您的需求設置一個合適的值
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
    <title>免費午餐分享平台</title>
    <link rel="stylesheet" href="styleopen.css">
    <link rel='stylesheet' href='https://fullcalendar.io/releases/fullcalendar/3.9.0/fullcalendar.min.css' />
    <script src='https://fullcalendar.io/releases/fullcalendar/3.9.0/lib/jquery.min.js'></script>
    <script src='https://fullcalendar.io/releases/fullcalendar/3.9.0/lib/moment.min.js'></script>
    <script src='https://fullcalendar.io/releases/fullcalendar/3.9.0/fullcalendar.min.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js" async=""></script>



    <style>
        .scroll-button {
            position: fixed;
            right: 20px;
            bottom: 20px;
            width: 50px;
            height: 50px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0.8;
        }
        .scroll-button:hover {
            opacity: 1;
        }
        .scroll-top {
            bottom: 80px; /* 上移一點避免與另一按鈕重疊 */
        }
    </style>
</head>

<body>
    <?php require 'navbar.php'; ?>

    <div class="container">
    

        <header>
            <h1>歡迎，乞丐超人✪ ω ✪: <?php echo htmlspecialchars($user['username']); ?>！</h1>
        </header>
        <h2>免費午餐行事曆</h2>
        <div id='calendar'></div>
        <h2 style="color:#0d47a1;">最新活動</h2>
        <form method="get" action="">
            <label for="sort">排序方式：</label>
            <select name="sort" id="sort" onchange="this.form.submit()">
                <option value="date(current_to_old)" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'date(current_to_old)')
                    echo 'selected'; ?>>日期（由新到舊）</option>
                <option value="date(old_to_current)" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'date(old_to_current)')
                    echo 'selected'; ?>>日期（由舊到新）</option>
            </select>
        </form>

        <?php
        // 獲取用戶選擇的排序方式
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'rate';

        // 根據選擇的排序方式生成 SQL 查詢
        if ($sort == 'date(current_to_old)') {
            $order_by = 'act_date DESC';
        } else {
            $order_by = 'act_date ASC';
        }
        // 初始化 $activities 為空數組，避免未定義變量錯誤
        $activities = [];

        // 獲取最新的活動，並排除過期的活動
        $current_time = date('Y-m-d'); // 目前時間
        $sql = "SELECT activity_id, title, content, act_date, img, place, STARTT, ENDT, URL, LUNCH 
                FROM activities 
                WHERE act_date > :current_time
                ORDER BY $order_by";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':current_time', $current_time);
        $stmt->execute();
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($activities) {
            // 取得用戶已加入的活動
            $sql_joined = "SELECT activity_id FROM join_activity WHERE user_id = :user_id";
            $stmt_joined = $conn->prepare($sql_joined);
            $stmt_joined->bindParam(':user_id', $_SESSION['user_id']);
            $stmt_joined->execute();
            $joined_activities = $stmt_joined->fetchAll(PDO::FETCH_COLUMN);

            if ($joined_activities === false) {
                $joined_activities = [];
            }

            foreach ($activities as $activity) {
                echo '<div class="activity">';
                echo "<h3 class='activity-title'>" . htmlspecialchars($activity['title']) . "</h3>";
                echo "<p class='post-content'>" . htmlspecialchars($activity['content']) . "</p>";

                // 如果有圖片，顯示圖片
                if (!empty($activity['img'])) {
                    $img_url = htmlspecialchars($activity['img']);
                    echo "<img src='" . $img_url . "' alt='Activity Image' style='max-width:50%; height:auto;'>";
                }

                echo "<p>活動日期：" . htmlspecialchars($activity['act_date']) . "</p>";
                echo "<p>地點：" . htmlspecialchars($activity['place']) . "</p>";
                echo "<p>開始時間：" . htmlspecialchars($activity['STARTT']) . "</p>";
                echo "<p>結束時間：" . htmlspecialchars($activity['ENDT']) . "</p>";
                echo "<p>報名網址：<a href='" . htmlspecialchars($activity['URL']) . "'>" . htmlspecialchars($activity['URL']) . "</a></p>";
                echo "<p>午餐內容：" . htmlspecialchars($activity['LUNCH']) . "</p>";

                // 檢查用戶是否已加入活動
                if (in_array($activity['activity_id'], $joined_activities)) {
                    echo '<button class="add-to-calendar-btn" data-activity-id="' . htmlspecialchars($activity['activity_id']) . '">取消加入此活動</button>';
                } else {
                    echo '<button class="add-to-calendar-btn" data-activity-id="' . htmlspecialchars($activity['activity_id']) . '">加入行事曆</button>';
                }

                echo '</div>';
            }
        } else {
            echo "<p>目前沒有最新活動。</p>";
        }
        ?>


        <h2>最新貼文</h2>
        <form method="get" action="">
            <label for="sort">排序方式：</label>
            <select name="sort" id="sort" onchange="this.form.submit()">
                <option value="rate" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'rate')
                    echo 'selected'; ?>>
                    評分（由高至低）</option>
                <option value="date(current_to_old)" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'date(current_to_old)')
                    echo 'selected'; ?>>日期（由新到舊）</option>
                <option value="date(old_to_current)" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'date(old_to_current)')
                    echo 'selected'; ?>>日期（由舊到新）</option>
            </select>
        </form>

        <div id="articles">
            <?php
            // 獲取用戶選擇的排序方式
            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'rate';

            // 根據選擇的排序方式生成 SQL 查詢
            if ($sort == 'date(current_to_old)') {
                $order_by = 'art_date DESC';
            } elseif ($sort == 'date(old_to_current)') {
                $order_by = 'art_date ASC';
            } else {
                $order_by = 'rate DESC';
            }
               // 獲取最新的貼文及用戶對這些文章的評分狀態
                $sql = "SELECT A.article_id, A.title, A.content, A.art_date, A.rate, U.username, A.img, A.user_id, V.rate AS user_rate
                    FROM articles A
                    JOIN user_account U ON A.user_id = U.user_id
                    LEFT JOIN Votes V ON A.article_id = V.article_id AND V.user_id = :user_id
                    ORDER BY $order_by";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($articles) {
                    foreach ($articles as $article) {
                        echo "<div class='post'>";
                        echo "<h3 class='post-title'>" . htmlspecialchars($article['title']) . "</h3>";
                        echo "<p class='post-content'>" . htmlspecialchars($article['content']) . "</p>";
                        // 如果有圖片，顯示圖片
                        if (!empty($article['img'])) {
                            $img_path = htmlspecialchars($article['img']);
                            $full_img_url = 'http://localhost' . $img_path;
                            echo "<img src='" . $full_img_url . "' alt='Article Image' style='max-width:30%; height:auto; object-fit: contain;'>";
                        }
                        echo "<p class='post-meta'>由" . htmlspecialchars($article['username']) . " 發佈於" . htmlspecialchars($article['art_date']) . "</p>";
                        // 顯示評分
                        echo "<p>評分： " . htmlspecialchars($article['rate']) . "</p>";

                        // 根據用戶對文章的評分狀態調整按鈕
                        $like_btn_text = $article['user_rate'] == 1 ? "收回讚" : "按讚";
                        $dislike_btn_text = $article['user_rate'] == -1 ? "收回倒讚" : "倒讚";
                        $like_rate = $article['user_rate'] == 1 ? 0 : 1;
                        $dislike_rate = $article['user_rate'] == -1 ? 0 : -1;

                        // 決定按鈕的類別
                        $like_btn_class = $article['user_rate'] == 1 ? "rate-btn like-btn active" : "rate-btn like-btn";
                        $dislike_btn_class = $article['user_rate'] == -1 ? "rate-btn dislike-btn active" : "rate-btn dislike-btn";

                        // 輸出按讚按鈕
                        echo '<button class="' . $like_btn_class . '" data-article-id="' . htmlspecialchars($article['article_id']) . '" data-rate="' . $like_rate . '">' . $like_btn_text . '</button>';

                        // 輸出倒讚按鈕
                        echo '<button class="' . $dislike_btn_class . '" data-article-id="' . htmlspecialchars($article['article_id']) . '" data-rate="' . $dislike_rate . '">' . $dislike_btn_text . '</button>';

                        // 顯示編輯按鈕
                        if ($article['user_id'] == $user_id) {
                            echo '<a href="manage_articles.php?article_id=' . htmlspecialchars($article['article_id']) . '" class="edit-btn">編輯文章</a>';
                        }

                        // 顯示已有的留言
                        $sql = "SELECT C.content, C.comm_date, U.username
                            FROM comments C
                            JOIN user_account U ON C.user_id = U.user_id
                            WHERE C.article_id = :article_id
                            ORDER BY C.comm_date DESC";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':article_id', $article['article_id']);
                        $stmt->execute();
                        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        echo '<div class="comments">';
                        foreach ($comments as $comment) {
                            echo '<p>' . htmlspecialchars($comment['username']) . ': ' . htmlspecialchars($comment['content']) . ' (' . htmlspecialchars($comment['comm_date']) . ')</p>';
                        }
                        echo '</div>';

                        // 顯示留言按鈕
                        //  echo '<button class="comment-btn" data-article-id="' . htmlspecialchars($article['article_id']) . '">留言</button>';
                        // 顯示留言輸入框
                        echo '<form class="comment-form" method="post" action="comments.php">';
                        echo '<input type="hidden" name="article_id" value="' . htmlspecialchars($article['article_id']) . '">';
                        echo '<textarea name="content" placeholder="留言……" required></textarea><br>';
                        echo '<input type="submit" value="送出" data-article-id="' . htmlspecialchars($article['article_id']) . '">';
                        echo '</form>';
                        echo '</div>';
                    }
                } else {
                    echo "<p>目前沒有最新貼文。</p>";
                }
                ?>
            </div>
        <a href="recharge.php">儲值點數</a>
        <p>目前點數餘額：<?php echo $userBalance; ?> 點</p>
    </div>

    <button class="scroll-button scroll-top" id="scrollTopBtn">↑</button>
    <button class="scroll-button scroll-bottom" id="scrollBottomBtn">↓</button>

    <script>
        // 滾動到頁面頂部
        document.getElementById('scrollTopBtn').addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // 滾動到頁面底部
        document.getElementById('scrollBottomBtn').addEventListener('click', function () {
            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
        });
    </script>


    <!-- 留言框 -->
    <!-- <div id="comment-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <form id="comment-form" method="post" action="comments.php">
                <input type="hidden" name="article_id" id="modal-article-id">
                Content: <textarea name="content" required></textarea><br>
                <input type="submit" value="Comment">
            </form>
        </div>
    </div> -->

    <script>
    $(document).ready(function () {
        $('#calendar').fullCalendar({
            events: 'load_activities.php',
            editable: false, 
            eventLimit: true,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            selectable: true,
            eventClick: function (event) {
                if (confirm('Are you sure you want to delete this event?')) {
                    $.ajax({
                        url: 'manage_calendar.php',
                        type: 'POST',
                        data: {
                            action: 'delete',
                            activity_id: event.id
                        },
                        success: function (response) {
                            $('#calendar').fullCalendar('removeEvents', event._id);
                            alert(response);
                            location.reload();
                        }
                    });
                }
            }
        });

    // 活動加到行事曆
    $(document).on('click', '.add-to-calendar-btn', function () {
        var button = $(this);
        var activityId = button.data('activity-id');
        var action = button.text() === '加入行事曆' ? 'add' : 'delete';

        $.ajax({
            url: 'manage_calendar.php',
            type: 'POST',
            data: {
                action: action,
                activity_id: activityId
            },
            success: function (response) {
                alert(response);
                $('#calendar').fullCalendar('refetchEvents'); // 重新載入行事曆

                // 更新button的字
                if (action === 'add') {
                    button.text('取消加入此活動');
                } else {
                    button.text('加入行事曆');
                }
                
            }
        });
    });

        // //顯示留言框
        // $('.comment-btn').on('click', function () {
        //         var articleId = $(this).data('article-id');
        //         $('#modal-article-id').val(articleId);
        //         $('#comment-modal').show();
        //     });
    
        //     // 關閉留言框
        //     $('.close').on('click', function () {
        //         $('#comment-modal').hide();
        //     });
    
        //     // 當點擊留言框外部時關閉
        //     $(window).on('click', function (event) {
        //         if (event.target.id === 'comment-modal') {
        //             $('#comment-modal').hide();
        //         }
        //     });

    // Handle like and dislike
    $('.rate-btn').on('click', function () {
        var articleId = $(this).data('article-id');
        var rate = $(this).data('rate');
        
        $.post('rate.php', { article_id: articleId, rate: rate }, function (response) {
            location.reload();  // Reload the page to reflect changes
        });
    });
});

        
    </script>
    <footer>
        <p>CopyRight © 2024 Great Purpose Team All Rights Reserved</p>
    </footer>
</body>

</html>