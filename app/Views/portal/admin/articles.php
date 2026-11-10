<?php
$counts = $counts ?? [
    'total' => 0,
    'published' => 0,
    'draft' => 0,
    'pending' => 0,
    'scheduled' => 0,
    'en' => 0,
    'si' => 0,
    'ta' => 0
];
$activeStatus = $activeStatus ?? ($_GET['status'] ?? '');
$activeEdition = $activeEdition ?? ($_GET['edition'] ?? '');
$searchQuery = $searchQuery ?? ($_GET['q'] ?? '');

$filterParams = static function(array $overrides = []) use ($activeStatus, $activeEdition, $searchQuery): array {
    $current = [
        'status' => $activeStatus,
        'edition' => $activeEdition,
        'q' => $searchQuery
    ];
    $merged = array_merge($current, $overrides);
    return array_filter($merged, fn($v) => $v !== null && $v !== '');
};
?>

<!-- Header / Overview Banner -->
<div class="media-hero">
  <div class="media-hero-content">
    <h2>News Stories & Editorial Desks</h2>
    <p>Unified Multi-Domain Newsroom: Publish, schedule, and distribute news across <code>news.liontech.lk</code> (English), <code>sinhala.news.liontech.lk</code> (Sinhala), and <code>tamil.news.liontech.lk</code> (Tamil).</p>
  </div>
  <div class="media-hero-actions" style="display:flex;gap:8px;flex-wrap:wrap">
    <a class="button" href="<?= e(url('admin/articles/create')) ?>">+ Write a new story</a>
    <div style="display:inline-flex;gap:4px">
      <a class="button secondary" href="<?= e(edition_url('en')) ?>" target="_blank" rel="noopener" title="Main English site">news. ↗</a>
      <a class="button secondary" href="<?= e(edition_url('si')) ?>" target="_blank" rel="noopener" title="Sinhala site">sinhala. ↗</a>
      <a class="button secondary" href="<?= e(edition_url('ta')) ?>" target="_blank" rel="noopener" title="Tamil site">tamil. ↗</a>
    </div>
  </div>
</div>

<!-- Metrics Bar -->
<div class="stat-grid" style="margin-bottom:20px;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr))">
  <div class="stat">
    <span>Published stories</span>
    <strong style="color:var(--accent-green, #16a34a)"><?= (int)$counts['published'] ?></strong>
  </div>
  <div class="stat">
    <span>Drafts</span>
    <strong style="color:var(--muted, #64748b)"><?= (int)$counts['draft'] ?></strong>
  </div>
  <div class="stat">
    <span>Under review</span>
    <strong style="color:var(--accent-red, #dc2626)"><?= (int)$counts['pending'] ?></strong>
  </div>
  <div class="stat">
    <span>Scheduled</span>
    <strong style="color:var(--accent-blue, #2563eb)"><?= (int)$counts['scheduled'] ?></strong>
  </div>
  <div class="stat">
    <span>news.liontech.lk</span>
    <strong style="color:#991b1b"><?= (int)($counts['en'] ?? 0) ?></strong>
  </div>
  <div class="stat">
    <span>sinhala.news...</span>
    <strong style="color:#92400e"><?= (int)($counts['si'] ?? 0) ?></strong>
  </div>
  <div class="stat">
    <span>tamil.news...</span>
    <strong style="color:#3730a3"><?= (int)($counts['ta'] ?? 0) ?></strong>
  </div>
</div>

<!-- Edition Filter Bar -->
<div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;background:#f8fafc;padding:10px 14px;border:1px solid #e2e8f0;border-radius:6px;flex-wrap:wrap">
  <strong style="font-size:12px;color:#475569;text-transform:uppercase;letter-spacing:0.5px">Subdomain Edition:</strong>
  <a class="filter-pill <?= empty($activeEdition) ? 'active' : '' ?>" href="<?= e(url('admin/articles', $filterParams(['edition' => '']))) ?>">
    🌐 All Subdomains (<?= (int)$counts['total'] ?>)
  </a>
  <a class="filter-pill <?= $activeEdition === 'en' ? 'active' : '' ?>" href="<?= e(url('admin/articles', $filterParams(['edition' => 'en']))) ?>" style="border-left:3px solid #c4161c">
    English (news.liontech.lk)
  </a>
  <a class="filter-pill <?= $activeEdition === 'si' ? 'active' : '' ?>" href="<?= e(url('admin/articles', $filterParams(['edition' => 'si']))) ?>" style="border-left:3px solid #8b0d11">
    සිංහල (sinhala.news.liontech.lk)
  </a>
  <a class="filter-pill <?= $activeEdition === 'ta' ? 'active' : '' ?>" href="<?= e(url('admin/articles', $filterParams(['edition' => 'ta']))) ?>" style="border-left:3px solid #b91c1c">
    தமிழ் (tamil.news.liontech.lk)
  </a>
