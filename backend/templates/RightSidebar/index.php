<?php
/**
 * Right sidebar component: profile, suggested users, footer
 * Variables: $suggested, $currentUser
 */
?>
<div class="right-sidebar">
    <div class="profile-row" style="display:flex;gap:8px;align-items:center">
        <div class="avatar"></div>
        <div>
            <div class="username"><?= h($currentUser->username ?? 'username') ?></div>
            <div class="fullname" style="font-size:13px;color:var(--muted)"><?= h($currentUser->fullname ?? '') ?></div>
        </div>
    </div>

    <div style="margin-top:16px;display:flex;justify-content:space-between;align-items:center">
        <strong>Suggested for you</strong>
        <a href="#">See All</a>
    </div>

    <ul class="suggested-list">
        <?php if (!empty($suggested)): ?>
            <?php foreach ($suggested as $u): ?>
                <li style="display:flex;justify-content:space-between;align-items:center;gap:8px;padding:8px 0">
                    <div style="display:flex;gap:8px;align-items:center">
                        <div class="avatar-sm"></div>
                        <div>
                            <div><?= h($u->username) ?></div>
                            <div style="font-size:12px;color:var(--muted)"><?= h($u->fullname ?? '') ?></div>
                        </div>
                    </div>
                    <div>
                        <button class="follow-btn">Follow</button>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No suggestions yet</li>
        <?php endif; ?>
    </ul>

    <small style="display:block;margin-top:16px;color:var(--muted)">© WeLinked</small>
</div>
