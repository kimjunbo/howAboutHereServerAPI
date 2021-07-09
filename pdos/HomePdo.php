<?php

//READ
function getHomepage($isUser,$userIdx)
{
    if($isUser==1){
        $res[0]['viewlist']=getViewlist($userIdx);
    }
    $res[0]['hotelResort']=getHomeRcmdAcm(2);
    $res[0]['pensionCamping']=getHomeRcmdAcm(3);
    $res[0]['motel']=getHomeRcmdAcm(1);
    $res[0]['guestHouse']=getHomeRcmdAcm(6);


    return $res;


}

function getHomeRcmdAcm($categoryIdx){
    $pdo = pdoSqlConnect();

    switch ($categoryIdx){
        case 1:$categoryIdxQuery="where kind=1"; break;
        case 2:$categoryIdxQuery="where kind=2 or kind =4"; break;
        case 3:$categoryIdxQuery="where kind=3 or kind =5"; break;
        case 4:$categoryIdxQuery="where kind=2 or kind =4"; break;
        case 5:$categoryIdxQuery="where kind=3 or kind =5"; break;
        case 6:$categoryIdxQuery="where kind=6"; break;
    }

    $query = "select Acm.kind as categoryIdx, Acm.idx as acmIdx , Acm.img, Acm.name,surround,price,price2,
       ifnull(reviewCnt,0) as reviewCnt,
       ifnull(reviewAverage,0) as reviewAverage
from Acm

inner join (select *
from (select *
from Room
order by price limit 100) as c
group by c.acmIdx) as c on c.acmIdx=Acm.idx

left join (select acmIdx,count(acmIdx) as reviewCnt,ROUND(sum(grade)/count(acmIdx),
1) as reviewAverage
from Review
inner join Reservation on Reservation.reserveNum=Review.reserveNum
group by acmIdx) as d on d.acmIdx=Acm.Idx

left join (select acmIdx,count(acmIdx) as wishCnt
from WishList
where status ='N'
group by acmIdx) as e on e.acmIdx=Acm.idx

$categoryIdxQuery

order by ifnull(wishCnt,0) desc,price,reviewAverage
limit 3
;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    return $res;
}
