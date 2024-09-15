<?php
require_once("./db_mahjong.php");
session_start();

// 檢查是否正常進入此頁
if (!isset($_POST["coupon_id"])) {
  echo "請循正常管道進入此頁";
  exit;
}

// 接收表單數據
$coupon_id = $_POST["coupon_id"];
$name = $_POST["name"];
$discountCode = $_POST["discountCode"];
$discountType = $_POST["discountType"];
$percentDiscountValue = $_POST["percentDiscountValue"];
$cashDiscountValue = $_POST["cashDiscountValue"];
$validFrom = $_POST["validFrom"];
$validTo = $_POST["validTo"];
$limitValue = $_POST["limitValue"];
$usageLimit = $_POST["usageLimit"];
$status = $_POST["status"];

// 保存折扣類型到 session
$_SESSION["discountType"] = $discountType;

// 檢查優惠券代碼是否已存在
$sqlCheckCoupon = "SELECT * FROM coupons WHERE discount_code = '$discountCode' AND coupon_id != '$coupon_id'";
$resultCheck = $conn->query($sqlCheckCoupon);

if ($resultCheck->num_rows > 0) {
  $_SESSION["discountCode_error"] = "此優惠券已被生成。";
  $_SESSION["discountCode_class"] = "is-invalid";
  header("location:edit-coupon.php?coupon_id=" . $coupon_id);
  exit();
}

// 驗證表單數據
$validationErrors = [];
if (empty($name)) {
  $validationErrors["name_error"] = "請輸入優惠劵名稱。";
  $_SESSION["name_class"] = "is-invalid";
  $_SESSION["name"] = "";
} else {
  $_SESSION["name"] = $name;
  $_SESSION["name_class"] = "is-valid";
}

if (empty($discountCode)) {
  $validationErrors["discountCode_error"] = "請輸入優惠劵代碼。";
  $_SESSION["discountCode_class"] = "is-invalid";
  $_SESSION["discountCode"] = "";
} else {
  $_SESSION["discountCode"] = $discountCode;
  $_SESSION["discountCode_class"] = "is-valid";
}

$_SESSION["discountType_text_color"] = "text-success";

if ($discountType == "percent") {
  if (empty($percentDiscountValue) || $percentDiscountValue <= 0) {
    $validationErrors["percentDiscountValue_error"] = "請輸入有效的優惠劵折扣值。";
    $_SESSION["percentDiscountValue_class"] = "is-invalid";
    $_SESSION["percentDiscountValue"] = "";
  } else {
    $_SESSION["percentDiscountValue"] = $percentDiscountValue;
    $_SESSION["percentDiscountValue_class"] = "is-valid";
  }
} else if ($discountType == "cash") {
  if (empty($cashDiscountValue) || $cashDiscountValue <= 0) {
    $validationErrors["cashDiscountValue_error"] = "請輸入有效的優惠劵折扣值。";
    $_SESSION["cashDiscountValue_class"] = "is-invalid";
    $_SESSION["cashDiscountValue"] = "";
  } else {
    $_SESSION["cashDiscountValue"] = $cashDiscountValue;
    $_SESSION["cashDiscountValue_class"] = "is-valid";
  }
}

if (empty($validFrom)) {
  $validationErrors["validFrom_error"] = "請選擇優惠劵有效起始日。";
  $_SESSION["validFrom_class"] = "is-invalid";
  $_SESSION["validFrom"] = "";
} else {
  $_SESSION["validFrom"] = $validFrom;
  $_SESSION["validFrom_class"] = "is-valid";
}

if (empty($validTo)) {
  $validationErrors["validTo_error"] = "請選擇優惠劵有效截止日。";
  $_SESSION["validTo_class"] = "is-invalid";
  $_SESSION["validTo"] = "";
} else {
  $_SESSION["validTo"] = $validTo;
  $_SESSION["validTo_class"] = "is-valid";
}

if (empty($limitValue) || $limitValue <= 0) {
  $validationErrors["limitValue_error"] = "請輸入有效的使用優惠劵限制金額。";
  $_SESSION["limitValue_class"] = "is-invalid";
  $_SESSION["limitValue"] = "";
} else {
  $_SESSION["limitValue"] = $limitValue;
  $_SESSION["limitValue_class"] = "is-valid";
}

if (empty($usageLimit) || $usageLimit <= 0) {
  $validationErrors["usageLimit_error"] = "請輸入有效的優惠劵可使用次數。";
  $_SESSION["usageLimit_class"] = "is-invalid";
  $_SESSION["usageLimit"] = "";
} else {
  $_SESSION["usageLimit"] = $usageLimit;
  $_SESSION["usageLimit_class"] = "is-valid";
}

if ($status == 'active') {
  $_SESSION["status_text_color"] = "text-success";
} elseif ($status == 'inactive') {
  $_SESSION["status_text_color"] = "text-success";
}

// 如果有驗證錯誤，重定向回編輯頁面
if (!empty($validationErrors)) {
  $_SESSION = array_merge($_SESSION, $validationErrors);
  header("location:edit-coupon.php?coupon_id=" . $coupon_id);
  exit();
}

// 更新資料到資料庫
$now = date("Y-m-d H:i:s");
$discountValue = ($discountType == "percent") ? $percentDiscountValue : $cashDiscountValue;
$sql = "UPDATE coupons SET 
        name='$name',
        discount_code='$discountCode',
        discount_type='$discountType',
        discount_value='$discountValue',
        valid_from='$validFrom',
        valid_to='$validTo',
        limit_value='$limitValue',
        usage_limit='$usageLimit',
        status='$status',
        update_at='$now' 
        WHERE coupon_id=$coupon_id";

if ($conn->query($sql) === TRUE) {
  $_SESSION["successMsg"] = "# " . $coupon_id . " " . "修改成功";
  header("location:coupon-list.php?p=1&o=1");
  exit();
} else {
  $_SESSION["errorMsg"] = "更新資料錯誤: " . $conn->error;
  header("location:edit-coupon.php?coupon_id=" . $coupon_id);
  exit();
}

$conn->close();
