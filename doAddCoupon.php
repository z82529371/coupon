<?php
require_once("../db_mahjong.php");
session_start();

// 檢查是否正常進入此頁
if (!isset($_POST["name"])) {
  echo "請循正常管道進入此頁";
  exit;
}

// 接收表單數據
$name = $_POST["name"];
$discountCode = $_POST["discountCode"];
$discountType = $_POST["discountType"];
$percentDiscountValue = $_POST["percentDiscountValue"];
$cashDiscountValue = $_POST["cashDiscountValue"];
$validFrom = $_POST["validFrom"];
$validTo = $_POST["validTo"];
$limitValue = $_POST["limitValue"];
$usageLimit = $_POST["usageLimit"];

// 保存折扣類型到 session
$_SESSION["discountType"] = $discountType;

// 檢查優惠券代碼是否已存在
$sqlCheckCoupon = "SELECT * FROM coupons WHERE discount_code = '$discountCode'";
$resultCheck = $conn->query($sqlCheckCoupon);

if ($resultCheck->num_rows > 0) {
  $_SESSION["discountCode_error"] = "此優惠券已被生成。";
  $_SESSION["discountCode_class"] = "is-invalid";
  header("location:add-Coupon.php");
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
  if (empty($percentDiscountValue)) {
    $validationErrors["percentDiscountValue_error"] = "請輸入優惠劵折扣值。";
    $_SESSION["percentDiscountValue_class"] = "is-invalid";
    $_SESSION["percentDiscountValue"] = "";
  } else {
    $_SESSION["percentDiscountValue"] = $percentDiscountValue;
    $_SESSION["percentDiscountValue_class"] = "is-valid";
  }
} else if ($discountType == "cash") {
  if (empty($cashDiscountValue)) {
    $validationErrors["cashDiscountValue_error"] = "請輸入優惠劵折扣值。";
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

if (empty($limitValue)) {
  $validationErrors["limitValue_error"] = "請輸入使度優惠劵限制金額。";
  $_SESSION["limitValue_class"] = "is-invalid";
  $_SESSION["limitValue"] = "";
} else {
  $_SESSION["limitValue"] = $limitValue;
  $_SESSION["limitValue_class"] = "is-valid";
}

if (empty($usageLimit)) {
  $validationErrors["usageLimit_error"] = "請輸入優惠劵可使用次數。";
  $_SESSION["usageLimit_class"] = "is-invalid";
  $_SESSION["usageLimit"] = "";
} else {
  $_SESSION["usageLimit"] = $usageLimit;
  $_SESSION["usageLimit_class"] = "is-valid";
}

// 如果有驗證錯誤，重定向回添加頁面
if (!empty($validationErrors)) {
  $_SESSION = array_merge($_SESSION, $validationErrors);
  header("location:add-Coupon.php");
  exit();
}

// 插入數據到資料庫
$now = date("Y-m-d H:i:s");
$discountValue = $discountType == "percent" ? $percentDiscountValue : $cashDiscountValue;
$sql = "INSERT INTO coupons (name, discount_code, discount_type, discount_value, valid_from, valid_to, limit_value, usage_limit, status, created_at) 
        VALUES ('$name', '$discountCode', '$discountType', '$discountValue', '$validFrom', '$validTo', '$limitValue', '$usageLimit', 'active', '$now')";

if ($conn->query($sql) === TRUE) {
  $newCouponId = $conn->insert_id; // 獲取新增記錄的ID
  $_SESSION["successMsg"] = "# " . $newCouponId . " " . "優惠劵新增成功";
  header("location:coupon-list.php??p=1&o=1");
  exit();
} else {
  $_SESSION["errorMsg"] = "優惠劵新增失敗: " . $conn->error;
  header("location:add-Coupon.php");
  exit();
}
