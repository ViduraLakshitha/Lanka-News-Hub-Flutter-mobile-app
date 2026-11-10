<p class="muted">Review content and photos before approval. Approval starts a new 30-day listing period. Editing a
  listing always sends it back for review.</p><?php if (!$items): ?>
  <div class="empty">No advertisements to review.</div><?php endif ?><?php foreach ($items as $item): ?>
  <article class="panel review-card">
    <div>
      <div class="section-heading">
        <h2>#<?= (int) $item['id'] ?> · <?= e($item['title']) ?></h2><span
          class="status <?= e($item['status']) ?>"><?= e($item['status']) ?></span>
      </div>
      <p class="meta">Advertiser: <strong><?= e($item['owner_name'] ?? 'Member') ?></strong>
        (<?= e($item['owner_email']) ?> · <?= e($item['owner_status'] ?? 'active') ?> · Member since
        <?= e(date('M Y', strtotime($item['owner_registered_at'] ?? 'now'))) ?>) · Location: <?= e($item['location']) ?> ·
        Edition: <?= e(strtoupper($item['language'])) ?></p>
      <p class="preserve-lines"><?= e($item['description']) ?></p>
      <p><strong><?= $item['price'] !== null ? 'LKR ' . number_format($item['price'], 2) : 'Contact for price' ?></strong> ·
        <?= e($item['contact_name']) ?> · <?= e($item['contact_phone']) ?> · <?= e($item['contact_email']) ?></p>
      <div class="review-photos"><?php foreach (json_decode($item['images'], true) ?: [] as $photo): ?><a
            href="<?= e(media_url($photo)) ?>" target="_blank" rel="noopener"><img src="<?= e(media_url($photo)) ?>"
              alt="Listing photo" loading="lazy"></a><?php endforeach ?></div><?php if ($item['premium_requested']): ?>
        <p class="notice">Premium placement requested.</p><?php endif ?>
    </div>
    <form method="post" class="form-stack"><?= csrf() ?><input type="hidden" name="id"
        value="<?= (int) $item['id'] ?>"><label>Review decision<select name="status">
          <option value="approved" <?= $item['status'] === 'approved' ? 'selected' : '' ?>>Approve</option>
          <option value="rejected" <?= $item['status'] === 'rejected' ? 'selected' : '' ?>>Reject</option>
          <option value="suspended" <?= $item['status'] === 'suspended' ? 'selected' : '' ?>>Suspend</option>
          <option value="withdrawn" <?= $item['status'] === 'withdrawn' ? 'selected' : '' ?>>Withdraw</option>
          <option value="pending" <?= $item['status'] === 'pending' ? 'selected' : '' ?>>Return to pending review</option>
        </select></label><label>Note to advertiser<textarea name="moderation_note" rows="3"
          maxlength="2000"><?= e($item['moderation_note']) ?></textarea></label><label class="check"><input
          type="checkbox" name="is_premium" <?= $item['is_premium'] ? 'checked' : '' ?>> Premium (confirmed payment
        required)</label><button class="button">Save decision</button></form>
    <form method="post" action="<?= e(url('classifieds/delete/' . $item['id'])) ?>"
      data-confirm="Permanently delete this advertisement? All images and records will be removed."
      style="margin-top:10px"><?= csrf() ?><button class="link-button" style="color:#b91c1c">Delete listing</button>
    </form>
  </article><?php endforeach ?><?php require dirname(__DIR__) . '/pagination.php'; ?>