<?php

function getAcmDetail($acmIdx,$checkIn,$checkOut,$isUser,$userIdx){
    $pdo = pdoSqlConnect();

    $likeStatusQuery='';
    if($isUser==1){
        $likeStatusQuery=",
       exists(select * from WishList where WishList.acmIdx=$acmIdx and WishList.userIdx=$userIdx) as likeStatus";
    }
    $query = "select Acm.kind as categoryIdx,Acm.idx as acmIdx,name,location,ifnull(reviewAverage,0.0) as reviewAverage,ifnull(reviewCnt,0) as reviewCnt,
       concat( date_format($checkIn, '%c.%d'),' ',SUBSTR(_UTF8'일월화수목금토', DAYOFWEEK($checkIn), 1 )) as checkIn,
       concat( date_format($checkOut, '%c.%d'),' ',SUBSTR(_UTF8'일월화수목금토', DAYOFWEEK($checkOut), 1 )) as checkOut,
       concat(datediff($checkOut,$checkIn),'박') as night$likeStatusQuery,intro
from Acm

left join(select acmIdx,count(acmIdx) as reviewCnt,ROUND(sum(grade)/count(acmIdx), 1) as reviewAverage
from Review
inner join Reservation on Reservation.reserveNum=Review.reserveNum
group by acmIdx) as d on d.acmIdx=Acm.idx

where Acm.idx=$acmIdx;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    $res[0]["img"]= getAcmImg($acmIdx);
    $res[0]["rating"]=getAcmRating($acmIdx);
    $res[0]["rooms"] = getAcmDetailRoom($acmIdx,$checkIn,$checkOut);
    $res[0]["facility"] = getAcmFacility($acmIdx);
    $res[0]["notice"] = getAcmNotice($acmIdx);
    $res[0]["info"]= getAcmInfo($acmIdx);
    $res[0]["refund"]=getAcmRefund($acmIdx);
    $res[0]["reviews"]=getAcmBestReview($acmIdx,$userIdx,$isUser);


    return $res;


}


function getAcmRating($acmIdx){
    $pdo = pdoSqlConnect();
    $query = "select name as rating
from AcmRating
inner join Rating on Rating.idx=AcmRating.ratingIdx
where acmIdx=$acmIdx;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    $acmRatingArray = array();

    for($i=0; $i<count($res); $i++){
        array_push($acmRatingArray, $res[$i]["rating"]);
    }

    return $acmRatingArray;

}

function getAcmImg($acmIdx){
    $pdo = pdoSqlConnect();
    $query = "select *
from AcmImg
where acmIdx=$acmIdx;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    $acmImgArray = array();

    for($i=0; $i<count($res); $i++){
        array_push($acmImgArray, $res[$i]["img"]);
    }

    return $acmImgArray;
    
}



function getAcmDetailRoom($acmIdx,$checkIn,$checkOut){
    $pdo = pdoSqlConnect();

    $AvailableRoomQuery=checkAvailableRoom($checkIn,$checkOut);

    $query = "select Room.idx as roomIdx,Room.name,ifnull(c.price,'다른 날짜확인') as price ,ifnull(c.price2,'다른 날짜확인') as price2,Room.img
from Room

left join (select * from ($AvailableRoomQuery) as c  inner join Room on Room.idx = c.roomIdx where c.remainRoom>0 )as c on c.roomIdx=Room.idx

where Room.acmIdx=$acmIdx  ;";




    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;



    for($i=0; $i<count($res); $i++){
        $res[$i]["roomInfo"] = getRoomInfo($res[$i]["roomIdx"]);
    }


    return $res;

}


function getRoomInfo($roomIdx){
    $pdo = pdoSqlConnect();
    $query = "select *
from RoomInfo
where roomIdx=$roomIdx;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    $roomInfo = '';
    for($i=0; $i<count($res); $i++){
        if($i==1){
            $roomInfo=$roomInfo.', '.$res[$i]["content"];
            break;
        }
        $roomInfo=$roomInfo.$res[$i]["content"];
    }

    return $roomInfo;

}


function getAcmFacility($acmIdx){
    $pdo = pdoSqlConnect();
    $query = "select name,img
from AcmFacility
inner join Facility on Facility.idx= AcmFacility.facilityIdx
where acmIdx=$acmIdx;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;


    $acmFacilityArray = array();

    for($i=0; $i<count($res); $i++){
        array_push($acmFacilityArray, $res[$i]["img"]);
    }

    return $acmFacilityArray;

}

function getAcmNotice($acmIdx){
    $pdo = pdoSqlConnect();
    $query = "select *
from Notice
where acmIdx=$acmIdx;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;


    $acmNoticeArray = array();

    for($i=0; $i<count($res); $i++){
        array_push($acmNoticeArray, $res[$i]["content"]);
    }

    return $acmNoticeArray;

}



function getAcmInfo($acmIdx){
    $pdo = pdoSqlConnect();
    $query = "select *
from AcmInfo
where acmIdx=$acmIdx;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;


    $acmInfoArray = array();

    for($i=0; $i<count($res); $i++){
        array_push($acmInfoArray, $res[$i]["content"]);
    }

    return $acmInfoArray;

}


function getAcmRefund($acmIdx){
    $pdo = pdoSqlConnect();
    $query = "select *
from Refund
where acmIdx=$acmIdx;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;


    $acmRefundArray = array();

    for($i=0; $i<count($res); $i++){
        array_push($acmRefundArray, $res[$i]["content"]);
    }

    return $acmRefundArray;

}


