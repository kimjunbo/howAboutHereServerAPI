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




        case "getReservationPage":
            http_response_code(200);


            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            $categoryIdx = $vars['categoryIdx'];
            $acmIdx= $vars['acmIdx'];
            $roomIdx= $vars['roomIdx'];
            $checkIn=$_GET['checkIn'];
            $checkOut=$_GET['checkOut'];

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "토큰이 존재하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 3000;
                $res->message = "토큰이 유효하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }



            $res->result = getReservationPage($roomIdx,$userIdx,$checkIn,$checkOut);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "에약 페이지 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "createReservation":
            http_response_code(200);


            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            $acmIdx= $vars['acmIdx'];
            $roomIdx= $vars['roomIdx'];
            $checkIn=$req->checkIn;
            $checkOut=$req->checkOut;
            $reserveName=$req->reserverName;

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "토큰이 존재하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 3000;
                $res->message = "토큰이 유효하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $checkUserIdx=$vars['userIdx'];
            if($checkUserIdx!=$userIdx){
                $res->isSuccess = FALSE;
                $res->code = 3004;
                $res->message = "권한이 없는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if(empty($checkIn) or empty($checkOut) or empty($reserveName) ){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "체크인 날짜, 체크아웃 날짜, 예약자 이름을 모두 입력해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            $checkInYear = substr($checkIn, 0, 4);
            $checkInMonth = substr($checkIn, 4, 2);
            $checkInDay = substr($checkIn, 6, 2);

            $checkOutYear = substr($checkOut, 0, 4);
            $checkOutMonth = substr($checkOut, 4, 2);
            $checkOutDay = substr($checkOut, 6, 2);



            if(!checkdate($checkInMonth,$checkInDay,$checkInYear) or !checkdate($checkOutMonth,$checkOutDay,$checkOutYear)){
                $res->isSuccess = FALSE;
                $res->code = 3002;
                $res->message = "존재하는 날짜가 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if($checkIn>=$checkOut){
                $res->isSuccess = FALSE;
                $res->code = 3003;
                $res->message = "체크인 날짜는 체크아웃 날짜와 같거나 늦을수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }





            if(!isPossibleCreateReservation($checkIn,$checkOut,$roomIdx)){
                $res->isSuccess = FALSE;
                $res->code = 3001;
                $res->message = "객실이 품절되어 예약이 불가합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }



            $reserveNum=mt_rand(10000000,99999999);

            while(isValidReserveNum($reserveNum)){
                $reserveNum=mt_rand(10000000,99999999);
            }

            createReservation($reserveNum,$userIdx,$roomIdx,$acmIdx,$checkIn,$checkOut,$reserveName);

            $result=array(array(reserveNum=>$reserveNum));

            $res->result=$result;
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "에약 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "getMyReservation":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            $statusIdx=$vars['statusIdx'];


            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "토큰이 존재하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 3000;
                $res->message = "토큰이 유효하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $checkUserIdx=$vars['userIdx'];
            if($checkUserIdx!=$userIdx){
                $res->isSuccess = FALSE;
                $res->code = 3001;
                $res->message = "권한이 없는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if($statusIdx!=1 and $statusIdx!=2 and $statusIdx!=3){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "올바르지 않은 statusIdx입니다. 1:이용전, 2:이용후, 3:취소됨";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            $res->result = getMyReservation($userIdx,$statusIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "에약 내역 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteReservation":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            $reserveNum=$vars['reserveNum'];

            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "토큰이 존재하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 3000;
                $res->message = "토큰이 유효하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $checkUserIdx=$vars['userIdx'];
            if($checkUserIdx!=$userIdx){
                $res->isSuccess = FALSE;
                $res->code = 3003;
                $res->message = "권한이 없는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if (!isValidReservation($reserveNum)) {
                $res->isSuccess = FALSE;
                $res->code = 3001;
                $res->message = "이미 취소되었거나 존재하지 않는 예약번호입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if (!isValidUserOfReservation($userIdx,$reserveNum)) {
                $res->isSuccess = FALSE;
                $res->code = 3002;
                $res->message = "취소 권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }



            deleteReservation($reserveNum);

            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "에약 취소 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;






        case "test":
            http_response_code(200);

            require './bootphp/bootpay.php';







    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}


