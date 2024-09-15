<?php
require_once("./db_mahjong.php");
session_start();

// 保留特定的 session 鍵和值
$keepSessionKey = ["operation_result", "successMsg", "group"];
$keepSessionValue = [];

foreach ($keepSessionKey as $key) {
  if (isset($_SESSION[$key])) {
    $keepSessionValue[$key] = $_SESSION[$key];
  }
}
session_destroy();
session_start();

foreach ($keepSessionValue as $key => $value) {
  $_SESSION[$key] = $value;
}

// 初始化變數
$pageTitle = "優惠劵列表";
$perPage = 15; // 每頁顯示的優惠劵數量
$page = isset($_GET["p"]) ? (int)$_GET["p"] : 1;
$order = isset($_GET["o"]) ? (int)$_GET["o"] : 1;
$filter = isset($_GET["f"]) ? $_GET["f"] : 'all';
$search = isset($_GET["s"]) ? $_GET["s"] : '';
$startDate = isset($_GET["s_d"]) ? $_GET["s_d"] : '';
$endDate = isset($_GET["e_d"]) ? $_GET["e_d"] : '';
$minCash = isset($_GET["mC"]) ? $_GET["mC"] : '';
$maxCash = isset($_GET["MxC"]) ? $_GET["MxC"] : '';
$minPercent = isset($_GET["mP"]) ? $_GET["mP"] : '';
$maxPercent = isset($_GET["MxP"]) ? $_GET["MxP"] : '';

// 更新過期的優惠劵狀態為 'inactive'
$updateSql = "UPDATE coupons SET status = 'inactive' WHERE valid_to < CURRENT_DATE() AND status = 'active'";
$conn->query($updateSql);

// 建立查詢條件
$whereClause = "WHERE 1=1";

if ($filter == "active") {
  $whereClause .= " AND status = 'active'";
} else if ($filter == "inactive") {
  $whereClause .= " AND status = 'inactive'";
} else if ($filter == 'percent') {
  $whereClause .= " AND discount_type = 'percent'";
  // 僅在篩選百分比類別時根據百分比進行篩選
  if (!empty($minPercent)) {
    $whereClause .= " AND discount_value >= '$minPercent'";
  }
  if (!empty($maxPercent)) {
    $whereClause .= " AND discount_value <= '$maxPercent'";
  }
} else if ($filter == 'cash') {
  $whereClause .= " AND discount_type = 'cash'";
  // 僅在篩選現金類別時根據價格進行篩選
  if (!empty($minCash)) {
    $whereClause .= " AND discount_value >= '$minCash'";
  }
  if (!empty($maxCash)) {
    $whereClause .= " AND discount_value <= '$maxCash'";
  }
}

if (!empty($startDate)) {
  $whereClause .= " AND valid_from >= '$startDate'";
}
if (!empty($endDate)) {
  $whereClause .= " AND valid_to <= '$endDate'";
}
if (!empty($search)) {
  $whereClause .= " AND (name LIKE '%$search%' OR discount_code LIKE '%$search%')";
}

// 計算總優惠劵數量
$sqlCount = "SELECT COUNT(*) AS count FROM coupons $whereClause";
$countResult = $conn->query($sqlCount);
$countRow = $countResult->fetch_assoc();
$allCouponCount = $countRow["count"];
$firstItem = ($page - 1) * $perPage;

// 設定排序方式
$orderOptions = [
  1 => "coupon_id DESC", 2 => "coupon_id ASC", 3 => "name DESC", 4 => "name ASC",
  5 => "discount_code DESC", 6 => "discount_code ASC", 7 => "discount_type DESC", 8 => "discount_type ASC",
  9 => "discount_value DESC", 10 => "discount_value ASC", 11 => "valid_from DESC", 12 => "valid_from ASC",
  13 => "valid_to DESC", 14 => "valid_to ASC", 15 => "limit_value DESC", 16 => "limit_value ASC",
  17 => "usage_limit DESC", 18 => "usage_limit ASC", 19 => "status DESC", 20 => "status ASC"
];

$sqlOrder = $orderOptions[$order] ?? $orderOptions[1];

// 設定搜尋結果標題
$searchResultTitle = "";

$conditions = [];

if (!empty($startDate) && !empty($endDate)) {
  $conditions[] = "$startDate 到 $endDate";
} elseif (!empty($startDate)) {
  $conditions[] = "$startDate 之後";
} elseif (!empty($endDate)) {
  $conditions[] = "$endDate 之前";
}

