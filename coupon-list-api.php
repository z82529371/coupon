<?php
require_once("../db_mahjong.php");
if (!isset($_POST["coupon_id"])) {
  $data = [
    "status" => "0",
    "message" => "請輸入coupon_id"
  ];

  echo json_encode($data);
  exit;
}

$coupon_id = $_POST["coupon_id"];

// $_SESSION["coupon_id"] = $coupon_id;

$data = [
  "status" => "1",
  "coupon_id" => $coupon_id
];
echo json_encode($data);
