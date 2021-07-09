<?php

header('Content-Type: json');

require_once('./autoload.php');

spl_autoload_register('BootpayAutoload');



use Bootpay\Rest\BootpayApi;

$receiptId=$_GET['receiptId'];



$bootpay = BootpayApi::setConfig(

    '5f9facf118e1ae002e4f468a',

    'rXcqOwW+4KAKWHRn4BBEi28SbCuByBP+KucsVjV8Vxc='

);

//var_dump($bootpay);


$response = $bootpay->requestAccessToken();


// var_dump($response);



// Token이 발행되면 그 이후에 verify 처리 한다.

if ($response->status === 200) {



    $token = $response->data->token; //ACCES TOKEN KEY

// echo $token;

}



//

//

//





// Token이 발행되면 그 이후에 verify 처리 한다.

if ($response->status === 200) {

    $result = $bootpay->verify($receiptId);

//    var_dump($result);


// 그리고 결제 상태가 완료 상태인가?

    if ($result->data->status === 1) {

// TODO: 이곳이 상품 지급 혹은 결제 완료 처리를 하는 로직으로 사용하면 됩니다.

        $res = (object)array();

        $aa=array(acmName=>$result->data->name,payAmount=>$result->data->price);
        $resultArray=array($aa);

        $res->result =$resultArray;
        $res->isSuccess = TRUE;
        $res->code = 1000;
        $res->message = "결제성공";
        echo json_encode($res, JSON_NUMERIC_CHECK);
    }
    else{

        $res = (object)array();
        $res->isSuccess = FALSE;
        $res->code = 2000;
        $res->message = "결제에 실패했습니다";
        echo json_encode($res, JSON_NUMERIC_CHECK);
    }

}






