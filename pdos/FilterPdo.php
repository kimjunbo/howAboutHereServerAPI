<?php

//READ
function filter($categoryIdx,$alignIdx,$region,$regionFilter,$checkIn,$checkOut,$peopleFilter,$themaFilter,$themaFilterCnt,$ratingFilter,
              $typeFilter,$priceFilter,$facilityFilter,$facilityFilterCnt)
{
    $pdo = pdoSqlConnect();

    $alignQuery='';

    switch ($alignIdx){
        case 1: $alignQuery ="order by ifnull(wishCnt,0) desc,price,reviewAverage"; break;
        case 2: $alignQuery ="order by d.reviewAverage desc"; break;
        case 3: $alignQuery ="order by d.reviewCnt desc"; break;
        case 4: $alignQuery ="order by c.price"; break;
    }


    $AvailableRoomQuery=checkAvailableRoom($checkIn,$checkOut);

    $query = "select Acm.idx as acmIdx, Acm.name, Acm.img, substring_index(substring_index(location,' ',2),' ',-1) as location,surround,
       ifnull(c.price,'다른 날짜 확인') as price,
       ifnull(d.reviewCnt,0) as reviewCnt,
       ifnull(d.reviewAverage,0.0) as reviewAverage
from Acm
    
#검색조건 체크    
inner join (select *
from (#위치 날짜 인원 가격 필터 => 예약 가능한 방들
select c.acmIdx,roomIdx,price,remainRoom
from (select Acm.idx as acmIdx,remainRoom,people,loca,price,Room.idx as roomIdx
from (select *, $regionFilter
from Acm) as Acm
inner join Room on Room.acmIdx = Acm.idx
inner join ($AvailableRoomQuery) as c on c.roomIdx = Room.idx
# 베드타입 필터
$typeFilter
) as c

inner join (#취향 필터
select c1.acmIdx
from (select acmIdx,count(acmIdx) as count
from AcmThema
$themaFilter
group by acmIdx) as c1

#등급 필터
inner join (select *
from AcmRating
$ratingFilter
group by acmIdx) as c2 on c2.acmIdx= c1.acmIdx

#편의시설 필터
inner join (select acmIdx
from (select acmIdx,count(acmIdx) as count
from AcmFacility
$facilityFilter
group by acmIdx) as c
$facilityFilterCnt) as c3 on c3.acmIdx = c1.acmIdx

$themaFilterCnt
) as c1 on c1.acmIdx = c.acmIdx

  #인원필터
  $peopleFilter
  #지역필터
  and loca='$region'
  #가격필터
  $priceFilter
order by price
limit 100000
) as c
group by acmIdx) as c0 on c0.acmIdx=Acm.idx 

#방있는지 체크
left join (select *
from (#위치 날짜 인원 가격 필터 => 예약 가능한 방들
select c.acmIdx,roomIdx,price,remainRoom
from (select Acm.idx as acmIdx,remainRoom,people,loca,price,Room.idx as roomIdx
from (select *, $regionFilter
from Acm) as Acm
inner join Room on Room.acmIdx = Acm.idx
inner join ($AvailableRoomQuery) as c on c.roomIdx = Room.idx
# 베드타입 필터
$typeFilter
) as c

inner join (#취향 필터
select c1.acmIdx
from (select acmIdx,count(acmIdx) as count
from AcmThema
$themaFilter
group by acmIdx) as c1

#등급 필터
inner join (select *
from AcmRating
$ratingFilter
group by acmIdx) as c2 on c2.acmIdx= c1.acmIdx

#편의시설 필터
inner join (select acmIdx
from (select acmIdx,count(acmIdx) as count
from AcmFacility
$facilityFilter
group by acmIdx) as c
$facilityFilterCnt) as c3 on c3.acmIdx = c1.acmIdx

$themaFilterCnt
) as c1 on c1.acmIdx = c.acmIdx

where remainRoom>0
  #인원필터
  $peopleFilter
  #지역필터
  and loca='$region'
  #가격필터
  $priceFilter
order by price
limit 100000
) as c
group by acmIdx) as c on c.acmIdx=Acm.idx 
    


left join(select acmIdx,count(acmIdx) as reviewCnt,ROUND(sum(grade)/count(acmIdx), 1) as reviewAverage
from Review
inner join Reservation on Reservation.reserveNum=Review.reserveNum
group by acmIdx) as d on d.acmIdx=Acm.idx

left join (select acmIdx,count(acmIdx) as wishCnt
from WishList
where status ='N'
group by acmIdx) as e on e.acmIdx=Acm.idx

where Acm.kind=$categoryIdx
    
$alignQuery;";



    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    for($i=0; $i<count($res); $i++){

        $res[$i]["rating"]=getAcmRating($res[$i]["acmIdx"]);
    }

    return $res;


}


