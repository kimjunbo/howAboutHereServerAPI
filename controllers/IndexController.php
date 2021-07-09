<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            $res->result = 'JUNBOAPI';
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "API 연동 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


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

        case "getUsers":
            http_response_code(200);

            $res->result = getUsers();
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "유저조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getMypage":
            http_response_code(200);

            $checkUserIdx=$vars['userIdx'];

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

            $res->result = getMypage($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "마이페이지 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;



        case "sign-up":
            http_response_code(200);

            // Packet의 Body에서 데이터를 파싱합니다.

            $id = $req->id;
            $pwd =$req->pwd;
            $pwd_hash = password_hash($pwd, PASSWORD_DEFAULT); // Password Hash
            $nickname = $req->nickname;
            $phone = "#01012344321";

            $profileNum=mt_rand(1,4);

//
//            $id = $_POST['id'];
//            $pwd = $_POST['pwd'];
//            $pwd_hash = password_hash($pwd, PASSWORD_DEFAULT); // Password Hash
//            $nickname = $_POST['nickname'];
//            $phone = (int)$_POST['phone'];

            #필수입력사항 체크
            if(empty(id) or empty($pwd) or empty($nickname) or empty($phone)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "입력사항을 모두 기입해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            #이메일 형식 체크
            if(!preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $id)){
                $res->isSuccess = FALSE;
                $res->code = 3003;
                $res->message = "id를 이메일형식으로 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            };

            #아이디 중복체크
            if(isValidUserId($id)){
                $res->isSuccess = FALSE;
                $res->code = 3000;
                $res->message = "중복된 id입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            #닉네임 중복체크
            if(isValidNickname($nickname)){
                $res->isSuccess = FALSE;
                $res->code = 3001;
                $res->message = "중복된 닉네임입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            #패스워드 유효성 검사
            if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&^])[A-Za-z\d$@$!%*#?&^]{8,}$/',$pwd) ) {
                $res->isSuccess = FALSE;
                $res->code = 3002;
                $res->message = "비밀번호는 문자,숫자,특수문자 하나이상 8자이상-16자 이하입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            createUser($id, $pwd_hash, $nickname,$phone,$profileNum);

            $userIdx = getUserIdxByID($req->id);  // JWTPdo.php 에 구현
            $jwt = getJWT($userIdx, JWT_SECRET_KEY); // function.php 에 구현


            $aa=array(jwt=>$jwt,userIdx=>$userIdx);
            $result=array($aa);

            $res->result=  $result;
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "회원가입 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "login":
            http_response_code(200);

            $id = $req->id;
            $pwd =  $req->pwd;

            #필수입력사항 체크
            if(empty(id) or empty($pwd)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "입력사항을 모두 기입해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            // 1) 로그인 시 email, password 받기
            if (!isValidUser($id, $pwd)) { // JWTPdo.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 3000;
                $res->message = "유효하지 않은 아이디이거나 비밀번호가 맞지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 2) JWT 발급
            // Payload에 맞게 다시 설정 요함, 아래는 Payload에 userIdx를 넣기 위한 과정
            $userIdx = getUserIdxByID($req->id);  // JWTPdo.php 에 구현
            $jwt = getJWT($userIdx, JWT_SECRET_KEY); // function.php 에 구현


            $aa=array(jwt=>$jwt,userIdx=>$userIdx);
            $result=array($aa);

            $res->result=  $result;
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "로그인 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "check-login":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

//            if (empty($jwt)){
//                $res->isSuccess = FALSE;
//                $res->code = 2000;
//                $res->message = "토큰이 존재하지 않습니다..";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                addErrorLogs($errorLogs, $res, $req);
//                return;
//            }

            // 1) JWT 유효성 검사
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 3000;
                $res->message = "비로그인 상태입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "로그인 상태입니다.";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "kakao-login":

////            access-token 발급코드
////            POST방식 => GET방식으로 바꿀것
//
//            $returnCode = $_GET["code"];
//            $restAPIKey = "605d8f7b2c090006bfee372f7592a0f1";
//            $callbacURI = urlencode("https://test.junbo.shop/kakao-login"); // 본인의 Call Back URL을 입력해주세요
//// API 요청 URL
//            $returnUrl = "https://kauth.kakao.com/oauth/token?grant_type=authorization_code&client_id=".$restAPIKey."&redirect_uri=".$callbacURI."&code=".$returnCode;
//
//
//            $isPost = false;
//
//            $ch = curl_init();
//            curl_setopt($ch, CURLOPT_URL, $returnUrl);
//            curl_setopt($ch, CURLOPT_POST, $isPost);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//            $headers = array();
//            $loginResponse = curl_exec ($ch);
//            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//            curl_close ($ch);
//
//            $accessToken= json_decode($loginResponse)->access_token; // access token 가져옴
//
//            echo $accessToken;



            $accessToken= $req->TOKEN;

            //방식은 PHP에서 호출하는 curl_init()도 있고 밑에 방식으로도 가능하다는점 알려드리려고 두 개를 같이 썻습니다.
            $curl = 'curl -v -X GET https://kapi.kakao.com/v2/user/me -H "Authorization: Bearer '.$accessToken.'"';
            $info = shell_exec($curl);
            $info_arr = json_decode($info, true);

            $kakaoId = $info_arr["id"];

            if(empty($accessToken)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "토큰이 존재하지 않습니다. 헤더에 토큰을 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(empty($kakaoId)){
                $res->isSuccess = FALSE;
                $res->code = 3001;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $kakaoEmailId=$kakaoId.'@kakao.com';
            $pwd ='abcd1234!';
            $pwd_hash = password_hash($pwd, PASSWORD_DEFAULT); // Password Hash
            $nickname = '캔디';
            $phone = "#01012344321";
            $profileNum=mt_rand(1,4);


            if(!isValidUserId($kakaoEmailId)){
                createUser($kakaoEmailId, $pwd_hash, $nickname,$phone,$profileNum);
                $userIdx = getUserIdxByID($kakaoEmailId);

                $res->userIdx = $userIdx;
                $res->isSuccess = FALSE;
                $res->code = 3000;
                $res->message = "가입되지 않은 회원입니다. 닉네임 설정을 해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            // 2) JWT 발급
            // Payload에 맞게 다시 설정 요함, 아래는 Payload에 userIdx를 넣기 위한 과정
            $userIdx = getUserIdxByID($kakaoEmailId);  // JWTPdo.php 에 구현
            $jwt = getJWT($userIdx, JWT_SECRET_KEY); // function.php 에 구현

            $aa=array(jwt=>$jwt,userIdx=>$userIdx);
            $result=array($aa);

            $res->result=  $result;
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "로그인 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;



        case "patchMypage":

            $checkUserIdx=$vars['userIdx'];

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt, JWT_SECRET_KEY) ->userIdx;


            if(empty($jwt)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "토큰이 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }


            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 3000;
                $res->message = "유효하지 않은 토큰입니다";
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

            $nickname = $req->nickname;
            $pwd =$req->pwd;
            $pwd_hash = password_hash($pwd, PASSWORD_DEFAULT); // Password Hash
            $name = $req->name;
            $phone = $req->phone;

            #케이스
            if(empty($nickname) and empty($pwd) and empty($name) and empty($phone)){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "수정값이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            #닉네임 중복체크
            if(isValidNickname($nickname)){
                $res->isSuccess = FALSE;
                $res->code = 3001;
                $res->message = "중복된 닉네임입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            #패스워드 유효성 검사
            if (!empty($pwd) and !preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&^])[A-Za-z\d$@$!%*#?&^]{8,}$/',$pwd) ) {
                $res->isSuccess = FALSE;
                $res->code = 3002;
                $res->message = "비밀번호는 문자,숫자,특수문자 하나이상 8자이상-16자 이하입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }



            else if(!empty($nickname) and empty($pwd) and empty($name) and empty($phone)){
                $case=1;
            }
            else if(empty($nickname) and !empty($pwd) and empty($name) and empty($phone)){
                $case=2;
            }
            else if(empty($nickname) and empty($pwd) and !empty($name) and empty($phone)){
                $case=3;
            }
            else if(empty($nickname) and empty($pwd) and empty($name) and !empty($phone)){
                $case=4;
            }
            else if(!empty($nickname) and !empty($pwd) and empty($name) and empty($phone)){
                $case=5;
            }
            else if(!empty($nickname) and empty($pwd) and !empty($name) and empty($phone)){
                $case=6;
            }
            else if(!empty($nickname) and empty($pwd) and empty($name) and !empty($phone)){
                $case=7;
            }
            else if(empty($nickname) and !empty($pwd) and !empty($name) and empty($phone)){
                $case=8;
            }
            else if(empty($nickname) and !empty($pwd) and empty($name) and !empty($phone)){
                $case=9;
            }
            else if(empty($nickname) and empty($pwd) and !empty($name) and !empty($phone)){
                $case=10;
            }
            else if(!empty($nickname) and !empty($pwd) and !empty($name) and empty($phone)){
                $case=11;
            }
            else if(!empty($nickname) and !empty($pwd) and empty($name) and !empty($phone)){
                $case=12;
            }
            else if(!empty($nickname) and empty($pwd) and !empty($name) and !empty($phone)){
                $case=13;
            }
            else if(empty($nickname) and !empty($pwd) and !empty($name) and !empty($phone)){
                $case=14;
            }
            else if(!empty($nickname) and !empty($pwd) and !empty($name) and !empty($phone)){
                $case=15;
            }


            patchMypage($case,$nickname,$pwd_hash,$name,$phone,$userIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "해당 회원정보 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "kakao-sign-up":

            $userIdx=$vars['userIdx'];
            $nickname=$req->nickname;


            $pdo = pdoSqlConnect();

            #카카오 간편가입인지 체크
            $query = "select exists(select * from User where
id like concat('%','kakao.com','%') and idx=$userIdx and status='U') as exist;";

            $st = $pdo->prepare($query);
            //    $st->execute([$param,$param]);
            $st->execute([]);

            //    $st->execute();
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res1 = $st->fetchAll();

            if(!$res1[0]['exist']){
                $res->isSuccess = FALSE;
                $res->code = 3000;
                $res->message = "카카오 간편가입 회원이 아닙니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            #닉네임 중복체크
            if(empty($nickname)){
                $res->isSuccess = FALSE;
                $res->code = 3001;
                $res->message = "닉네임을 입력해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }



            #닉네임 중복체크
            if(isValidNickname($nickname)){
                $res->isSuccess = FALSE;
                $res->code = 3002;
                $res->message = "중복된 닉네임입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $query = "update User set nickname='$nickname' where idx=$userIdx;";


            $st = $pdo->prepare($query);
            //    $st->execute([$param,$param]);
            $st->execute([]);


            $jwt = getJWT($userIdx, JWT_SECRET_KEY); // function.php 에 구현

            $aa=array(jwt=>$jwt,userIdx=>$userIdx);
            $result=array($aa);

            $res->result=  $result;
            $res->isSuccess = FALSE;
            $res->code = 1000;
            $res->message = "카카오 간편가입 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            addErrorLogs($errorLogs, $res, $req);
            return;




        case "test":
            $res->userIdx = 13;
            $res->isSuccess = FALSE;
            $res->code = 3000;
            $res->message = "가입되지 않은 회원입니다. 해당 userIdx의 닉네임 설정을 해주세요";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            addErrorLogs($errorLogs, $res, $req);
            return;






    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
