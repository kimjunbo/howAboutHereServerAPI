<?php

function getWishlist($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select case when Acm.kind =1 then '모텔'
           when Acm.kind =2 then '호텔'
           when Acm.kind =3 then '펜션/풀빌라'
           when Acm.kind =4 then '리조트'
           when Acm.kind =5 then '캠핑/글램핑'
           when Acm.kind =6 then '게하/한옥' end as categoryString,
       Acm.kind as categoryIdx,Acm.idx as acmIdx,Acm.name,Acm.img,Acm.location
from WishList
inner join Acm on Acm.idx = WishList.acmIdx
where WishList.userIdx=$userIdx and WishList.status='N';";

    $st = $pdo->prepare($query);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function createWishlist($userIdx,$acmIdx){
    $pdo = pdoSqlConnect();
    $query = "insert into WishList (userIdx,acmIdx) values ($userIdx,$acmIdx);";

    $st = $pdo->prepare($query);
    $st->execute([]);
}

function isExistWishlist($userIdx,$acmIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from WishList where userIdx=$userIdx and acmIdx=$acmIdx and status='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    return $res[0]['exist'];
}

function deleteWishlist($userIdx,$acmIdx){
    $pdo = pdoSqlConnect();
    $query = "update WishList set status='Y' where userIdx=$userIdx and acmIdx=$acmIdx;";

    $st = $pdo->prepare($query);
    $st->execute([]);
}


function isExistDeletedWishlist($userIdx,$acmIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from WishList where userIdx=$userIdx and acmIdx=$acmIdx and status='Y') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    return $res[0]['exist'];
}

function recreateWishlist($userIdx,$acmIdx){
    $pdo = pdoSqlConnect();
    $query = "update WishList set status='N'  where userIdx=$userIdx and acmIdx=$acmIdx;";

    $st = $pdo->prepare($query);
    $st->execute([]);
}



