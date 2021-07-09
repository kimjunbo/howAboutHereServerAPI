<?php

function getViewlist($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select case when Acm.kind =1 then '모텔'
           when Acm.kind =2 then '호텔'
           when Acm.kind =3 then '펜션/풀빌라'
           when Acm.kind =4 then '리조트'
           when Acm.kind =5 then '캠핑/글램핑'
           when Acm.kind =6 then '게하/한옥' end as categoryString,
       Acm.kind as categoryIdx,Acm.idx as acmIdx,Acm.name,Acm.img,Acm.location
from SearchAcm
inner join Acm on Acm.idx = SearchAcm.acmIdx
where SearchAcm.userIdx=$userIdx and SearchAcm.status='N'
order by createdAt desc;";

    $st = $pdo->prepare($query);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    return $res;
}


function isExistDeletedViewlist($userIdx,$acmIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from SearchAcm where userIdx=$userIdx and acmIdx=$acmIdx and status='Y') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    return $res[0]['exist'];
}

function isExistViewlist($userIdx,$acmIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from SearchAcm where userIdx=$userIdx and acmIdx=$acmIdx and status='N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    return $res[0]['exist'];
}

function recreateViewlist($userIdx,$acmIdx){
    $pdo = pdoSqlConnect();
    $query = "update SearchAcm set status='N',createdAt=NOW() where userIdx=$userIdx and acmIdx=$acmIdx;";

    $st = $pdo->prepare($query);
    $st->execute([]);
}

function createViewlist($userIdx,$acmIdx){
    $pdo = pdoSqlConnect();
    $query = "insert into SearchAcm (userIdx,acmIdx) values ($userIdx,$acmIdx);";

    $st = $pdo->prepare($query);
    $st->execute([]);
}

function deleteViewlist($userIdx){
    $pdo = pdoSqlConnect();
    $query = "update SearchAcm set status='Y' where userIdx=$userIdx;";

    $st = $pdo->prepare($query);
    $st->execute([]);
}


