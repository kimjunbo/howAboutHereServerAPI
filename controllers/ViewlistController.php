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

        case "getViewlist":
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

            $res->result = getViewlist($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "최근 본 상품 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;



        case "createViewlist":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;

            $acmIdx=$vars['acmIdx'];

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

            if(!isValidAcm($acmIdx)){
                $res->isSuccess = FALSE;
                $res->code = 3001;
                $res->message = "존재하지 않는 acmIdx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if(isExistViewlist($userIdx,$acmIdx)){
                recreateViewlist($userIdx,$acmIdx);
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "최근 본 상품 업데이트 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(isExistDeletedViewlist($userIdx,$acmIdx)){
                recreateViewlist($userIdx,$acmIdx);
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "최근 본 상품 생성 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }





            createViewlist($userIdx,$acmIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "최근 본 상품 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;



        case "deleteViewlist":
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




            deleteViewlist($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "최근 본 상품 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;







    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}