</div>

<!-- Status & Search Filter Bar -->
<div class="media-filter-bar">
  <div class="filter-pills">
    <a class="filter-pill <?= empty($activeStatus) ? 'active' : '' ?>" href="<?= e(url('admin/articles', $filterParams(['status' => '']))) ?>">
      All Statuses
    </a>
    <a class="filter-pill <?= $activeStatus === 'published' ? 'active' : '' ?>" href="<?= e(url('admin/articles', $filterParams(['status' => 'published']))) ?>">
      Published
    </a>
    <a class="filter-pill <?= $activeStatus === 'pending' ? 'active' : '' ?>" href="<?= e(url('admin/articles', $filterParams(['status' => 'pending']))) ?>">
      Review (<?= (int)$counts['pending'] ?>)
    </a>
    <a class="filter-pill <?= $activeStatus === 'draft' ? 'active' : '' ?>" href="<?= e(url('admin/articles', $filterParams(['status' => 'draft']))) ?>">
      Drafts
    </a>
    <a class="filter-pill <?= $activeStatus === 'scheduled' ? 'active' : '' ?>" href="<?= e(url('admin/articles', $filterParams(['status' => 'scheduled']))) ?>">
      Scheduled
    </a>
    <a class="filter-pill <?= $activeStatus === 'archived' ? 'active' : '' ?>" href="<?= e(url('admin/articles', $filterParams(['status' => 'archived']))) ?>">
      Archived
    </a>
  </div>

  <form method="get" class="media-search-box" action="<?= e(url('admin/articles')) ?>">
    <?php if (!empty($activeStatus)): ?>
      <input type="hidden" name="status" value="<?= e($activeStatus) ?>">
    <?php endif; ?>
    <?php if (!empty($activeEdition)): ?>
      <input type="hidden" name="edition" value="<?= e($activeEdition) ?>">
    <?php endif; ?>
    <input type="text" name="q" placeholder="Search headlines across languages..." value="<?= e($searchQuery) ?>" aria-label="Search stories">
    <button class="button secondary" type="submit" style="min-height:auto;padding:8px 14px">Search</button>
    <?php if (!empty($searchQuery) || !empty($activeEdition) || !empty($activeStatus)): ?>
      <a href="<?= e(url('admin/articles')) ?>" class="button secondary" style="min-height:auto;padding:8px 10px;text-decoration:none" title="Clear all filters">✕</a>
    <?php endif; ?>
  </form>
</div>

