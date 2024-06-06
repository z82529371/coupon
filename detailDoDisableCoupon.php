<?php
require_once("../db_mahjong.php");
session_start();

$couponId = intval($_GET['coupon_id']); // 防止 SQL 注入

$Sql = "UPDATE coupons SET status = 'inactive' WHERE coupon_id = $couponId";

if ($conn->query($Sql) === TRUE) {
  $_SESSION["successMsg"] = "# " . $couponId . " " . "停用成功";
  header("location:coupon-list.php?p=1&o=1");
  exit();
} else {
  $_SESSION["errorMsg"] = "更新資料錯誤: " . $conn->error;
  header("location:coupon-detail.php?coupon_id=" . $couponId);
  exit();
}
