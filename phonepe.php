<?php
$curl = curl_init();
  // Credentials to change
  $grandTotalPrice = 100; // in rupees
  $merchantId = "PGTESTPAYUAT";
  $merchantUserId = "MUID123";
  $merchantTransactionId = $merchantId.rand();

  $data = [
    "merchantId"=> $merchantId,
    "merchantTransactionId"=> $merchantTransactionId,
    "merchantUserId"=> $merchantUserId,
    "amount"=> (int)$grandTotalPrice*100,
    "redirectUrl"=> "https://yourdomain.com/redirect-url",
    "redirectMode"=> "POST",
    "callbackUrl"=> "https://yourdomain.com/redirecting-page-after-success-or-failed.php",
    "mobileNumber"=> "9999999999",
    "paymentInstrument"=> [
      "type"=> "PAY_PAGE"
    ]
  ];
  $data = base64_encode(json_encode($data));
  $sha = $data."/pg/v1/pay"."099eb0cd-02cf-4e2a-8aca-3e6c6aff0399";
  $sha = hash('sha256', $sha); //conerting the data to SHA
  $postdata = json_encode(["request"=>$data]);
  curl_setopt_array($curl, [
    CURLOPT_URL => "https://api-preprod.phonepe.com/apis/hermes/pg/v1/pay",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_POSTFIELDS => $postdata,
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_HTTPHEADER => [
      "Accept: application/json",
      "Content-Type: application/json",
      "X-VERIFY: ".$sha."###1",
      "X-CALLBACK-URL: https://clothingnonshop.com/phonepe-thankyou.php"
    ],
  ]);
  // https://mercury-uat.phonepe.com/v4/debit

  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);
  if ($err) {
    echo "cURL Error #:" . $err;
  } else {
    $res = json_decode($response);
    // var_dump($res)."<br>";
    if ($res->success==true) {
      $msg = ($res->message).$res->data->instrumentResponse->redirectInfo->url;
      header("location:".$res->data->instrumentResponse->redirectInfo->url);
    }
  }