if (!empty($minCash) && !empty($maxCash)) {
  $conditions[] = "$minCash 元到 $maxCash 元";
} elseif (!empty($minCash)) {
  $conditions[] = "$minCash 元以上";
} elseif (!empty($maxCash)) {
  $conditions[] = "$maxCash 元以下";
}

if (!empty($minPercent) && !empty($maxPercent)) {
  $conditions[] = "$minPercent % 到 $maxPercent %";
} elseif (!empty($minPercent)) {
  $conditions[] = "$minPercent % 以上";
} elseif (!empty($maxPercent)) {
  $conditions[] = "$maxPercent % 以下";
}

if (!empty($search)) {
  $conditions[] = "$search";
}

if (!empty($conditions)) {
  $searchResultTitle = implode(' , ', $conditions) . " 的搜尋結果";
}

// 設定過濾條件標題
$filterTitles = [
  "active" => "（可使用）", "inactive" => "（已停用）", "percent" => "（百分比）", "cash" => "（現金）", "all" => "（所有）"
];

$filterTitle = $filterTitles[$filter] ?? $filterTitles["all"];

// 計算總頁數
$pageCount = ceil($allCouponCount / $perPage);

// 查詢優惠劵資料
$sql = "SELECT * FROM coupons $whereClause ORDER BY $sqlOrder LIMIT $firstItem, $perPage";
$result = $conn->query($sql);
$rows = $result->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">

<head>
  <title><?= $pageTitle ?></title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <?php include("coupon-css.php") ?>
</head>

