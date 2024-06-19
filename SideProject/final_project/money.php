<?php
function updatemoney($user_id, $money, $conn)
{
    $sql = "UPDATE premium SET money = money + :money WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':money', $money);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
}

// 增加不同活動的獎勵金額
function rewardForAction($user_id, $action, $conn)
{
    $moneyReward = 0;

    switch ($action) {
        case 'post':
            $moneyReward = 5;
            break;
        case 'comment':
            $moneyReward = 3;
            break;
        case 'like':
            $moneyReward = 1;
            break;
        case 'dislike':
            $moneyReward = 1;
            break;
    }

    if ($moneyReward > 0) {
        updatemoney($user_id, $moneyReward, $conn);
    }
}
?>
