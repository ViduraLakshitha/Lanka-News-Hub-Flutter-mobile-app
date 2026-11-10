<div class="management-grid">
  <!-- Users List Section -->
  <section class="panel table-wrap">
    <div class="section-heading mb-3">
      <div>
        <h2>People & Writers</h2>
        <span class="muted">Manage newsroom journalists, editors, administrators and their language domains</span>
      </div>
    </div>
    <table>
      <thead>
        <tr>
          <th>Person</th>
          <th>Role</th>
          <th>Writing Domains / Editions</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $row): ?>
          <?php 
            $userLangs = \App\Helpers\DomainHelper::userLanguages($row);
            $roleSlug = $row['role_slug'] ?? 'member';
          ?>
          <tr>
            <td>
              <strong><?= e($row['name']) ?></strong>
              <small class="text-muted d-block"><?= e($row['email']) ?></small>
            </td>
            <td>
              <span class="badge" style="background:#e2e8f0; color:#1e293b; font-weight:700; padding:3px 8px; border-radius:4px; font-size:11px;">
                <?= e($row['role_name']) ?>
              </span>
            </td>
            <td>
              <?php if (in_array($roleSlug, ['super_admin', 'editor'], true)): ?>
                <span class="badge" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:10.5px; padding:3px 7px; border-radius:4px; font-weight:600;">
                  🌐 All Subdomains
                </span>
              <?php else: ?>
                <div style="display:flex; flex-wrap:wrap; gap:4px;">
                  <?php if (in_array('en', $userLangs, true)): ?>
                    <span title="news.liontech.lk" class="badge" style="background:#fee2e2; color:#991b1b; font-size:10px; font-weight:700; padding:2px 6px; border-radius:3px;">
                      EN (news.)
                    </span>
                  <?php endif; ?>
                  <?php if (in_array('si', $userLangs, true)): ?>
                    <span title="sinhala.news.liontech.lk" class="badge" style="background:#fef3c7; color:#92400e; font-size:10px; font-weight:700; padding:2px 6px; border-radius:3px;">
                      සිංහල (sinhala.)
                    </span>
                  <?php endif; ?>
                  <?php if (in_array('ta', $userLangs, true)): ?>
                    <span title="tamil.news.liontech.lk" class="badge" style="background:#f0fdf4; color:#166534; font-size:10px; font-weight:700; padding:2px 6px; border-radius:3px;">
                      தமிழ் (tamil.)
                    </span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <span style="display:inline-flex; align-items:center; gap:4px; font-size:11.5px; font-weight:600; color:<?= $row['status']==='active' ? '#16a34a' : '#94a3b8' ?>;">
                <?= $row['status']==='active' ? '● Active' : '○ Inactive' ?>
              </span>
            </td>
            <td>
              <a class="button secondary small" style="padding:3px 10px; font-size:11px;" href="?edit=<?= (int)$row['id'] ?>">Edit</a>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </section>

  <!-- Add / Edit Person Form -->
  <form method="post" class="panel form-stack">
    <?= csrf() ?>
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <h2><?= $edit ? 'Edit person & writer' : 'Add article writer / person' ?></h2>

    <?php 
      $field('name', 'Full Name', 'text', null, 'required maxlength="100" placeholder="e.g. Kasun Perera"'); 
      $field('email', 'Email Address', 'email', null, 'required maxlength="255" placeholder="writer@news.liontech.lk"'); 
      $field('password', $edit ? 'New password (leave blank to keep current)' : 'Password', 'password', '', 'minlength="12" maxlength="72" autocomplete="new-password"'); 
    ?>

    <label>
      System Role
      <select name="role_id" id="userRoleSelect">
        <?php foreach ($roles as $role): ?>
          <option value="<?= (int)$role['id'] ?>" <?= ($edit['role_id'] ?? 3)==$role['id'] ? 'selected' : '' ?>>
            <?= e($role['name']) ?><?= $role['slug']==='reporter' ? ' (Article Writer)' : '' ?>
          </option>
        <?php endforeach ?>
      </select>
    </label>

    <!-- Language / Subdomain Permissions -->
    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:12px; margin-top:6px;">
      <label style="font-weight:700; margin-bottom:4px; display:block;">
        Assigned Writing Languages & Domains
      </label>
      <p class="muted" style="font-size:11px; margin-bottom:8px;">
        Choose which language edition(s) this writer is authorized to report for. Stories written will be published to the corresponding subdomain.
      </p>

      <?php 
        $currentEditLangs = !empty($edit['allowed_languages']) 
          ? array_map('trim', explode(',', $edit['allowed_languages'])) 
          : ($edit ? ['en', 'si', 'ta'] : ['si']); // Default to Sinhala for new local writers or whatever admin picks
      ?>

      <div style="display:flex; flex-direction:column; gap:6px;">
        <label class="check" style="margin:0; font-weight:600;">
          <input type="checkbox" name="allowed_languages[]" value="en" <?= in_array('en', $currentEditLangs, true) ? 'checked' : '' ?>>
          <span><strong>English Edition</strong> &mdash; <code>news.liontech.lk</code></span>
        </label>
        <label class="check" style="margin:0; font-weight:600;">
          <input type="checkbox" name="allowed_languages[]" value="si" <?= in_array('si', $currentEditLangs, true) ? 'checked' : '' ?>>
          <span><strong>සිංහල (Sinhala Edition)</strong> &mdash; <code>sinhala.news.liontech.lk</code></span>
        </label>
        <label class="check" style="margin:0; font-weight:600;">
          <input type="checkbox" name="allowed_languages[]" value="ta" <?= in_array('ta', $currentEditLangs, true) ? 'checked' : '' ?>>
          <span><strong>தமிழ் (Tamil Edition)</strong> &mdash; <code>tamil.news.liontech.lk</code></span>
        </label>
      </div>
    </div>

    <label class="check" style="margin-top:8px;">
      <input type="checkbox" name="active" <?= ($edit['status'] ?? 'active')==='active' ? 'checked' : '' ?>> 
      Active Account
    </label>

    <div style="display:flex; gap:10px; margin-top:12px;">
      <button class="button" style="flex:1;">Save person →</button>
      <?php if ($edit): ?>
        <a class="button secondary" href="<?= e(url('admin/users')) ?>">Cancel</a>
      <?php endif; ?>
    </div>

    <small class="muted" style="display:block; margin-top:8px; line-height:1.4;">
      <strong>Permissions Overview:</strong><br>
      • <strong>Article Writers (Journalists):</strong> Only see & write stories in their assigned languages. Can submit drafts for editorial review.<br>
      • <strong>Editors:</strong> Full editorial control across all 3 language editions and classifieds.<br>
      • <strong>Administrators:</strong> Complete access including user accounts, domains, advertisements, and system settings.
    </small>
  </form>
</div>