function getAcmBestReview($acmIdx,$userIdx,$isUser){
    $pdo = pdoSqlConnect();
    $likeStatusQuery='';
    if($isUser==1){
        $likeStatusQuery=",
       exists(select * from ReviewLikes where userIdx=$userIdx and reviewIdx=Review.idx) as likeStatus";
    }

    $query = "select *
from (select Review.idx as reviewIdx,profile,nickname,grade,Review.createdAt,concat(Room.name,' 객실 이용' ) as roomName,content,ifnull(d.likeCnt,0) as likeCnt$likeStatusQuery
from Review
inner join Reservation on Reservation.reserveNum = Review.reserveNum
inner join User on Review.userIdx=User.idx
inner join Room on Room.idx=Reservation.roomIdx
left join (select reviewIdx,count(reviewIdx) as likeCnt
from ReviewLikes
group by reviewIdx) as d on d.reviewIdx=Review.idx
where Reservation.acmIdx=$acmIdx
group by Review.idx) as c
#1순위 평점,2순위 좋아요 수, 3순위 최신순
order by c.grade desc,c.likeCnt desc,createdAt desc
limit 3
;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    for($i=0;$i<count($res);$i++){
        $res[$i]['createdAt']=display_datetime($res[$i]['createdAt']);
        $res[$i]['img']=getReviewImg($res[$i]['reviewIdx']);
    }



    return $res;

}


function getAcmReview($acmIdx,$isUser,$userIdx){
    $pdo = pdoSqlConnect();

    #리뷰들 평균평점 and 개수
    $query = "select ifnull(ROUND(sum(grade)/count(acmIdx), 1),0.0) as reviewAverage,ifnull(count(idx),0) as reviewCnt
from Review
inner join Reservation on Reservation.reserveNum=Review.reserveNum
where Reservation.acmIdx=$acmIdx;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    #모든 리뷰들 조회
    $likeStatusQuery='';
    if($isUser==1){
        $likeStatusQuery=",
       exists(select * from ReviewLikes where userIdx=$userIdx and reviewIdx=Review.idx) as likeStatus";
    }


    $query = "select *
from (select Review.idx as reviewIdx,profile,nickname,grade,Review.createdAt,concat(Room.name,' 객실 이용' ) as roomName,content,ifnull(d.likeCnt,0) as likeCnt$likeStatusQuery
from Review
inner join Reservation on Reservation.reserveNum = Review.reserveNum
inner join User on Review.userIdx=User.idx
inner join Room on Room.idx=Reservation.roomIdx
left join (select reviewIdx,count(reviewIdx) as likeCnt
from ReviewLikes
group by reviewIdx) as d on d.reviewIdx=Review.idx
where Reservation.acmIdx=$acmIdx
group by Review.idx) as c
#1순위 평점,2순위 좋아요 수, 3순위 최신순
order by c.grade desc,c.likeCnt desc,createdAt desc
;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res2 = $st->fetchAll();

    for($i=0;$i<count($res2);$i++){
        $res2[$i]['createdAt']=display_datetime($res2[$i]['createdAt']);
        $res2[$i]['img']=getReviewImg($res2[$i]['reviewIdx']);
    }


    $res[0]['reviews']=$res2;


    return $res;
}


function getRoomDetail($acmIdx,$roomIdx,$checkIn,$checkOut){
    $pdo = pdoSqlConnect();

    $query = "select Acm.kind as categoryIdx,Acm.idx as acmIdx,Room.idx as roomIdx,Room.name,price,price2,
       concat( date_format($checkIn, '%m월 %d일'),' ',SUBSTR(_UTF8'일월화수목금토', DAYOFWEEK($checkIn), 1 ),'요일') as checkIn,
       concat( date_format($checkOut, '%m월 %d일'),' ',SUBSTR(_UTF8'일월화수목금토', DAYOFWEEK($checkOut), 1 ),'요일') as checkOut,
       concat(datediff($checkOut,$checkIn),'박') as night,
       price*(datediff($checkOut,$checkIn)) as paymentAmount
from Room
inner join Acm on Acm.idx=Room.acmIdx
where Room.idx=$roomIdx;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    #객실정보
    $query = "select *
from RoomInfo
where roomIdx=$roomIdx;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res2 = $st->fetchAll();

    $roomInfoArray = array();

    for($i=0; $i<count($res2); $i++){
        array_push($roomInfoArray, $res2[$i]["content"]);
    }


    #객실 편의시설
    $query = "select *
from AcmFacility
inner join Facility on Facility.idx=AcmFacility.facilityIdx
where acmIdx=$acmIdx;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res3 = $st->fetchAll();

    $acmFacilityString = '';

    for($i=0; $i<count($res3); $i++){
        if($i==0){
            $acmFacilityString=$res3[$i]['name'];
        }
        else{
            $acmFacilityString=$acmFacilityString.', '.$res3[$i]['name'];
        }
    }




    $res[0]['img']=getRoomImg($roomIdx);
    $res[0]['info']=$roomInfoArray;
    $res[0]['facility']=$acmFacilityString;
    $res[0]['refund']=getAcmRefund($acmIdx);

    return $res;


}

function getRoomImg($roomIdx){
    $pdo = pdoSqlConnect();

    $query = "select *
from RoomImg
where roomIdx=$roomIdx;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $roomImgArray = array();

    for($i=0; $i<count($res); $i++){
        array_push($roomImgArray, $res[$i]["img"]);
    }

    return $roomImgArray;
}




