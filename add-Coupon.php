<?php
require_once("./db_mahjong.php");
session_start();
?>
<!doctype html>
<html lang="en">

<head>
  <title>新增優惠劵</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <?php include("coupon-css.php") ?>
</head>

<body>
  <div class="container">
    <div class="text-center">
      <h1 class="py-2 fw-semibold">新增優惠劵</h1>
    </div>
    <div class="row justify-content-center position-relative mb-4">
      <div class="return py-3 position-absolute">
        <a class="btn btn-primary fw-semibold" href="coupon-list.php?page=1&order=1"><i class="fa-solid fa-arrow-left"></i> 回優惠劵列表</a>
      </div>

      <div class="col-6 border border-2 border-primary-subtle rounded bg-light">
        <form action="doAddCoupon.php" method="post">
          <!-- 優惠劵名稱 -->
          <div class="py-3">
            <label for="name" class="form-label fw-semibold">優惠劵名稱：</label>
            <input type="text" class="form-control <?= $_SESSION["name_class"] ?? '' ?>" id="name" name="name" value="<?= $_SESSION["name"] ?? '' ?>" />
            <?php if (isset($_SESSION["name_error"])) : ?>
              <div class="text-danger text-error pt-2"><?= $_SESSION["name_error"]; ?></div>
              <?php unset($_SESSION["name_error"], $_SESSION["name_class"]); ?>
            <?php endif; ?>
          </div>

          <!-- 優惠劵折扣碼 -->
          <div class="mb-3">
            <label for="discountCode" class="form-label fw-semibold">優惠劵折扣碼：</label>
            <div class="input-group">
              <input type="text" class="form-control <?= $_SESSION["discountCode_class"] ?? '' ?>" id="discountCode" name="discountCode" value="<?= $_SESSION["discountCode"] ?? '' ?>" />
              <button type="button" class="btn btn-primary fw-semibold" id="randomCode">生成隨機代碼</button>
            </div>
            <?php if (isset($_SESSION["discountCode_error"])) : ?>
              <div class="text-danger text-error pt-2"><?= $_SESSION["discountCode_error"]; ?></div>
              <?php unset($_SESSION["discountCode_error"], $_SESSION["discountCode_class"]); ?>
            <?php endif; ?>
          </div>

          <!-- 優惠劵折扣類型 -->
          <div class="mb-3">
            <label class="form-label fw-semibold">優惠劵折扣類型：</label>
            <div class="row">
              <div class="col-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="discountType" id="cashDiscount" value="cash" checked>
                  <label class="form-check-label" for="cashDiscount"><span class="fw-semibold <?= $_SESSION["discountType_text_color"] ?? ''; ?>">金額折扣</span></label>
                </div>
              </div>
              <div class="col-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" id="percentDiscount" name="discountType" value="percent">
                  <label class="form-check-label" for="percentDiscount"><span class="fw-semibold <?= $_SESSION["discountType_text_color"] ?? ''; ?>">%數折扣</span></label>
                </div>
              </div>
            </div>
          </div>

          <!-- 優惠劵折扣金額 -->
          <div class="mb-3" id="cashDiscountValueDiv">
            <label for="cashDiscountValue" class="form-label fw-semibold">優惠劵折扣金額：</label>
            <div class="input-group">
              <div class="input-group-text">$</div>
              <input type="number" class="form-control <?= $_SESSION["cashDiscountValue_class"] ?? ''; ?>" id="cashDiscountValue" name="cashDiscountValue" value="<?= $_SESSION["cashDiscountValue"] ?? ''; ?>">
            </div>
            <?php if (isset($_SESSION["cashDiscountValue_error"])) : ?>
              <div class="text-danger text-error pt-2"><?= $_SESSION["cashDiscountValue_error"]; ?></div>
              <?php unset($_SESSION["cashDiscountValue_error"], $_SESSION["cashDiscountValue_class"]); ?>
            <?php endif; ?>
          </div>

          <!-- 優惠劵折扣百分比 -->
          <div class="mb-3" id="percentDiscountValueDiv" style="display:none;">
            <label for="percentDiscountValue" class="form-label fw-semibold">優惠劵折扣百分比：</label>
            <div class="input-group">
              <input type="number" min="0" max="100" class="form-control <?= $_SESSION["percentDiscountValue_class"] ?? ''; ?>" id="percentDiscountValue" name="percentDiscountValue" value="<?= $_SESSION["percentDiscountValue"] ?? ''; ?>">
              <div class="input-group-text">%</div>
            </div>
            <?php if (isset($_SESSION["percentDiscountValue_error"])) : ?>
              <div class="text-danger text-error pt-2"><?= $_SESSION["percentDiscountValue_error"]; ?></div>
              <?php unset($_SESSION["percentDiscountValue_error"], $_SESSION["percentDiscountValue_class"]); ?>
            <?php endif; ?>
          </div>

          <!-- 有效日期 -->
          <div class="mb-3 row">
            <div class="col-6">
              <label for="validFrom" class="form-label fw-semibold">有效起始日：</label>
              <input type="date" class="form-control <?= $_SESSION["validFrom_class"] ?? ''; ?>" name="validFrom" id="validFrom" value="<?= $_SESSION["validFrom"] ?? ''; ?>">
              <?php if (isset($_SESSION["validFrom_error"])) : ?>
                <div class="text-danger text-error pt-2"><?= $_SESSION["validFrom_error"]; ?></div>
                <?php unset($_SESSION["validFrom_error"], $_SESSION["validFrom_class"]); ?>
              <?php endif; ?>
            </div>
            <div class="col-6">
              <label for="validTo" class="form-label fw-semibold">有效截止日：</label>
              <input type="date" class="form-control <?= $_SESSION["validTo_class"] ?? ''; ?>" name="validTo" id="validTo" value="<?= $_SESSION["validTo"] ?? ''; ?>">
              <?php if (isset($_SESSION["validTo_error"])) : ?>
                <div class="text-danger text-error pt-2"><?= $_SESSION["validTo_error"]; ?></div>
                <?php unset($_SESSION["validTo_error"], $_SESSION["validTo_class"]); ?>
              <?php endif; ?>
            </div>
          </div>

          <!-- 使用最低消費金額 -->
          <div class="mb-3">
            <label for="limitValue" class="form-label fw-semibold">使用最低消費金額：</label>
            <div class="input-group">
              <div class="input-group-text">$</div>
              <input type="text" class="form-control <?= $_SESSION["limitValue_class"] ?? ''; ?>" name="limitValue" id="limitValue" value="<?= $_SESSION["limitValue"] ?? ''; ?>">
            </div>
            <?php if (isset($_SESSION["limitValue_error"])) : ?>
              <div class="text-danger text-error pt-2"><?= $_SESSION["limitValue_error"]; ?></div>
              <?php unset($_SESSION["limitValue_error"], $_SESSION["limitValue_class"]); ?>
            <?php endif; ?>
          </div>

          <!-- 可使用次數 -->
          <div class="mb-3">
            <label for="usageLimit" class="form-label fw-semibold">可使用次數：</label>
            <input type="text" class="form-control <?= $_SESSION["usageLimit_class"] ?? ''; ?>" name="usageLimit" id="usageLimit" value="<?= $_SESSION["usageLimit"] ?? ''; ?>">
            <?php if (isset($_SESSION["usageLimit_error"])) : ?>
              <div class="text-danger text-error pt-2"><?= $_SESSION["usageLimit_error"]; ?></div>
              <?php unset($_SESSION["usageLimit_error"], $_SESSION["usageLimit_class"]); ?>
            <?php endif; ?>
          </div>

          <!-- 提交按鈕 -->
          <div class="d-flex justify-content-center gap-3 my-4">
            <button class="btn btn-primary fw-semibold" type="submit"><i class="fa-solid fa-check"></i> 創建</button>
            <a href="coupon-list.php?page=1&order=1" class="btn btn-danger fw-semibold" role="button"><i class="fa-solid fa-trash"></i> 取消創建</a>
          </div>
      </div>
      </form>
    </div>

    <script>
      // 生成隨機代碼
      document.getElementById("randomCode").addEventListener("click", function() {
        var characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        var code = '';
        for (var i = 0; i < 10; i++) {
          code += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        document.getElementById("discountCode").value = code;
      });

      // 折扣類型切換顯示
      document.querySelectorAll('input[name="discountType"]').forEach(function(elem) {
        elem.addEventListener("click", function() {
          if (elem.value == "percent") {
            document.getElementById("cashDiscountValueDiv").style.display = "none";
            document.getElementById("percentDiscountValueDiv").style.display = "block";
          } else {
            document.getElementById("cashDiscountValueDiv").style.display = "block";
            document.getElementById("percentDiscountValueDiv").style.display = "none";
          }
        });
      });

      // 頁面載入時設置折扣類型顯示
      window.onload = function() {
        var discountType = "<?= $_SESSION['discountType'] ?? '' ?>";
        if (discountType == "cash") {
          document.getElementById("cashDiscount").checked = true;
        } else if (discountType == "percent") {
          document.getElementById("percentDiscount").checked = true;
        }
        document.querySelector('input[name="discountType"]:checked').dispatchEvent(new Event('click'));
        <?php unset($_SESSION['discountType']); ?>
      }

      // 設置有效日期的邊界
      document.getElementById("validFrom").addEventListener("change", function() {
        document.getElementById("validTo").min = this.value;
      });
      document.getElementById("validTo").addEventListener("change", function() {
        document.getElementById("validFrom").max = this.value;
      });
    </script>

    <?php include("coupon-js.php") ?>
</body>

</html>