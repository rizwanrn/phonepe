<?php
require 'includes/functions.php'; // this file includes my local variables, you can create yours like session
include 'phonepe-config.php'; // this file includes Phonepe credentials
$page = "Thank you!";
$paymentstts = "Pending";
$paymentmsg = "Record does not found, Try Again!";
$userIP = getUserIP();
$order_id = "";
$selectThisOrd = select("orders","user_ip='$userIP' ORDER BY id DESC LIMIT 1");
if (howMany($selectThisOrd)>0) {
  $fetchThisOrd = fetch($selectThisOrd);
  $transactionId = $fetchThisOrd['txn_mid'];
  $sha = "/pg/v1/status/".$merchantId."/".$transactionId.$saltPPkey;
  $sha = hash('sha256', $sha);
  // $postdata = json_encode(["request"=>$data]);
  $curl = curl_init();
  curl_setopt_array($curl, [
    CURLOPT_URL => "https://api-preprod.phonepe.com/apis/hermes/pg/v1/status/".$merchantId."/".$transactionId,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
      "Accept: application/json",
      "Content-Type: application/json",
      "X-VERIFY: ".$sha."###".$keyIndex,
      "X-MERCHANT-ID: ".$merchantId,
    ],
  ]);

  $response = curl_exec($curl);
  $err = curl_error($curl);
  curl_close($curl);
  if ($err) {
     $paymentmsg = "cURL Error #:" . $err;
  } else {

    $res = json_decode($response);
    if ($res->success==true) {
      $paymentstts = $res->data->state;
      // $paymentstts = "Success.";
      $paymentmsg = $res->message;
      if ($res->code=="PAYMENT_SUCCESS") {
        update("tbl_payment",["status" => "Success"],"order_id='".$fetchThisOrd['id']."'");
        $order_id = $fetchThisOrd['order_id'];
      }else{
        $page = "Try Again!";
        deleteRow("tbl_payment","order_id='".$fetchThisOrd['id']."'");
        deleteRow("orderitems","orderid='".$fetchThisOrd['id']."'");
        deleteRow("orders","id='".$fetchThisOrd['id']."'");
        $paymentmsg = $res->message." If deducted from your account then request for Refund or Contact us for more details.";
        /*{"success":true,"code":"PAYMENT_PENDING","message":"Your request is in pending state.","data":{"merchantId":"PGTESTPAYUAT","merchantTransactionId":"rizwan12456txn127","transactionId":null,"amount":52000,"state":"PENDING","responseCode":"PAYMENT_PENDING","paymentInstrument":null}}*/
      }
    }else{
        $paymentstts = $res->data->state;
        $page = "Try Again!";
      deleteRow("tbl_payment","order_id='".$fetchThisOrd['id']."'");
      deleteRow("orderitems","orderid='".$fetchThisOrd['id']."'");
      deleteRow("orders","id='".$fetchThisOrd['id']."'");
      $paymentmsg = $res->message." If deducted from your account then request for Refund or Contact us for more details.";
      /*{
          "success":false,
          "code":"PAYMENT_ERROR",
          "message":"Payment Failed",
          "data":{
            "merchantId":"PGTESTPAYUAT",
            "merchantTransactionId":"rizwan12456txn123",
            "transactionId":null,
            "amount":52000,
            "state":"FAILED",
            "responseCode":"PAYMENT_ERROR",
            "paymentInstrument":null
          }
        }*/
    }
  }
}?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Place favicon.ico in the root directory -->
    <?php include 'includes/style.php';?>

</head>
   <body>
    
    <div class="wrapper">
        <!--Header Area Start-->
    <?php include 'includes/header.php';?>
        <!--Header Area End-->
        <!--Page Banner2 Area Start-->
        <div class="page-banner2-area">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="page-banner2-title">
                            <h2><?=$page?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--Page Banner Area End-->
        <!--Breadcrumb Start-->
        <div class="breadcrumb-Area">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="breadcrumb-content">
                            <ul>
                                <li><a href="index.php">Home</a></li>
                                <li class="active"><a href="#"><?=$page?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--Breadcrumb End-->
        <div class="jumbotron text-xs-center">
          <h1 class="display-3"><?=$page?></h1>
          <h4 class="display-3"><?=$paymentstts?></h4>
          <p class="lead"><?=$paymentmsg?></p>
            <hr>
          <p class="lead"><?php echo (!empty($order_id)) ? 'Your Order id #'.$order_id:'';?></p>
            <h4>You will be redirecting in 1 min.</h4>
          <p class="lead">
            <a class="btn btn-primary btn-sm" href="index.php" role="button">Continue Shopping</a>
          </p>
        </div>
      <?php include 'includes/footer.php';
      include 'includes/script.php';?>
      <script>
        $(function(){
            $("#cartData").attr('action', 'index.php');
            setTimeout(function(){
            $("#checkout-my-cart").click(); }, 60000);
        });
    </script>
  </body>
</html>
