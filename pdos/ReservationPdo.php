<?php
//READ
function getReservationPage($roomIdx,$userIdx,$checkIn,$checkOut)
{
    $pdo = pdoSqlConnect();
    $query = "select Acm.kind as categoryIdx,Acm.idx as acmIdx,Room.idx as roomIdx,Acm.name as acmName,Room.name as roomName,
       RoomInfo.content as roomInfo,price*(datediff($checkOut,$checkIn)) as paymentAmount,
       concat( date_format($checkIn, '%c.%d'),' ',SUBSTR(_UTF8'일월화수목금토', DAYOFWEEK($checkIn), 1 )) as checkIn,
       time_format(Acm.checkIn,'%H:%i') as checkInTime,
       concat( date_format($checkOut, '%c.%d'),' ',SUBSTR(_UTF8'일월화수목금토', DAYOFWEEK($checkOut), 1 )) as checkOut,
       time_format(Acm.checkOut,'%H:%i') as checkOutTime,
       concat(datediff($checkOut,$checkIn),'박') as night,
       ifnull(c.name,0) as reserverName,
       c.phone as reserverPhone


from Room
inner join Acm on Acm.idx=Room.acmIdx
inner join RoomInfo on RoomInfo.roomIdx=Room.idx
inner join (select * from User where idx=$userIdx) as c
where roomIdx=$roomIdx
limit 1;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();



    return $res;


}

function isPossibleCreateReservation($checkIn,$checkOut,$roomIdx){
    $pdo = pdoSqlConnect();

    $AvailableRoomQuery=checkAvailableRoom($checkIn,$checkOut);

    $query = "select case when c.remainRoom>0 then 1 else 0 end as possible
from Room
left join ($AvailableRoomQuery) as c on Room.idx=c.roomIdx
where idx=$roomIdx
;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();




    return $res[0]['possible'];
}

function isValidReserveNum($reserveNum){
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from Reservation where reserveNum=$reserveNum) as exist";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();



    return $res[0]['exist'];
}

function createReservation($reserveNum,$userIdx,$roomIdx,$acmIdx,$checkIn,$checkOut,$reserveName){
    $pdo = pdoSqlConnect();
    $query = "select concat(substring_index(checkIn,':',1),substring_index(substring_index(checkIn,':',2),':',-1),substring_index(checkIn,':',-1)) as checkIn,
       concat(substring_index(checkOut,':',1),substring_index(substring_index(checkOut,':',2),':',-1),substring_index(checkOut,':',-1)) as checkOut
from Acm where idx=$acmIdx;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();


    $checkIn=$checkIn*1000000+$res[0]['checkIn'];
    $checkOut=$checkOut*1000000+$res[0]['checkOut'];

    $query = "INSERT INTO Reservation (reserveNum,userIdx,roomIdx,acmIdx,checkIn,checkOut,reserveName)
VALUES ($reserveNum,$userIdx,$roomIdx,$acmIdx,$checkIn,$checkOut,'$reserveName');";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
}


function getMyReservation($userIdx,$statusIdx){
    $pdo = pdoSqlConnect();

    switch ($statusIdx){
        #이용전
        case 1: $statusQuery1="concat(datediff(Reservation.checkIn,current_timestamp()),'일 ',
    time_format(timediff(timediff(Reservation.checkIn,current_timestamp()),
        datediff(Reservation.checkIn,current_timestamp())*240000),'%k시간 %i분 뒤 입실 가능')) as remainTime,";
            $statusQuery2="and Reservation.checkIn>current_timestamp and Reservation.status='N'";break;

        #이용후
        case 2: $statusQuery1="";
            $statusQuery2="and Reservation.checkIn<=current_timestamp and Reservation.status='N'";break;

        #취소됨
        case 3: $statusQuery1="";
            $statusQuery2="and Reservation.status='Y'";break;
    }

    $query = "select $statusQuery1
       Reservation.reserveNum,Acm.location,
       Acm.kind as categoryIdx,Acm.idx as acmIdx,Acm.img,Acm.name as acmName,
       Room.idx as roomIdx,Room.name as roomName,Reservation.checkIn,Reservation.checkOut,
       concat(date_format(Reservation.checkIn,'%c.%e (') ,
           SUBSTR(_UTF8'일월화수목금토', DAYOFWEEK(Reservation.checkIn), 1 ),
           date_format(Reservation.checkIn,') %H:%i') ) as checkInString,
       concat(date_format(Reservation.checkOut,'%c.%e (') ,
           SUBSTR(_UTF8'일월화수목금토', DAYOFWEEK(Reservation.checkOut), 1 ),
           date_format(Reservation.checkOut,') %H:%i') ) as checkOutString

from Reservation
inner join Acm on Acm.idx=Reservation.acmIdx
inner join Room on Room.idx= Reservation.roomIdx
where userIdx=$userIdx $statusQuery2
;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    return $res;

}


function deleteReservation($reserveNum){
    $pdo = pdoSqlConnect();

    $query = "update Reservation set status='Y' where reserveNum=$reserveNum;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
}

function isValidReservation($reserveNum){
    $pdo = pdoSqlConnect();

    $query = "select EXISTS(select * from Reservation where reserveNum=$reserveNum and status='N') as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    return $res[0]['exist'];
}

function isValidUserOfReservation($userIdx,$reserveNum){

    $pdo = pdoSqlConnect();

    $query = "select EXISTS(select * from Reservation where userIdx=$userIdx and reserveNum=$reserveNum and status='N') as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    return $res[0]['exist'];

}



