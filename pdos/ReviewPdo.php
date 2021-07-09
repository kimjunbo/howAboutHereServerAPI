<?php

function getReviewImg($reviewIdx){
    $pdo = pdoSqlConnect();

    $query = "select *
from ReviewImg
where reviewIdx=$reviewIdx;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $reviewImgArray = array();

    for($i=0; $i<count($res); $i++){
        array_push($reviewImgArray, $res[$i]["img"]);
    }

    return $reviewImgArray;
}



function getMyReview($userIdx){
    $pdo = pdoSqlConnect();

    $query = "select profile,nickname,ifnull(reviewCnt,0) as reviewCnt
from User
left join (select userIdx,count(idx) as reviewCnt
from Review where userIdx=$userIdx
group by Review.userIdx) as c on c.userIdx=User.idx
where User.idx=$userIdx;
";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    $query = "select Review.idx as reviewIdx,Acm.kind as categoryIdx,Acm.idx as acmIdx,Acm.img as acmImg,Acm.name as acmName,
       Room.idx as roomIdx,Room.name as roomName,
       grade,
       Review.createdAt,
       content
from Review
inner join Reservation on Reservation.reserveNum=Review.reserveNum
inner join Acm on Acm.idx = Reservation.acmIdx
inner join Room on Room.idx = Reservation.roomIdx
where Review.userIdx=$userIdx;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res2 = $st->fetchAll();

    for($i=0;$i<count($res2);$i++){
        $res2[$i]['img']=getReviewImg($res2[$i]['reviewIdx']);
        $res2[$i]['createdAt']=display_datetime($res2[$i]['createdAt']);
    }


    $res[0]['reviews']=$res2;

    return $res;
}

function isExistReview($reserveNum,$userIdx){
    $pdo = pdoSqlConnect();

    $query = "select EXISTS(select * from Review where reserveNum=$reserveNum and userIdx=$userIdx and status='N') as exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    return $res[0]['exist'];
}

#예약했는지 체크
function isValidUserForCreatingReview($reserveNum,$userIdx){
    $pdo = pdoSqlConnect();

    $query = "select EXISTS(select * from Reservation where reserveNum=$reserveNum and userIdx=$userIdx and status='N') as exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    return $res[0]['exist'];
}

#이용후인지 체크
function isPossibleUserForCreatingReview($reserveNum,$userIdx){
    $pdo = pdoSqlConnect();

    $query = "select EXISTS(select * from Reservation where reserveNum=$reserveNum and userIdx=$userIdx and status='N' and checkIn<=current_timestamp()) as exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    return $res[0]['exist'];
}


function createReview($reserveNum,$userIdx,$content,$grade,$img){

    $pdo = pdoSqlConnect();

    $query = "insert into Review (reserveNum,userIdx,content,grade) values ($reserveNum,$userIdx,'$content',$grade)";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);

    #reviewIdx가져오기
    $query = "select idx as reviewIdx from Review where reserveNum=$reserveNum;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $reviewIdx=$res[0]['reviewIdx'];



    //리뷰 이미지
    for($i=0;$i<count($img);$i++){
        $query = "insert into ReviewImg (reviewIdx,img) values ($reviewIdx,'$img[$i]');";

        $st = $pdo->prepare($query);
        $st->execute();
    }


}


function deleteReview($reviewIdx){
    $pdo = pdoSqlConnect();

    $query = "update Review set status='Y' where idx=$reviewIdx;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
}

function isValidReview($reviewIdx){
    $pdo = pdoSqlConnect();

    $query = "select EXISTS(select * from Review where idx=$reviewIdx and status='N') as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    return $res[0]['exist'];
}

function isValidUserOfReview($userIdx,$reviewIdx){

    $pdo = pdoSqlConnect();

    $query = "select EXISTS(select * from Review where userIdx=$userIdx and idx=$reviewIdx and status='N') as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    return $res[0]['exist'];

}

function getReviewIdxFromReserveNum($reserveNum){
    $pdo = pdoSqlConnect();

    $query = "select idx
from Review where reserveNum=$reserveNum;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    return $res[0]['idx'];
}