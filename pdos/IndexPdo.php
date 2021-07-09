<?php

//READ
function getUsers()
{
    $pdo = pdoSqlConnect();
    $query = "SELECT * from User;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return $res;
}

function getMypage($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select case when exists(select * from User where idx=$userIdx and id like '%kakao.com')  then '카카오로 로그인'  else id end as userId,
       idx as userIdx,profile,nickname,ifnull(name,0) as name,phone
from User where idx=$userIdx;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return $res;
}

function patchMypage($case,$nickname,$pwd_hash,$name,$phone,$userIdx){
    $pdo = pdoSqlConnect();

    switch ($case) {
        case 1 :
            $query = "update User set nickname='$nickname' where idx=?;";
            break;
        case 2 :
            $query = "update User set pwd='$pwd_hash' where idx=?;";
            break;
        case 3 :
            $query = "update User set name='$name' where idx=?;";
            break;
        case 4 :
            $query = "update User set phone='#$phone' where idx=?;";
            break;
        case 5 :
            $query = "update User set nickname='$nickname',pwd='$pwd_hash' where idx=?;";
            break;
        case 6 :
            $query = "update User set nickname='$nickname',name='$name' where idx=?;";
            break;
        case 7 :
            $query = "update User set nickname='$nickname',phone='#$phone' where idx=?;";
            break;
        case 8 :
            $query = "update User set pwd='$pwd_hash',name='$name'  where idx=?;";
            break;
        case 9 :
            $query = "update User set pwd='$pwd_hash',phone='#$phone' where idx=?;";
            break;
        case 10 :
            $query = "update User set name='$name',phone='#$phone' where idx=?;";
            break;
        case 11 :
            $query = "update User set nickname='$nickname',pwd='$pwd_hash',name='$name' where idx=?;";
            break;
        case 12 :
            $query = "update User set nickname='$nickname',pwd='$pwd_hash',phone='#$phone' where idx=?;";
            break;
        case 13 :
            $query = "update User set nickname='$nickname',name='$name',phone='#$phone' where idx=?;";
            break;
        case 14 :
            $query = "update User set pwd='$pwd_hash',name='$name',phone='#$phone' where idx=?;";
            break;
        case 15 :
            $query = "update User set nickname='$nickname',pwd='$pwd_hash',name='$name',phone='#$phone' where idx=?;";
            break;
    }


    $st =$pdo->prepare($query);
    $st->execute([$userIdx]);


}



function createUser($id, $pwd, $nickname,$phone,$profileNum)
{
    $pdo = pdoSqlConnect();

    switch ($profileNum){
        case 1 : $profileImg = "https://softsquared.s3.ap-northeast-2.amazonaws.com/howAboutHere/profile/profile1.png"; break;
        case 2 : $profileImg = "https://softsquared.s3.ap-northeast-2.amazonaws.com/howAboutHere/profile/profile2.png"; break;
        case 3 : $profileImg = "https://softsquared.s3.ap-northeast-2.amazonaws.com/howAboutHere/profile/profile3.png"; break;
        case 4 : $profileImg = "https://softsquared.s3.ap-northeast-2.amazonaws.com/howAboutHere/profile/profile4.png"; break;
    }

    switch ($profileNum){
        case 1 : $query = "insert into User (id, pwd, nickname, phone,profile) values (?,?,?,?,'$profileImg');"; break;
        case 2 : $query = "insert into User (id, pwd, nickname, phone,profile) values (?,?,?,?,'$profileImg');"; break;
        case 3 : $query = "insert into User (id, pwd, nickname, phone,profile) values (?,?,?,?,'$profileImg');"; break;
        case 4 : $query = "insert into User (id, pwd, nickname, phone,profile) values (?,?,?,?,'$profileImg');"; break;

    }

    $st = $pdo->prepare($query);
    $st->execute([$id, $pwd, $nickname,$phone]);

    $st = null;
    $pdo = null;

}

function isValidUserId($id)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from User where id = ? and status='U') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$id]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}



function isValidNickname($nickname)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from User where nickname = ? and status='U') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$nickname]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}




// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
