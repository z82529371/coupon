<?php
require_once("./db_mahjong.php");
session_start();

$couponId = intval($_GET['coupon_id']); // 防止 SQL 注入
$response = [];

$Sql = "UPDATE coupons SET status = 'inactive' WHERE coupon_id = $couponId";

if ($conn->query($Sql) === TRUE) {
  $response['operation_result'] = "success";
} else {
  $response['operation_result'] = "error";
}

echo json_encode($response);
