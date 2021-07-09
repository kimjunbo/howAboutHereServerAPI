<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {

        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;
        /*
         * API No. 4
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */

        case "filter":
            http_response_code(200);

            $toolType=$_GET['type'];

            $categoryIdx = $vars['categoryIdx'];
            $alignIdx=$_GET['alignIdx'];
            $region=$_GET['region'];
            $keyword=$_GET['keyword'];
            $checkIn=$_GET['checkIn']+0;
            $checkOut=$_GET['checkOut']+1;
            $people=$_GET['people'];

            if($toolType!=1 and $toolType!=2){
                $res->isSuccess = FALSE;
                $res->code = 2010;
                $res->message = "올바르지 않은 type입니다. (1:필터 조회, 2:검색 조회)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($toolType==1){
                if($categoryIdx!=1 and $categoryIdx!=2 and $categoryIdx!=3
                    and $categoryIdx!=4 and $categoryIdx!=5 and $categoryIdx!=6 ){
                    $res->isSuccess = FALSE;
                    $res->code = 2001;
                    $res->message = "올바르지 않은 categoryIdx입니다. (1:모텔, 2:호텔 ,3:펜션, 4:리조트, 5:캠핑, 6:게하";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                if(empty($region)){
                    $res->isSuccess = FALSE;
                    $res->code = 2004;
                    $res->message = "지역을 입력해주세요.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

            }
            else if($toolType==2){
                if($categoryIdx!=0 and $categoryIdx!=1 and $categoryIdx!=2 and $categoryIdx!=3
                    and $categoryIdx!=4 and $categoryIdx!=5 and $categoryIdx!=6 ){
                    $res->isSuccess = FALSE;
                    $res->code = 2001;
                    $res->message = "올바르지 않은 categoryIdx입니다. (0:전체, 1:모텔, 2:호텔 ,3:펜션, 4:리조트, 5:캠핑, 6:게하";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                if(empty($keyword)){
                    $res->isSuccess = FALSE;
                    $res->code = 2004;
                    $res->message = "검색어를 입력해주세요.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            if(empty($alignIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "정렬값을 입력해주세요.(1:여기어때 추천순,2:평점높은순,3:리뷰많은순,4:낮은 가격순)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($alignIdx!=1 and $alignIdx!=2 and $alignIdx!=3 and $alignIdx!=4 ){
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "올바르지 않은 alignIdx입니다. (1:여기어때 추천순,2:평점높은순,3:리뷰많은순,4:낮은 가격순)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }



            if(empty($checkIn) or empty($checkOut)){
                $res->isSuccess = FALSE;
                $res->code = 2005;
                $res->message = "체크인 체크아웃 날짜를 모두 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if( (!empty($checkIn) and !(is_int($checkIn) and $checkIn>=10000000 and $checkIn<100000000)) or
                (!empty($checkOut) and !(is_int($checkOut) and $checkOut>=10000000 and $checkOut<100000000))){
                $res->isSuccess = FALSE;
                $res->code = 2006;
                $res->message = "날짜는 8자리 숫자를 입력해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $checkInYear = substr($checkIn, 0, 4);
            $checkInMonth = substr($checkIn, 4, 2);
            $checkInDay = substr($checkIn, 6, 2);

            $checkOutYear = substr($checkOut, 0, 4);
            $checkOutMonth = substr($checkOut, 4, 2);
            $checkOutDay = substr($checkOut, 6, 2);



            if(!checkdate($checkInMonth,$checkInDay,$checkInYear) or !checkdate($checkOutMonth,$checkOutDay,$checkOutYear)){
                $res->isSuccess = FALSE;
                $res->code = 2007;
                $res->message = "존재하는 날짜가 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!empty($people) and !($people>=1)){
                $res->isSuccess = FALSE;
                $res->code = 2008;
                $res->message = "사람수는 자연수를 입력해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }



            #----------------------------------------------------------------------------------validation 끝


            #카테고리 필터
            $categoryFilter="";
            switch ($categoryIdx){
                case 0:$categoryFilter=""; break;
                case 1:$categoryFilter="and Acm.kind =1"; break;
                case 2:$categoryFilter="and Acm.kind =2 or Acm.kind=4"; break;
                case 3:$categoryFilter="and Acm.kind =3"; break;
                case 4:$categoryFilter="and Acm.kind =2 or Acm.kind=4"; break;
                case 5:$categoryFilter="and Acm.kind =5"; break;
            }

            #지역필터
            $regionFilter='';
            if($region=='서울 전체'){
                $region="서울";
                $regionFilter="substring_index(location, ' ', 1) as loca";
            }
            if($region=='강남/역삼/삼성/신사/청담'){
                $region="강남구";
                $regionFilter="substring_index(substring_index(location, ' ', 2), ' ', -1) as loca";
            }
            if($region=='서초/교대'){
                $region="서초구";
                $regionFilter="substring_index(substring_index(location, ' ', 2), ' ', -1) as loca";
            }

            #인원 필터
            $peopleFilter ='';
            if(!empty($people)){
                $peopleFilter='and c.people>='.$people;
            }

            #테마 필터
            $thema = array('HOT한물놀이','인생사진','해돋이명소','바닷가','NEW오픈','산행코스','오션뷰','루프탑칵테일','파인다이닝','피트니스','키즈전용','놀이동산근처');

            $themaFilter="";
            $themaFilterCnt=0;
            for($i=0; $i<count($thema); $i++){
                if($_GET[$thema[$i]]==1){
                    if($themaFilter==null){
                        $themaFilter=$themaFilter.'where themaIdx='.($i+1);
                    }else{
                        $themaFilter=$themaFilter.' or themaIdx='.($i+1);
                    }
                    $themaFilterCnt+=1;
                }
            }

            if($themaFilterCnt!=0){
                $themaFilterCnt = 'where c1.count='.$themaFilterCnt;
            }
            else{
                $themaFilterCnt='';
            }


            #등급 필터
            $rating = array('블랙','5성급','4성급','리조트','가족호텔','풀빌라');

            $ratingFilter="";
            for($i=0; $i<count($rating); $i++){
                if($_GET[$rating[$i]]==1){
                    if($ratingFilter==null){
                        $ratingFilter=$ratingFilter.'where ratingIdx='.($i+1);
                    }else{
                        $ratingFilter=$ratingFilter.' or ratingIdx='.($i+1);
                    }
                }
            }

            #가격 필터
            $price = array('~5만원','5~10만원','10~15만원','15~20만원','20~25만원','25~30만원','30만원이상~');

            $priceFilter="";
            $priceFilterNum=100;

            for($i=0; $i<count($price); $i++){
                if($_GET[$price[$i]]==1){
                    $priceFilterNum=$i;
                    break;
                }
            }
            switch ($priceFilterNum){
                case 0:$priceFilter='and price<=50000'; break;
                case 1:$priceFilter='and price>=50000 and price<=100000'; break;
                case 2:$priceFilter='and price>=100000 and price<=150000'; break;
                case 3:$priceFilter='and price>=150000 and price<=200000'; break;
                case 4:$priceFilter='and price>=200000 and price<=250000'; break;
                case 5:$priceFilter='and price>=250000 and price<=300000'; break;
                case 6:$priceFilter='and price>=300000'; break;
            }

            #베드타입 필터
            $type = array('싱글','더블','트윈','온돌');

            $typeFilter="";
            for($i=0; $i<count($type); $i++){
                if($_GET[$type[$i]]==1){
                    if($typeFilter==null){
                        $typeFilter=$typeFilter."where type='".$type[$i]."'";
                    }else{
                        $typeFilter=$typeFilter." or type='".$type[$i]."'";
                    }
                }
            }


            #편의시설 필터
            $facility = array('에어컨','욕실용품','욕조','BBQ','짐보관가능','조식포함','카페','편의점','장애인편의','건조기',
                '엘레베이터','피트니스','냉장고','무료주차','골프장','드라이기','다리미','주방/식당','라운지','미니바',
                '금연','주차장','공용PC','반려견동반','프린터사용','레스토랑','사우나','객실샤워실','객실스파','수영장',
                'TV','와이파이');

            $facilityFilter="";
            $facilityFilterCnt=0;
            for($i=0; $i<count($facility); $i++){
                if($_GET[$facility[$i]]==1){
                    if($facilityFilter==null){
                        $facilityFilter=$facilityFilter.'where facilityIdx='.($i+1);
                    }else{
                        $facilityFilter=$facilityFilter.' or facilityIdx='.($i+1);
                    }
                    $facilityFilterCnt+=1;
                }
            }

            if($facilityFilterCnt!=0){
                $facilityFilterCnt = 'where c.count='.$facilityFilterCnt;
            }
            else{
                $facilityFilterCnt='';
            }


            #response 값
            if($toolType==1){
                if(count(filter($categoryIdx,$alignIdx,$region,$regionFilter,$checkIn,$checkOut,$peopleFilter,$themaFilter,$themaFilterCnt,$ratingFilter,
                        $typeFilter,$priceFilter,$facilityFilter,$facilityFilterCnt))==0){
                    $res->isSuccess = FALSE;
                    $res->code = 3001;
                    $res->message = "검색 결과가 없습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }


                $res->result = filter($categoryIdx,$alignIdx,$region,$regionFilter,$checkIn,$checkOut,$peopleFilter,$themaFilter,$themaFilterCnt,$ratingFilter,
                    $typeFilter,$priceFilter,$facilityFilter,$facilityFilterCnt);

                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "숙소 필터 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if($toolType==2){
                if(count(search($alignIdx,$keyword,$categoryFilter,$checkIn,$checkOut,$peopleFilter,$themaFilter,$themaFilterCnt,$ratingFilter,
                        $typeFilter,$priceFilter,$facilityFilter,$facilityFilterCnt))==0){
                    $res->isSuccess = FALSE;
                    $res->code = 3001;
                    $res->message = "검색 결과가 없습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }

                $res->result = search($alignIdx,$keyword,$categoryFilter,$checkIn,$checkOut,$peopleFilter,$themaFilter,$themaFilterCnt,$ratingFilter,
                    $typeFilter,$priceFilter,$facilityFilter,$facilityFilterCnt);

                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "숙소 검색 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }





        case "search":
            http_response_code(200);



            $categoryIdx = $vars['categoryIdx'];
            $alignIdx=$_GET['alignIdx'];
            $keyword=$_GET['keyword'];
            $checkIn=$_GET['checkIn']+0;
            $checkOut=$_GET['checkOut']+1;
            $people=$_GET['people'];




            if(empty($alignIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "정렬값을 입력해주세요.(1:여기어때 추천순,2:평점높은순,3:리뷰많은순,4:낮은 가격순)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($alignIdx!=1 and $alignIdx!=2 and $alignIdx!=3 and $alignIdx!=4 ){
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "올바르지 않은 alignIdx입니다. (1:여기어때 추천순,2:평점높은순,3:리뷰많은순,4:낮은 가격순)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }



            if(empty($checkIn) or empty($checkOut)){
                $res->isSuccess = FALSE;
                $res->code = 2005;
                $res->message = "체크인 체크아웃 날짜를 모두 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if( (!empty($checkIn) and !(is_int($checkIn) and $checkIn>=10000000 and $checkIn<100000000)) or
                (!empty($checkOut) and !(is_int($checkOut) and $checkOut>=10000000 and $checkOut<100000000))){
                $res->isSuccess = FALSE;
                $res->code = 2006;
                $res->message = "날짜는 8자리 숫자를 입력해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $checkInYear = substr($checkIn, 0, 4);
            $checkInMonth = substr($checkIn, 4, 2);
            $checkInDay = substr($checkIn, 6, 2);

            $checkOutYear = substr($checkOut, 0, 4);
            $checkOutMonth = substr($checkOut, 4, 2);
            $checkOutDay = substr($checkOut, 6, 2);



            if(!checkdate($checkInMonth,$checkInDay,$checkInYear) or !checkdate($checkOutMonth,$checkOutDay,$checkOutYear)){
                $res->isSuccess = FALSE;
                $res->code = 2007;
                $res->message = "존재하는 날짜가 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!empty($people) and !($people>=1)){
                $res->isSuccess = FALSE;
                $res->code = 2008;
                $res->message = "사람수는 자연수를 입력해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }





            #카테고리 필터
            $categoryFilter="";
            switch ($categoryIdx){
                case 0:$categoryFilter=""; break;
                case 1:$categoryFilter="and Acm.kind =1"; break;
                case 2:$categoryFilter="and Acm.kind =2 or Acm.kind=4"; break;
                case 3:$categoryFilter="and Acm.kind =3"; break;
                case 4:$categoryFilter="and Acm.kind =2 or Acm.kind=4"; break;
                case 5:$categoryFilter="and Acm.kind =5"; break;
            }



            #인원 필터
            $peopleFilter ='';
            if(!empty($people)){
                $peopleFilter='and c.people>='.$people;
            }

            #테마 필터
            $thema = array('HOT한물놀이','인생사진','해돋이명소','바닷가','NEW오픈','산행코스','오션뷰','루프탑칵테일','파인다이닝','피트니스','키즈전용','놀이동산근처');

            $themaFilter="";
            $themaFilterCnt=0;
            for($i=0; $i<count($thema); $i++){
                if($_GET[$thema[$i]]==1){
                    if($themaFilter==null){
                        $themaFilter=$themaFilter.'where themaIdx='.($i+1);
                    }else{
                        $themaFilter=$themaFilter.' or themaIdx='.($i+1);
                    }
                    $themaFilterCnt+=1;
                }
            }

            if($themaFilterCnt!=0){
                $themaFilterCnt = 'where c1.count='.$themaFilterCnt;
            }
            else{
                $themaFilterCnt='';
            }


            #등급 필터
            $rating = array('블랙','5성급','4성급','리조트','가족호텔','풀빌라');

            $ratingFilter="";
            for($i=0; $i<count($rating); $i++){
                if($_GET[$rating[$i]]==1){
                    if($ratingFilter==null){
                        $ratingFilter=$ratingFilter.'where ratingIdx='.($i+1);
                    }else{
                        $ratingFilter=$ratingFilter.' or ratingIdx='.($i+1);
                    }
                }
            }

            #가격 필터
            $price = array('~5만원','5~10만원','10~15만원','15~20만원','20~25만원','25~30만원','30만원이상~');

            $priceFilter="";
            $priceFilterNum=100;

            for($i=0; $i<count($price); $i++){
                if($_GET[$price[$i]]==1){
                    $priceFilterNum=$i;
                    break;
                }
            }
            switch ($priceFilterNum){
                case 0:$priceFilter='and price<=50000'; break;
                case 1:$priceFilter='and price>=50000 and price<=100000'; break;
                case 2:$priceFilter='and price>=100000 and price<=150000'; break;
                case 3:$priceFilter='and price>=150000 and price<=200000'; break;
                case 4:$priceFilter='and price>=200000 and price<=250000'; break;
                case 5:$priceFilter='and price>=250000 and price<=300000'; break;
                case 6:$priceFilter='and price>=300000'; break;
            }

            #베드타입 필터
            $type = array('싱글','더블','트윈','온돌');

            $typeFilter="";
            for($i=0; $i<count($type); $i++){
                if($_GET[$type[$i]]==1){
                    if($typeFilter==null){
                        $typeFilter=$typeFilter."where type='".$type[$i]."'";
                    }else{
                        $typeFilter=$typeFilter." or type='".$type[$i]."'";
                    }
                }
            }


            #편의시설 필터
            $facility = array('에어컨','욕실용품','욕조','BBQ','짐보관가능','조식포함','카페','편의점','장애인편의','건조기',
                '엘레베이터','피트니스','냉장고','무료주차','골프장','드라이기','다리미','주방/식당','라운지','미니바',
                '금연','주차장','공용PC','반려견동반','프린터사용','레스토랑','사우나','객실샤워실','객실스파','수영장',
                'TV','와이파이');

            $facilityFilter="";
            $facilityFilterCnt=0;
            for($i=0; $i<count($facility); $i++){
                if($_GET[$facility[$i]]==1){
                    if($facilityFilter==null){
                        $facilityFilter=$facilityFilter.'where facilityIdx='.($i+1);
                    }else{
                        $facilityFilter=$facilityFilter.' or facilityIdx='.($i+1);
                    }
                    $facilityFilterCnt+=1;
                }
            }

            if($facilityFilterCnt!=0){
                $facilityFilterCnt = 'where c.count='.$facilityFilterCnt;
            }
            else{
                $facilityFilterCnt='';
            }


            if(count(search($alignIdx,$keyword,$categoryFilter,$checkIn,$checkOut,$peopleFilter,$themaFilter,$themaFilterCnt,$ratingFilter,
                $typeFilter,$priceFilter,$facilityFilter,$facilityFilterCnt))==0){
                $res->isSuccess = FALSE;
                $res->code = 3001;
                $res->message = "검색 결과가 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = search($alignIdx,$keyword,$categoryFilter,$checkIn,$checkOut,$peopleFilter,$themaFilter,$themaFilterCnt,$ratingFilter,
                $typeFilter,$priceFilter,$facilityFilter,$facilityFilterCnt);



            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "숙소 검색 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;





    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
