<?php
// 檢查 coupon_id 是否存在，若不存在則設置為 1
$coupon_id = isset($_GET["coupon_id"]) ? $_GET["coupon_id"] : 1;

require_once("./db_mahjong.php");

// 查詢優惠劵資料
$sql = "SELECT * FROM coupons WHERE coupon_id = $coupon_id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

// 根據查詢結果設定標題和狀態
if ($result->num_rows > 0) {
  $coupon = true;
  $title = $row["coupon_id"];
} else {
  $coupon = false;
  $title = "優惠劵不存在";
}
?>

<!doctype html>
<html lang="en">

<head>
  <title><?= $title ?></title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <?php include("coupon-css.php") // 引入樣式文件
  ?>
</head>

<body>
  <!-- 停用確認 Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5 fw-semibold" id="deleteModalLabel">確認停用優惠卷?</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">取消</button>
          <a href="" type="button" class="btn btn-danger fw-semibold" id="confirm">確認</a>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="text-center">
      <h1 class="py-2 fw-semibold">優惠劵詳細資料</h1>
    </div>
    <div class="row justify-content-center position-relative">
      <div class="return py-3 position-absolute">
        <a class="btn btn-primary fw-semibold" href="coupon-list.php?p=1&o=1"><i class="fa-solid fa-arrow-left"></i> 回優惠劵列表</a>
      </div>
      <div class="col-6">
        <?php if ($coupon) : ?>
          <table class="table table-light table-bordered text-center">
            <!-- 顯示優惠劵詳細資料 -->
            <thead class="table-primary">
              <tr>
                <th class="col-3">項目</th>
                <td class="col-9 fw-semibold">說明</td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th class="col-3">優惠劵編號</th>
                <td class="col-9 fw-semibold"><?= $row["coupon_id"] ?></td>
              </tr>
              <tr>
                <th>優惠劵名稱</th>
                <td class="fw-semibold"><?= $row["name"] ?></td>
              </tr>
              <tr>
                <th>優惠劵折扣碼</th>
                <td class="fw-semibold"><?= $row["discount_code"] ?></td>
              </tr>
              <tr>
                <th>優惠劵折扣類型</th>
                <td class="fw-semibold">
                  <?php if ($row['discount_type'] == 'cash') : ?>
                    <span class="text-primary fw-semibold">現金</span>
                  <?php elseif ($row['discount_type'] == 'percent') : ?>
                    <span class="text-warning fw-semibold">百分比</span>
                  <?php endif; ?>
                </td>
              </tr>
              <tr class="fw-semibold">
                <th>優惠劵折扣值</th>
                <td>
                  <?php if ($row['discount_type'] == 'cash') : ?>
                    <span class="text-primary fw-semibold"><?= '$' . " " . $row['discount_value']; ?></span>
                  <?php elseif ($row['discount_type'] == 'percent') : ?>
                    <span class="text-warning fw-semibold"><?= $row['discount_value'] . " " . '%'; ?></span>
                  <?php endif; ?>
                </td>
              </tr>
              <tr class="fw-semibold">
                <th>優惠劵有效期</th>
                <td><?= $row["valid_from"] ?> ~ <?= $row["valid_to"] ?></td>
              </tr>
              <tr class="fw-semibold">
                <th>使用最低消費金額</th>
                <td>
                  <span class="text-danger fw-semibold"><?= '$' . $row['limit_value'] ?></span>
                </td>
              </tr>
              <tr class="fw-semibold">
                <th>可使用次數</th>
                <td><?= $row["usage_limit"] ?></td>
              </tr>
              <tr class="fw-semibold">
                <th>狀態</th>
                <td>
                  <span class="fw-semibold <?= ($row['status'] == 'active') ? 'text-success' : 'text-danger'; ?>">
                    <?= ($row['status'] == 'active') ? '<i class="fa-regular fa-circle-check"></i> 可使用' : '<i class="fa-regular fa-circle-xmark"></i> 已停用'; ?>
                  </span>
                </td>
              </tr>
              <tr class="fw-semibold">
                <th>創建時間</th>
                <td><?= $row["created_at"] ?></td>
              </tr>
              <tr class="fw-semibold">
                <th>最後修改時間</th>
                <td><?= $row["update_at"] ?></td>
              </tr>
            </tbody>
          </table>
      </div>
      <div class="d-flex justify-content-center gap-3">
        <a href="edit-coupon.php?coupon_id=<?= $row["coupon_id"] ?>" title="編輯優惠劵" class="btn btn-primary fw-semibold">
          <i class="fa-solid fa-pen-to-square"></i> 修改優惠劵
        </a>

        <button class="btn btn-danger btn-disable fw-semibold" title="停用優惠劵" data-id="<?= $row["coupon_id"] ?>" data-bs-toggle="modal" data-bs-target="#deleteModal">
          <i class="fa-solid fa-trash-can"></i> 停用優惠劵
        </button>
      </div>
    </div>
  <?php else : ?>
    <h1>優惠劵不存在</h1>
  <?php endif; ?>
  </div>
  </div>

  <script>
    // 停用按鈕點擊事件
    const btnDisable = document.querySelectorAll('.btn-disable');
    const confirm = document.querySelector('#confirm');

    btnDisable.forEach(btn => {
      btn.addEventListener('click', function() {
        const couponId = this.dataset.id;
        confirm.href = "detailDoDisableCoupon.php?coupon_id=" + couponId;
      });
    });
  </script>
  <?= include("coupon-js.php") // 引入 JavaScript 文件 
  ?>
</body>

</html>