<!-- Stories Table -->
<div class="panel table-wrap" style="padding:0;overflow:hidden">
  <table>
    <thead>
      <tr>
        <th style="padding-left:24px">Story Headline & Active Subdomains</th>
        <th>Category</th>
        <th>Byline / Author</th>
        <th>Status</th>
        <th>Publication Date</th>
        <th style="text-align:right;padding-right:24px">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $row): 
        $displayTitle = $row['title_en'] ?: ($row['title_si'] ?: ($row['title_ta'] ?: 'Untitled story'));
      ?>
      <tr>
        <td style="padding-left:24px">
          <a href="<?= e(url('admin/articles/edit/' . $row['id'])) ?>" style="font-size:14px;color:var(--ink);text-decoration:none">
            <strong><?= e($displayTitle) ?></strong>
          </a>
          <!-- Active Language / Subdomain Badges -->
          <div style="display:flex;gap:6px;margin-top:6px;align-items:center;flex-wrap:wrap">
            <?php if (!empty($row['title_en']) && !empty($row['content_en'])): ?>
              <span class="badge" title="Published on news.liontech.lk" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;padding:2px 7px;border-radius:4px;font-size:11px;font-weight:700">
                news.liontech.lk
              </span>
            <?php endif; ?>
            <?php if (!empty($row['title_si']) && !empty($row['content_si'])): ?>
              <span class="badge" title="Published on sinhala.news.liontech.lk" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:2px 7px;border-radius:4px;font-size:11px;font-weight:700">
                sinhala.news.liontech.lk
              </span>
            <?php endif; ?>
            <?php if (!empty($row['title_ta']) && !empty($row['content_ta'])): ?>
              <span class="badge" title="Published on tamil.news.liontech.lk" style="background:#e0e7ff;color:#3730a3;border:1px solid #c7d2fe;padding:2px 7px;border-radius:4px;font-size:11px;font-weight:700">
                tamil.news.liontech.lk
              </span>
            <?php endif; ?>

            <?php if (!empty($row['is_breaking'])): ?>
              <span class="badge" style="background:#fee2e2;color:#b91c1c;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700">🔥 Breaking</span>
            <?php endif; ?>
            <?php if (!empty($row['is_featured'])): ?>
              <span class="badge" style="background:#fef3c7;color:#b45309;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700">★ Lead</span>
            <?php endif; ?>
          </div>
        </td>
        <td>
          <span style="font-size:12px;color:#475569"><?= e($row['category_name'] ?? 'General') ?></span>
        </td>
        <td>
          <span style="font-size:12px;font-weight:500"><?= e($row['author']) ?></span>
        </td>
        <td>
          <span class="status <?= e($row['status']) ?>">
            <?= e($row['status'] === 'scheduled' && $row['published_at'] <= date('Y-m-d H:i:s') ? 'published' : $row['status']) ?>
          </span>
        </td>
        <td>
          <span style="font-size:12px;color:var(--muted)">
            <?= !empty($row['published_at']) ? e(date('d M Y, H:i', strtotime($row['published_at']))) : '—' ?>
          </span>
        </td>
        <td style="text-align:right;padding-right:24px">
          <div class="inline-actions" style="justify-content:flex-end">
            <a href="<?= e(url('admin/articles/edit/' . $row['id'])) ?>" class="button secondary" style="min-height:auto;padding:5px 12px;font-size:11px">Edit</a>
            
            <!-- Quick View per Language Subdomain -->
            <?php if (!empty($row['slug']) && $row['status'] === 'published'): ?>
              <?php foreach (['en' => 'news.', 'si' => 'sinhala.', 'ta' => 'tamil.'] as $subLang => $subPrefix): ?>
                <?php if (!empty($row['title_' . $subLang])): ?>
                  <a href="<?= e(edition_url($subLang, 'news/' . $row['slug'])) ?>" 
                     target="_blank" 
                     rel="noopener" 
                     class="button secondary" 
                     style="min-height:auto;padding:5px 7px;font-size:10px" 
                     title="View story on <?= e($subPrefix) ?>liontech.lk">
                    <?= e($subPrefix) ?>↗
                  </a>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endif; ?>

            <form action="<?= e(url('admin/articles/archive/' . $row['id'])) ?>" method="post" data-confirm="Archive this story? It will no longer appear on public subdomains." style="display:inline;margin:0">
              <?= csrf() ?>
              <button class="button secondary" style="min-height:auto;padding:5px 10px;font-size:11px;color:#64748b">Archive</button>
            </form>
            <?php if ($user['role'] !== 'reporter'): ?>
              <form action="<?= e(url('admin/articles/delete/' . $row['id'])) ?>" method="post" data-confirm="Permanently delete this story from all subdomains? This cannot be undone." style="display:inline;margin:0">
                <?= csrf() ?>
                <button class="button secondary" style="min-height:auto;padding:5px 10px;font-size:11px;color:#b91c1c">Delete</button>
              </form>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?>
      <tr>
        <td colspan="6" style="text-align:center;padding:50px 20px">
          <div style="font-size:36px;margin-bottom:10px">📰</div>
          <h3 style="margin:0 0 6px">No stories match your filter</h3>
          <p class="muted" style="margin:0 0 16px">Try searching for other terms or clear your active filters.</p>
          <a href="<?= e(url('admin/articles')) ?>" class="button secondary">Clear filters</a>
        </td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require dirname(__DIR__) . '/pagination.php'; ?>
