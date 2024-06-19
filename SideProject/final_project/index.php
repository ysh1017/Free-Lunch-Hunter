<?php
require 'config.php';
require 'money.php';
require 'premium_check.php';

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

if (isPremium($user_id, $conn) == 1) {
    header('Location: premiumindex.php');
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
</head>

<body>
    <?php require 'navbar.php'; ?>

    <div class="container">
        <header>
            <h1>歡迎，<?php echo htmlspecialchars($user['username']); ?>！</h1>
        </header>

        <h2>免費午餐活動行事曆</h2>
        <div id='calendar'></div>

        <h2 style="color:#0d47a1;">最新活動</h2>
            <div id="activities">
                <?php
                // 初始化 $activities 為空數組，避免未定義變量錯誤
                $activities = [];

                // 獲取最新的活動，並排除過期的活動
                $current_time = date('Y-m-d'); // 目前時間
                $sql = "SELECT activity_id, title, content, act_date, img, place, STARTT, ENDT, URL, LUNCH 
            FROM activities 
            WHERE act_date > :current_time
            LIMIT 3";
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
                            $img_path = htmlspecialchars($activity['img']);
                            $full_img_url = 'http://localhost' . $img_path;
                            echo "<img src='" . $full_img_url . "' alt='Activity Image' style='max-width:100%; height:auto;'>";
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
                echo '<div class="unlock-premium">';
                echo '<a href="upgrade.php">解鎖 Premium 會員以觀看更多免費午餐活動</a>';
                echo '</div>';
                ?>
            </div>

            <h2>最新貼文</h2>
            <div id="articles">
                <?php
                // 初始化 $articles 為空數組，避免未定義變量錯誤
                $articles = [];

                // 獲取最新的貼文及用戶對這些文章的評分狀態
                $sql = "SELECT A.article_id, A.title, A.content, A.art_date, A.rate, U.username, A.img, A.user_id, V.rate AS user_rate
                    FROM articles A
                    JOIN user_account U ON A.user_id = U.user_id
                    LEFT JOIN Votes V ON A.article_id = V.article_id AND V.user_id = :user_id
                    ORDER BY A.art_date DESC
                    LIMIT 5";
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

                        // 決定按鈕的類別，是否包括活躍狀態
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
                        echo '<input type="submit" value="Comment" data-article-id="' . htmlspecialchars($article['article_id']) . '">';
                        echo '</form>';

                        echo '</div>';
                    }
                } else {
                    echo "<p>目前沒有最新貼文。</p>";
                }
                // 提示升級premium以解鎖更多文章
                echo '<div class="unlock-premium">';
                echo '<a href="upgrade.php>解鎖 Premium 會員以觀看更多文章</p>';
                echo '</div>';
                ?>
            </div>

            <br>
            <a href="upgrade.php">升級到 Premium 會員</a>
            <a href="recharge.php">儲值點數</a>
            <p>目前點數餘額：<?php echo $userBalance; ?> 點</p>
    </div>

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
    dateClick: function(info) {
        console.log(info)
        Swal.fire({
            icon: "success",
            title: "Clicked",
            text: info.dateStr,
            confirmButtonText: "OK",
        })
    },
    select: function(info) {
        console.log(info)
        Swal.fire({
            icon: "success",
            title: "Selected",
            text: `${info.startStr} ~ ${info.endStr}`,
            confirmButtonText: "OK",
        })
    },
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

        // 處理按讚和倒讚
        $('.rate-btn').on('click', function () {
            var articleId = $(this).data('article-id');
            var rate = $(this).data('rate');
            $.post('rate.php', { article_id: articleId, rate: rate }, function (response) {
                location.reload();
            });
        });
    </script>
    <script src="scriptopen.js"></script>

    <footer>
        <p>CopyRight © 2024 Great Purpose Team All Rights Reserved</p>
    </footer>
</body>

</html>