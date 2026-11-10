<?php 
$page = (int)($page ?? 1);
$totalPages = (int)($totalPages ?? ($more ? $page + 1 : $page));
if ($totalPages > 1 || $page > 1 || ($more ?? false)):
  $params = $_GET;
?>
<nav class="ad-pagination-container" aria-label="Page navigation">
  <div class="ad-pagination">
    <?php if ($page > 1): ?>
      <a href="?<?= e(http_build_query(array_merge($params, ['page' => 1]))) ?>" class="ad-page-link ad-page-first">« First page »</a>
      <a href="?<?= e(http_build_query(array_merge($params, ['page' => $page - 1]))) ?>" class="ad-page-link ad-page-prev">Previous</a>
    <?php endif; ?>

    <?php
      $start = max(1, $page - 2);
      $end = min($totalPages, $page + 2);
      
      if ($start > 1) {
        echo '<a href="?'.e(http_build_query(array_merge($params, ['page' => 1]))).'" class="ad-page-link ad-page-num">1</a>';
        if ($start > 2) {
          echo '<span class="ad-page-ellipsis">...</span>';
        }
      }
      
      for ($p = $start; $p <= $end; $p++) {
        $activeClass = ($p === $page) ? ' active' : '';
        echo '<a href="?'.e(http_build_query(array_merge($params, ['page' => $p]))).'" class="ad-page-link ad-page-num'.$activeClass.'">'.$p.'</a>';
      }
      
      if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
          echo '<span class="ad-page-ellipsis">...</span>';
        }
        echo '<a href="?'.e(http_build_query(array_merge($params, ['page' => $totalPages]))).'" class="ad-page-link ad-page-num">'.$totalPages.'</a>';
      }
    ?>

    <?php if ($page < $totalPages || ($more ?? false)): ?>
      <a href="?<?= e(http_build_query(array_merge($params, ['page' => $page + 1]))) ?>" class="ad-page-link ad-page-next">Next »</a>
    <?php endif; ?>
  </div>
</nav>
<?php endif; ?>
