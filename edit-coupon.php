<?php
require_once("../db_mahjong.php");
session_start();

// 檢查是否正常進入此頁
if (!isset($_GET["coupon_id"])) {
  echo "請循正常管道進入此頁";
  exit;
}

$coupon_id = $_GET["coupon_id"];

// 查詢優惠劵資料
$sql = "SELECT * FROM coupons WHERE coupon_id = $coupon_id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

// 設定頁面標題
$title = $result->num_rows > 0 ? $row["coupon_id"] : "優惠劵不存在";
?>

<!doctype html>
<html lang="en">

<head>
  <title><?= $title ?></title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <?php include("coupon-css.php") ?>
</head>

<body>
  <div class="container">
    <div class="text-center">
      <h1 class="py-2 fw-semibold">編輯優惠劵</h1>
    </div>
    <div class="row justify-content-center position-relative">
      <div class="return py-3 position-absolute">
        <a class="btn btn-primary fw-semibold" href="coupon-list.php?p=1&o=1">
          <i class="fa-solid fa-arrow-left"></i> 回優惠劵列表
        </a>
      </div>
      <div class="row justify-content-center align-items-center">
        <div class="col-6">
          <?php if ($result->num_rows > 0) : ?>
            <form id="couponForm" action="doEditCoupon.php" method="post">
              <table class="table table-light table-bordered">
                <thead class="table-primary">
                  <tr>
                    <th class="col-3 text-center">項目</th>
                    <td class="col-9 fw-semibold text-center">編輯說明</td>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <input type="hidden" name="coupon_id" value="<?= $row["coupon_id"] ?>">
                    <th class="text-center">優惠劵編號</th>
                    <td class="fw-semibold text-center"><?= $row["coupon_id"] ?></td>
                  </tr>
                  <tr>
                    <th class="py-3 text-center">優惠劵名稱</th>
                    <td>
                      <input type="text" class="form-control <?= $_SESSION["name_class"] ?? '' ?>" id="name" name="name" value="<?= $row["name"] ?>">
                      <?php if (isset($_SESSION["name_error"])) : ?>
                        <div class="text-danger text-error pt-2"><?= $_SESSION["name_error"]; ?></div>
                        <?php unset($_SESSION["name_error"], $_SESSION["name_class"]); ?>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <th class="py-3 text-center">優惠劵折扣碼</th>
                    <td>
                      <div class="input-group">
                        <input type="text" class="form-control <?= $_SESSION["discountCode_class"] ?? '' ?>" name="discountCode" value="<?= $row["discount_code"] ?>" id="discountCode">
                        <button type="button" class="btn btn-primary fw-semibold" id="randomCode">生成隨機代碼</button>
                      </div>
                      <?php if (isset($_SESSION["discountCode_error"])) : ?>
                        <div class="text-danger text-error pt-2"><?= $_SESSION["discountCode_error"]; ?></div>
                        <?php unset($_SESSION["discountCode_error"], $_SESSION["discountCode_class"]); ?>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <th class="py-2 text-center">優惠劵折扣類型</th>
                    <td>
                      <div class="row">
                        <div class="col-6">
                          <div class="form-check">
                            <input class="form-check-input" type="radio" name="discountType" id="cashDiscount" value="cash" <?= $row["discount_type"] == 'cash' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="cashDiscount">
                              <span class="fw-semibold <?= $_SESSION["discountType_text_color"] ?? '' ?>">金額折扣</span>
                            </label>
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="form-check">
                            <input class="form-check-input" type="radio" id="percentDiscount" name="discountType" value="percent" <?= $row["discount_type"] == 'percent' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="percentDiscount">
                              <span class="fw-semibold <?= $_SESSION["discountType_text_color"] ?? '' ?>">百分比折扣</span>
                            </label>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <th class="col-3 py-3 text-center">優惠劵折扣值</th>
                    <td class="col-9">
                      <div id="cashDiscountValueDiv" <?= $row["discount_type"] !== 'cash' ? 'style="display: none;"' : '' ?>>
                        <div class="input-group">
                          <div class="input-group-text">$</div>
                          <input type="number" class="form-control <?= $_SESSION["cashDiscountValue_class"] ?? '' ?>" id="cashDiscountValue" name="cashDiscountValue" value="<?= $row["discount_value"] ?>">
                        </div>
                        <?php if (isset($_SESSION["cashDiscountValue_error"])) : ?>
                          <div class="text-danger text-error pt-2"><?= $_SESSION["cashDiscountValue_error"]; ?></div>
                          <?php unset($_SESSION["cashDiscountValue_error"], $_SESSION["cashDiscountValue_class"]); ?>
                        <?php endif; ?>
                      </div>
                      <div id="percentDiscountValueDiv" <?= $row["discount_type"] !== 'percent' ? 'style="display: none;"' : '' ?>>
                        <div class="input-group">
                          <input type="number" value="<?= $row["discount_value"] ?>" min="0" max="100" class="form-control <?= $_SESSION["percentDiscountValue_class"] ?? '' ?>" name="percentDiscountValue">
                          <div class="input-group-text">%</div>
                        </div>
                        <?php if (isset($_SESSION["percentDiscountValue_error"])) : ?>
                          <div class="text-danger text-error pt-2"><?= $_SESSION["percentDiscountValue_error"]; ?></div>
                          <?php unset($_SESSION["percentDiscountValue_error"], $_SESSION["percentDiscountValue_class"]); ?>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <th class="py-4 text-center">優惠劵有效期</th>
                    <td>
                      <div class="row">
                        <div class="col-6">
                          <label for="validFrom" class="form-label fw-semibold">有效起始日：</label>
                          <input type="date" class="form-control <?= $_SESSION["validFrom_class"] ?? '' ?>" name="validFrom" id="validFrom" value="<?= $row["valid_from"] ?>">
                          <?php if (isset($_SESSION["validFrom_error"])) : ?>
                            <div class="text-danger text-error pt-2"><?= $_SESSION["validFrom_error"]; ?></div>
                            <?php unset($_SESSION["validFrom_error"], $_SESSION["validFrom_class"]); ?>
                          <?php endif; ?>
                        </div>
                        <div class="col-6">
                          <label for="validTo" class="form-label fw-semibold">有效截止日：</label>
                          <input type="date" class="form-control <?= $_SESSION["validTo_class"] ?? '' ?>" name="validTo" id="validTo" value="<?= $row["valid_to"] ?>">
                          <?php if (isset($_SESSION["validTo_error"])) : ?>
                            <div class="text-danger text-error pt-2"><?= $_SESSION["validTo_error"]; ?></div>
                            <?php unset($_SESSION["validTo_error"], $_SESSION["validTo_class"]); ?>
                          <?php endif; ?>
                        </div>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <th class="py-3 text-center">使用最低消費金額</th>
                    <td>
                      <div class="input-group">
                        <div class="input-group-text">$</div>
                        <input type="number" class="form-control <?= $_SESSION["limitValue_class"] ?? '' ?>" name="limitValue" value="<?= $row["limit_value"] ?>">
                      </div>
                      <?php if (isset($_SESSION["limitValue_error"])) : ?>
                        <div class="text-danger text-error pt-2"><?= $_SESSION["limitValue_error"]; ?></div>
                        <?php unset($_SESSION["limitValue_error"], $_SESSION["limitValue_class"]); ?>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <th class="py-3 text-center">可使用次數</th>
                    <td>
                      <input type="number" class="form-control <?= $_SESSION["usageLimit_class"] ?? '' ?>" name="usageLimit" value="<?= $row["usage_limit"] ?>">
                      <?php if (isset($_SESSION["usageLimit_error"])) : ?>
                        <div class="text-danger text-error pt-2"><?= $_SESSION["usageLimit_error"]; ?></div>
                        <?php unset($_SESSION["usageLimit_error"], $_SESSION["usageLimit_class"]); ?>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <th class="py-2 text-center">狀態</th>
                    <td>
                      <div class="row">
                        <div class="col-6">
                          <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="statusActive" value="active" <?= $row["status"] == 'active' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="statusActive">
                              <span class="fw-semibold <?= $_SESSION["status_text_color"] ?? '' ?>">可使用</span>
                            </label>
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="form-check">
                            <input class="form-check-input" type="radio" id="statusInactive" name="status" value="inactive" <?= $row["status"] == 'inactive' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="statusInactive">
                              <span class="fw-semibold <?= $_SESSION["status_text_color"] ?? '' ?>">已停用</span>
                            </label>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div class="d-flex justify-content-center gap-3 my-4">
                <button class="btn btn-primary fw-semibold" type="submit"><i class="fa-solid fa-pen-to-square"></i> 確認編輯</button>
                <a href="coupon-list.php?page=1&order=1" class="btn btn-danger fw-semibold" role="button"><i class="fa-solid fa-trash"></i> 取消編輯</a>
              </div>
            </form>
          <?php else : ?>
            <h1>優惠劵不存在</h1>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    // 生成包含大寫字母和數字的10碼隨機折扣碼
    document.getElementById("randomCode").addEventListener("click", function() {
      var characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
      var code = '';
      for (var i = 0; i < 10; i++) {
        var randomIndex = Math.floor(Math.random() * characters.length);
        code += characters[randomIndex];
      }
      document.getElementById("discountCode").value = code;
    });

    // 根據用戶選擇的折扣類型顯示相應的輸入框並清空折扣值
    document.querySelectorAll('input[name="discountType"]').forEach(function(elem) {
      elem.addEventListener("click", function() {
        if (elem.value == "percent") {
          document.getElementById("cashDiscountValueDiv").style.display = "none";
          document.getElementById("percentDiscountValueDiv").style.display = "block";
          document.getElementById("cashDiscountValue").value = "";
        } else if (elem.value == "cash") {
          document.getElementById("cashDiscountValueDiv").style.display = "block";
          document.getElementById("percentDiscountValueDiv").style.display = "none";
          document.querySelector('input[name="percentDiscountValue"]').value = "";
        }
      });
    });

    // 頁面加載時顯示預設輸入框並清空折扣值
    window.onload = function() {
      var discountType = "<?= $_SESSION['discountType'] ?? $row["discount_type"] ?>";
      if (discountType == "cash") {
        document.getElementById("cashDiscountValueDiv").style.display = "block";
        document.getElementById("percentDiscountValueDiv").style.display = "none";
        document.querySelector('input[name="percentDiscountValue"]').value = "";
        document.getElementById("cashDiscount").checked = true;
      } else if (discountType == "percent") {
        document.getElementById("cashDiscountValueDiv").style.display = "none";
        document.getElementById("percentDiscountValueDiv").style.display = "block";
        document.getElementById("cashDiscountValue").value = "";
        document.getElementById("percentDiscount").checked = true;
      }
    }

    // 檢查截止日期是否小於今天，並禁用"可使用"選項
    document.addEventListener("DOMContentLoaded", function() {
      var validToElem = document.getElementById("validTo");
      var validFromElem = document.getElementById("validFrom");
      var statusActive = document.getElementById("statusActive");
      var statusInactive = document.getElementById("statusInactive");

      function checkValidToDate() {
        if (validToElem) {
          var validTo = new Date(validToElem.value);
          var today = new Date();
          today.setHours(0, 0, 0, 0);
          if (validTo < today) {
            if (statusActive) statusActive.disabled = true;
            if (statusInactive) statusInactive.checked = true;
          } else {
            if (statusActive) statusActive.disabled = false;
            if (statusInactive && !statusInactive.checked) statusActive.checked = true;
          }
        }
      }
      if (validToElem) {
        validToElem.addEventListener("change", checkValidToDate);
        checkValidToDate();
      }
      if (validFromElem && validToElem) {
        validFromElem.addEventListener("change", function() {
          validToElem.min = validFromElem.value;
        });
        validToElem.addEventListener("change", function() {
          validFromElem.max = validToElem.value;
        });
      }
    });
  </script>

  <?php include("coupon-js.php") ?>
</body>

</html>