<body>

  <!-- 停用確認 Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5 fw-semibold" id="deleteModalLabel">確認停用優惠劵?</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">取消</button>
          <button type="button" class="btn btn-danger fw-semibold" id="confirm">確認</button>
        </div>
      </div>
    </div>
  </div>

  <!-- 成功訊息 Modal -->
  <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5 fw-semibold" id="successModalLabel"> <?= $_SESSION["successMsg"] ?? '' ?></h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary fw-semibold" data-bs-dismiss="modal">確認</button>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="d-flex justify-content-center">
      <h1 class="py-2 fw-semibold"><?= $pageTitle ?></h1>
    </div>
    <div class="py-2">
      <div class="d-flex justify-content-between align-items-center mb-3 border p-2 rounded bg-primary-subtle">
        <!-- 日期篩選搜尋表單 -->
        <form action="" method="get" class="w-100">
          <dv class="d-flex justify-content-center gap-3">
            <div class="input-group">
              <label for="start_date" class="input-group-text fw-semibold">開始日期</label>
              <input type="date" id="start_date" name="s_d" class="form-control" value="<?= $startDate ?>">
            </div>
            <div class="input-group">
              <label for="end_date" class="input-group-text fw-semibold">結束日期</label>
              <input type="date" id="end_date" name="e_d" class="form-control" value="<?= $endDate ?>">
            </div>

            <!-- 折扣額搜尋 -->
            <div id="cashMinFilter" class="input-group d-none">
              <label for="minCash" class="input-group-text fw-semibold">最低折扣</label>
              <div class="input-group-text">$</div>
              <input type="number" id="minCash" name="mC" class="form-control" min="0" value="<?= $minCash ?>">
            </div>
            <div id="cashMaxFilter" class="input-group d-none">
              <label for="maxCash" class="input-group-text fw-semibold">最高折扣</label>
              <div class="input-group-text">$</div>
              <input type="number" id="maxCash" name="MxC" class="form-control" min="0" placeholder="" value="<?= $maxCash ?>">
            </div>

            <div id="percentMinFilter" class="input-group d-none">
              <label for="minPercent" class="input-group-text fw-semibold">最低折扣</label>
              <input type="number" id="minPercent" name="mP" class="form-control" min="0" max="100" value="<?= $minPercent ?>">
              <div class="input-group-text">%</div>
            </div>

            <div id="percentMaxFilter" class="input-group d-none">
              <label for="maxPercent" class="input-group-text fw-semibold">最高折扣</label>
              <input type="number" id="maxPercent" name="MxP" class="form-control" min="0" max="100" placeholder="" value="<?= $maxPercent ?>">
              <div class="input-group-text">%</div>
            </div>

            <!-- 名稱或折扣碼搜尋 -->
            <div class="input-group">
              <label for="search" class="input-group-text fw-semibold">關鍵字</label>
              <input type="text" class="form-control" placeholder="" name="s" value="<?= $search ?>">
            </div>

            <input type="hidden" name="f" value="<?= $filter ?>">
            <input type="hidden" name="p" value="1">
            <input type="hidden" name="o" value="<?= $order ?>">
            <button type="submit" class="btn btn-primary" name=""><i class="fa-solid fa-magnifying-glass"></i></button>
      </div>

      </form>

    </div>

    <div class="d-flex justify-content-between align-items-center gap-3 mb-3 p-2 rounded bg-primary-subtle">
      <!-- 返回按鈕 -->
      <div class="d-flex justify-content-center align-items-center gap-3">
        <?php if (isset($_GET["s"]) || isset($_GET["filter_btn"])) : ?>
          <a href="coupon-list.php?p=1&o=1" class="btn btn-primary justify-item-start fw-semibold">
            <i class="fa-solid fa-arrow-left"></i> 返回
          </a>
        <?php endif ?>

        <!-- 類別按鈕 -->
        <div class="d-flex justify-content-center align-items-center gap-2">
          <button class="btn btn-primary fw-semibold" onclick="filterCoupons('all')">所有</button>
          <button class="btn btn-success fw-semibold" onclick="filterCoupons('active')"><i class="fa-regular fa-circle-check"></i> 可使用</button>
          <button class="btn btn-danger fw-semibold" onclick="filterCoupons('inactive')"><i class="fa-regular fa-circle-xmark"></i> 已停用</button>
          <button class="btn btn-info text-white fw-semibold" onclick="filterCoupons('cash')">$ 現金</button>
          <button class="btn btn-warning text-white fw-semibold" onclick="filterCoupons('percent')">% 百分比</button>
        </div>
      </div>

      <a href="add-coupon.php" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> <span class="fw-semibold">新增</span>
      </a>
    </div>

    <div class="text-secondary pb-1 fw-semibold">
      <?= "$filterTitle $searchResultTitle 共 $allCouponCount 張優惠劵" ?>
    </div>

    <!-- 優惠劵列表 -->
    <table class="table table-light table-bordered text-center">
      <thead class="table-primary">
        <tr>
          <th><a href="?p=<?= $page ?>&o=<?= ($order == 1) ? 2 : 1 ?>&f=<?= $filter ?>&s=<?= $search ?>&s_d=<?= $startDate ?>&e_d=<?= $endDate ?>&mC=<?= $minCash ?>&MxC=<?= $maxCash ?>&mP=<?= $minPercent ?>&MxP=<?= $maxPercent ?>" class="sort-icon text-black text-decoration-none"># <i class="fa-solid fa-sort"></i></a></th>
          <th><a href="?p=<?= $page ?>&o=<?= ($order == 3) ? 4 : 3 ?>&f=<?= $filter ?>&s=<?= $search ?>&s_d=<?= $startDate ?>&e_d=<?= $endDate ?>&mC=<?= $minCash ?>&MxC=<?= $maxCash ?>&mP=<?= $minPercent ?>&MxP=<?= $maxPercent ?>" class="sort-icon text-black text-decoration-none">優惠劵名稱 <i class="fa-solid fa-sort"></i></a></th>
          <th><a href="?p=<?= $page ?>&o=<?= ($order == 5) ? 6 : 5 ?>&f=<?= $filter ?>&s=<?= $search ?>&s_d=<?= $startDate ?>&e_d=<?= $endDate ?>&mC=<?= $minCash ?>&MxC=<?= $maxCash ?>&mP=<?= $minPercent ?>&MxP=<?= $maxPercent ?>" class="sort-icon text-black text-decoration-none">折扣碼 <i class="fa-solid fa-sort"></i></a></th>
          <th><a href="?p=<?= $page ?>&o=<?= ($order == 7) ? 8 : 7 ?>&f=<?= $filter ?>&s=<?= $search ?>&s_d=<?= $startDate ?>&e_d=<?= $endDate ?>&mC=<?= $minCash ?>&MxC=<?= $maxCash ?>&mP=<?= $minPercent ?>&MxP=<?= $maxPercent ?>" class="sort-icon text-black text-decoration-none">折扣類型 <i class="fa-solid fa-sort"></i></a></th>
          <th><a href="?p=<?= $page ?>&o=<?= ($order == 9) ? 10 : 9 ?>&f=<?= $filter ?>&s=<?= $search ?>&s_d=<?= $startDate ?>&e_d=<?= $endDate ?>&mC=<?= $minCash ?>&MxC=<?= $maxCash ?>&mP=<?= $minPercent ?>&MxP=<?= $maxPercent ?>" class="sort-icon text-black text-decoration-none">折扣額 <i class="fa-solid fa-sort"></i></a></th>
          <th><a href="?p=<?= $page ?>&o=<?= ($order == 11) ? 12 : 11 ?>&f=<?= $filter ?>&s=<?= $search ?>&s_d=<?= $startDate ?>&e_d=<?= $endDate ?>&mC=<?= $minCash ?>&MxC=<?= $maxCash ?>&mP=<?= $minPercent ?>&MxP=<?= $maxPercent ?>" class="sort-icon text-black text-decoration-none">有效起始日 <i class="fa-solid fa-sort"></i></a></th>
          <th><a href="?p=<?= $page ?>&o=<?= ($order == 13) ? 14 : 13 ?>&f=<?= $filter ?>&s=<?= $search ?>&s_d=<?= $startDate ?>&e_d=<?= $endDate ?>&mC=<?= $minCash ?>&MxC=<?= $maxCash ?>&mP=<?= $minPercent ?>&MxP=<?= $maxPercent ?>" class="sort-icon text-black text-decoration-none">有效截止日 <i class=" fa-solid fa-sort"></i></a></th>
          <th><a href="?p=<?= $page ?>&o=<?= ($order == 15) ? 16 : 15 ?>&f=<?= $filter ?>&s=<?= $search ?>&s_d=<?= $startDate ?>&e_d=<?= $endDate ?>&mC=<?= $minCash ?>&MxC=<?= $maxCash ?>&mP=<?= $minPercent ?>&MxP=<?= $maxPercent ?>" class="sort-icon text-black text-decoration-none">使用最低消費金額 <i class="fa-solid fa-sort"></i></a></th>
          <th><a href="?p=<?= $page ?>&o=<?= ($order == 17) ? 18 : 17 ?>&f=<?= $filter ?>&s=<?= $search ?>&s_d=<?= $startDate ?>&e_d=<?= $endDate ?>&mC=<?= $minCash ?>&MxC=<?= $maxCash ?>&mP=<?= $minPercent ?>&MxP=<?= $maxPercent ?>" class="sort-icon text-black text-decoration-none">可使用次數 <i class="fa-solid fa-sort"></i></a></th>
          <th><a href="?p=<?= $page ?>&o=<?= ($order == 19) ? 20 : 19 ?>&f=<?= $filter ?>&s=<?= $search ?>&s_d=<?= $startDate ?>&e_d=<?= $endDate ?>&mC=<?= $minCash ?>&MxC=<?= $maxCash ?>&mP=<?= $minPercent ?>&MxP=<?= $maxPercent ?>" class="sort-icon text-black text-decoration-none">狀態 <i class="fa-solid fa-sort"></i></a></th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $coupon) : ?>
          <tr class="fw-semibold">
            <td><?= $coupon['coupon_id'] ?></td>
            <td><?= $coupon['name'] ?></td>
            <td><?= $coupon['discount_code'] ?></td>
            <td>
              <?php if ($coupon['discount_type'] == 'cash') : ?>
                <span class="text-primary fw-semibold">現金</span>
              <?php elseif ($coupon['discount_type'] == 'percent') : ?>
                <span class="text-warning fw-semibold">百分比</span>
              <?php endif; ?>
            </td>
            <td class="fw-semibold">
              <?php if ($coupon['discount_type'] == 'cash') : ?>
                <span class="text-primary fw-semibold"><?= '$' . " " . $coupon['discount_value']; ?></span>
              <?php elseif ($coupon['discount_type'] == 'percent') : ?>
                <span class="text-warning fw-semibold"><?= $coupon['discount_value'] . " " . '%'; ?></span>
              <?php endif; ?>
            </td>
            <td><?= $coupon['valid_from'] ?></td>
            <td><?= $coupon['valid_to'] ?></td>
            <td><span class="text-danger fw-semibold"><?= '$' . $coupon['limit_value'] ?></span></td>
            <td><?= $coupon['usage_limit'] ?></td>
            <td><span class="fw-semibold <?= ($coupon['status'] == 'active') ? 'text-success' : 'text-danger'; ?>">
                <?= ($coupon['status'] == 'active') ? '<i class="fa-regular fa-circle-check"></i> 可使用' : '<i class="fa-regular fa-circle-xmark"></i> 已停用'; ?>
              </span></td>
            <td>
              <a href="coupon-detail.php?coupon_id=<?= $coupon['coupon_id'] ?>" class="btn btn-primary" title="優惠劵詳細資料">
                <i class="fa-solid fa-square-poll-horizontal"></i>
              </a>
              <?php if ($coupon['status'] == 'inactive') : ?>
                <button class="btn btn btn-secondary" disabled title="停用優惠劵" data-id="<?= $coupon['coupon_id'] ?>" data-bs-toggle="modal" data-bs-target="#deleteModal">
                  <i class="fa-solid fa-trash-can"></i>
                </button>
              <?php else : ?>
                <button class="btn btn-danger btn-disable" title="停用優惠劵" data-id="<?= $coupon['coupon_id'] ?>" data-bs-toggle="modal" data-bs-target="#deleteModal">
                  <i class="fa-solid fa-trash-can"></i>
                </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
    </form>

    <!-- 分頁導航 -->
    <div class="d-flex justify-content-center">
      <?php if (isset($_GET["p"])) : ?>
        <nav aria-label="Page navigation example">
          <ul class="pagination">
            <?php for ($i = 1; $i <= $pageCount; $i++) : ?>
              <li class="page-item <?= ($i == $page) ? "active" : "" ?>">
                <a class="page-link" href="?p=<?= $i ?>&o=<?= $order ?>&f=<?= $filter ?>&s=<?= $search ?>&s_d=<?= $startDate ?>&e_d=<?= $endDate ?>&mC=<?= $minCash ?>&MxC=<?= $maxCash ?>&mP=<?= $minPercent ?>&MxP=<?= $maxPercent ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </div>
  </div>
  </div>
  <?php include("coupon-js.php") ?>


  <script>
    // 停用按鈕點擊事件
    const btnDisable = document.querySelectorAll('.btn-disable');
    const confirm = document.querySelector('#confirm');
    const modalTitle = document.querySelector('#deleteModalLabel');

    let currentCouponId;

    btnDisable.forEach(btn => {
      btn.addEventListener('click', function() {
        currentCouponId = this.dataset.id;
        confirm.removeAttribute('disabled');
        modalTitle.textContent = '確認停用優惠卷 ' + '#' + currentCouponId + ' ?';
      });
    });

    // 停用確認按鈕點擊完事件
    confirm.addEventListener('click', function() {
      confirm.setAttribute('disabled', true);
      fetch(`listDoDisableCoupon.php?coupon_id=${currentCouponId}`)
        .then(response => response.json())
        .then(data => {
          if (data.operation_result === 'success') {
            modalTitle.textContent = '#' + currentCouponId + ' 停用成功';
            // 顯示成功訊息並刷新頁面
            setTimeout(() => {
              location.reload(); // 刷新頁面以顯示最新狀態
            }, 1000); // 1 秒延遲
          } else {
            modalTitle.textContent = '操作失敗：' + data.message;
            confirm.removeAttribute('disabled');
          }
        });
    });

    // 設置有效日期的邊界
    document.getElementById("start_date").addEventListener("change", function() {
      document.getElementById("end_date").min = this.value;
    });
    document.getElementById("end_date").addEventListener("change", function() {
      document.getElementById("start_date").max = this.value;
    });

    // 過濾優惠劵
    function filterCoupons(filter) {
      let url = new URL(window.location.href);
      url.searchParams.set("f", filter);
      url.searchParams.set("p", 1);
      url.searchParams.set("o", <?= $order ?>);
      url.searchParams.set("s", "<?= $search ?>");
      url.searchParams.set("s_d", "<?= $startDate ?>");
      url.searchParams.set("e_d", "<?= $endDate ?>");
      url.searchParams.set("mC", "<?= $minCash ?>");
      url.searchParams.set("MxC", "<?= $maxCash ?>");
      url.searchParams.set("mP", "<?= $minPercent ?>");
      url.searchParams.set("MxP", "<?= $maxPercent ?>");
      window.location.href = url.toString();
    }

    // 檢查是否有成功訊息，如果有則顯示 modal
    document.addEventListener('DOMContentLoaded', function() {
      <?php if (isset($_SESSION["successMsg"])) : ?>
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
        setTimeout(() => {
          location.reload(); // 刷新頁面以顯示最新狀態
        }, 1500); // 1 秒延遲
        <?php unset($_SESSION["successMsg"]); ?>
      <?php endif; ?>
    });

    // 顯示或隱藏價格篩選和百分比篩選
    document.addEventListener('DOMContentLoaded', function() {
      let filter = "<?= $filter ?>";
      if (filter === 'cash') {
        document.getElementById('cashMinFilter').classList.remove('d-none');
        document.getElementById('cashMaxFilter').classList.remove('d-none');
      } else if (filter === 'percent') {
        document.getElementById('percentMinFilter').classList.remove('d-none');
        document.getElementById('percentMaxFilter').classList.remove('d-none');
      }
    });
  </script>
</body>

</html>