function search($alignIdx,$keyword,$categoryFilter,$checkIn,$checkOut,$peopleFilter,$themaFilter,$themaFilterCnt,$ratingFilter,
                $typeFilter,$priceFilter,$facilityFilter,$facilityFilterCnt)
{
    $pdo = pdoSqlConnect();

    $alignQuery='';

    switch ($alignIdx){
        case 1: $alignQuery ="order by ifnull(wishCnt,0) desc,price,reviewAverage"; break;
        case 2: $alignQuery ="order by d.reviewAverage desc"; break;
        case 3: $alignQuery ="order by d.reviewCnt desc"; break;
        case 4: $alignQuery ="order by c.price"; break;
    }

    $AvailableRoomQuery=checkAvailableRoom($checkIn,$checkOut);

    $query = "select Acm.idx as acmIdx, Acm.name, Acm.img, substring_index(substring_index(location,' ',2),' ',-1) as location,surround,
       ifnull(c.price,'다른 날짜 확인') as price,
       ifnull(d.reviewCnt,0) as reviewCnt,
       ifnull(d.reviewAverage,0.0) as reviewAverage
from Acm

#검색조건에 있는것들
inner join(select *
from (#위치 날짜 인원 가격 필터 => 예약 가능한 방들
select c.acmIdx,roomIdx,c.remainRoom,c.people,c.price
from (select Acm.idx as acmIdx,c.remainRoom,people,price,Room.idx as roomIdx
from (select *
from Acm
#키워드 필터
where Acm.location like concat('%','$keyword','%')
or Acm.name like concat('%','$keyword','%')
#카테고리idx 필터
$categoryFilter) as Acm
inner join Room on Room.acmIdx = Acm.idx
inner join ($AvailableRoomQuery) as c on c.roomIdx = Room.idx
# 베드타입 필터
$typeFilter
) as c

inner join (#취향 필터
select c1.acmIdx
from (select acmIdx,count(acmIdx) as count
from AcmThema
$themaFilter
group by acmIdx) as c1

#등급 필터
inner join (select *
from AcmRating
$ratingFilter
group by acmIdx) as c2 on c2.acmIdx= c1.acmIdx

#편의시설 필터
inner join (select acmIdx
from (select acmIdx,count(acmIdx) as count
from AcmFacility
$facilityFilter
group by acmIdx) as c
$facilityFilterCnt
    ) as c3 on c3.acmIdx = c1.acmIdx

$themaFilterCnt
) as c1 on c1.acmIdx = c.acmIdx
  #인원필터
$peopleFilter
  #가격필터
$priceFilter

order by price
limit 100000
) as c
group by acmIdx) as c0 on c0.acmIdx=Acm.idx

#존재하는 방체크
left join (select *
from (#위치 날짜 인원 가격 필터 => 예약 가능한 방들
select c.acmIdx,roomIdx,c.remainRoom,c.people,c.price
from (select Acm.idx as acmIdx,c.remainRoom,people,price,Room.idx as roomIdx
from (select *
from Acm
#키워드 필터
where Acm.location like concat('%','$keyword','%')
or Acm.name like concat('%','$keyword','%')
#카테고리idx 필터
$categoryFilter) as Acm
inner join Room on Room.acmIdx = Acm.idx
inner join ($AvailableRoomQuery) as c on c.roomIdx = Room.idx
# 베드타입 필터
$typeFilter
) as c

inner join (#취향 필터
select c1.acmIdx
from (select acmIdx,count(acmIdx) as count
from AcmThema
$themaFilter
group by acmIdx) as c1

#등급 필터
inner join (select *
from AcmRating
$ratingFilter
group by acmIdx) as c2 on c2.acmIdx= c1.acmIdx

#편의시설 필터
inner join (select acmIdx
from (select acmIdx,count(acmIdx) as count
from AcmFacility
$facilityFilter
group by acmIdx) as c
$facilityFilterCnt
    ) as c3 on c3.acmIdx = c1.acmIdx

$themaFilterCnt
) as c1 on c1.acmIdx = c.acmIdx


where remainRoom>0
  #인원필터
$peopleFilter
  #가격필터
$priceFilter

order by price
limit 100000
) as c
group by acmIdx) as c on c.acmIdx=Acm.idx

left join(select acmIdx,count(acmIdx) as reviewCnt,ROUND(sum(grade)/count(acmIdx),
1) as reviewAverage
from Review
inner join Reservation on Reservation.reserveNum=Review.reserveNum
group by acmIdx) as d on d.acmIdx=Acm.idx
    
left join (select acmIdx,count(acmIdx) as wishCnt
from WishList
where status ='N'
group by acmIdx) as e on e.acmIdx=Acm.idx
    
$alignQuery;";




    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    for($i=0; $i<count($res); $i++){

        $res[$i]["rating"]=getAcmRating($res[$i]["acmIdx"]);
    }

    return $res;



}




function checkAvailableRoom($checkIn,$checkOut){
    $pdo = pdoSqlConnect();

    $roomIdxArray=array();
    $RemainRoomCntArray=array();


    for($i=$checkIn+1;$i<$checkOut+1;$i++){
        $query = "select Room.acmIdx,Room.idx as roomIdx,ifnull(quantity-count,quantity) as remainRoom
from Room
left join (select acmIdx,roomIdx,count(roomIdx) as count
from Reservation
where checkIn < $i
and checkOut > $i and status='N'
group by roomIdx) as c on c.roomIdx=Room.idx;";


        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $st->execute([]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        #처음이라면
        if($i==$checkIn+1){
            for($j=0;$j<count($res);$j++){
                array_push($roomIdxArray, $res[$j]["roomIdx"]);
                array_push($RemainRoomCntArray, $res[$j]["remainRoom"]);
            }
        }

        for($j=0;$j<count($res);$j++){
            if($RemainRoomCntArray[$j]>$res[$j]["remainRoom"]){
                $RemainRoomCntArray[$j]=$res[$j]["remainRoom"];
            }
        }


    }

    $remainRoomFilter='case';

    for($i=0;$i<count($roomIdxArray);$i++){
        $remainRoomFilter=$remainRoomFilter." when Room.idx=$roomIdxArray[$i] then $RemainRoomCntArray[$i]";
    }


    $query = "select idx as roomIdx,
       $remainRoomFilter

    end as remainRoom
from Room";

    return $query;


}






