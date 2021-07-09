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




        case "getMyReview":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;



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

            $res->result = getMyReview($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "리뷰 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "createReview":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            $reserveNum=$req->reserveNum;
            $content=$req->content;
            $grade=$req->grade;
            $img=$req->img;

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

            if(empty($reserveNum) or empty($content) or empty($grade) ){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "예약번호,리뷰 내용,평점을 모두 입력해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidReservation($reserveNum)) {
                $res->isSuccess = FALSE;
                $res->code = 3001;
                $res->message = "취소되었거나 존재하지 않는 예약번호입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if(!isValidUserForCreatingReview($reserveNum,$userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 3002;
                $res->message = "해당 숙소를 예약한 회원만 작성할수 있습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isPossibleUserForCreatingReview($reserveNum,$userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 3005;
                $res->message = "숙소를 이용 전입니다. 숙소를 이용후 작성하실수 있습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isExistReview($reserveNum,$userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 3003;
                $res->message = "이미 작성한 리뷰가 존재합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            createReview($reserveNum,$userIdx,$content,$grade,$img);
            $result=array(array(reviewIdx=>getReviewIdxFromReserveNum($reserveNum)));
            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "리뷰 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "deleteReview":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            $reviewIdx=$vars['reviewIdx'];

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

            if (!isValidReview($reviewIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 3001;
                $res->message = "이미 삭제되었거나 존재하지 않는 리뷰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if (!isValidUserOfReview($userIdx,$reviewIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 3002;
                $res->message = "해당 리뷰의 삭제 권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }



            deleteReview($reviewIdx);

            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "리뷰 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;







    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}