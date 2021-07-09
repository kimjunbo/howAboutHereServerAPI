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

        case "getAcmDetail":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;
            $isUser=0;

            $categoryIdx = $vars['categoryIdx'];
            $acmIdx= $vars['acmIdx'];
            $checkIn=$_GET['checkIn'];
            $checkOut=$_GET['checkOut'];


            if(empty($jwt)){
                $res->result = getAcmDetail($acmIdx,$checkIn,$checkOut,$isUser,$userIdx);
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "토큰이 존재하지 않아 비회원용 API입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->result = getAcmDetail($acmIdx,$checkIn,$checkOut,$isUser,$userIdx);
                $res->isSuccess = FALSE;
                $res->code = 3000;
                $res->message = "토큰이 유효하지 않아 비회원용 API입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $isUser=1;
            

            $res->result = getAcmDetail($acmIdx,$checkIn,$checkOut,$isUser,$userIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "숙소 상세조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "getAcmReview":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;
            $isUser=0;

            $categoryIdx = $vars['categoryIdx'];
            $acmIdx= $vars['acmIdx'];

            if(empty($jwt)){
                $res->result = getAcmReview($acmIdx,$isUser,$userIdx);
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "토큰이 존재하지 않아 비회원용 API입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->result = getAcmReview($acmIdx,$isUser,$userIdx);
                $res->isSuccess = FALSE;
                $res->code = 3000;
                $res->message = "토큰이 유효하지 않아 비회원용 API입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $isUser=1;



            $res->result = getAcmReview($acmIdx,$isUser,$userIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "숙소 리뷰 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "getAcmFacility":
            http_response_code(200);


            $categoryIdx = $vars['categoryIdx'];
            $acmIdx= $vars['acmIdx'];


            $aa=array();
            $aa['img']=getAcmFacility($acmIdx);
            $result=array($aa);

            $res->result = $result=array($aa);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "숙소 편의시설 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "getRoomDetail":
            http_response_code(200);


            $categoryIdx = $vars['categoryIdx'];
            $acmIdx= $vars['acmIdx'];
            $roomIdx= $vars['roomIdx'];
            $checkIn=$_GET['checkIn'];
            $checkOut=$_GET['checkOut'];


            $res->result = getRoomDetail($acmIdx,$roomIdx,$checkIn,$checkOut);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "숙소 객실 상세조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;





    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}