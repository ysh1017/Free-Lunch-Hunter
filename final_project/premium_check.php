<?php
function isPremium($user_id, $conn)
{
    $sql = "SELECT premium FROM premium WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        return -1;
    } else {
        return (int) $result['premium'];
        //premium分級: 0 不是premium、1 是premium、2 是管理員
    }
}

function getUserBalance($user_id, $conn)
{
    $sql = "SELECT money FROM premium WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch();

    return $result ? $result['money'] : 0;
}
function getUserPremiumLevel($user_id, $conn)
{
    $sql = "SELECT premium FROM premium WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch();

    return $result ? $result['premium'] : 0;
}


