<?php
/**
 * Right sidebar component: profile, birthdays, suggested users, footer
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

    <!-- Birthdays Section -->
    <!-- DEBUG: NEW VERSION LOADED - <?= date('Y-m-d H:i:s') ?> -->
    <a href="/birthday" style="margin-top:20px;padding:12px;background:#3B82F6;border-radius:12px;cursor:pointer;display:block;text-decoration:none;transition:transform 0.2s;" onmouseover="console.log('Birthday hover'); this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:32px;">🎂</span>
                <div>
                    <div style="color:white;font-weight:600;font-size:14px;">Birthdays</div>
                    <div style="color:rgba(255,255,255,0.9);font-size:12px;" id="birthday-count">Loading...</div>
                </div>
            </div>
            <svg style="width:20px;height:20px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>

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

<script>
console.log('%c🎂 RIGHT SIDEBAR LOADED - NEW VERSION <?= date("Y-m-d H:i:s") ?>', 'background: #3B82F6; color: white; padding: 8px; font-weight: bold; font-size: 14px;');

// Load birthday count on page load
(function() {
    console.log('=== RIGHT SIDEBAR: Birthday count loader started ===');
    console.log('Fetching from: /birthdays/get-count');
    
    fetch('/birthdays/get-count')
        .then(response => {
            console.log('Birthday count response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Birthday count data:', data);
            if (data.success) {
                const countEl = document.getElementById('birthday-count');
                console.log('Birthday count element:', countEl);
                if (data.count === 0) {
                    countEl.textContent = 'No upcoming birthdays';
                } else if (data.count === 1) {
                    countEl.textContent = '1 birthday this week';
                } else {
                    countEl.textContent = data.count + ' birthdays this week';
                }
                console.log('Birthday count updated to:', countEl.textContent);
            }
        })
        .catch(err => {
            console.error('Error loading birthday count:', err);
            document.getElementById('birthday-count').textContent = 'Click to view';
        });
    
    console.log('=== RIGHT SIDEBAR: Checking for old modal element ===');
    const oldModal = document.getElementById('birthday-modal');
    if (oldModal) {
        console.warn('⚠️ OLD BIRTHDAY MODAL STILL EXISTS!', oldModal);
    } else {
        console.log('✅ No old birthday modal found - clean!');
    }
})();
</script>
