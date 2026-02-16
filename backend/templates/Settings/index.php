<?php
/**
 * Settings partial view wrapper
 * When accessed directly, we re-use the element so AJAX and full renders stay in sync.
 */

echo $this->element('Settings/settings_panel', [
    'user' => $user,
    'activeSection' => $activeSection ?? 'account',
    'isMobileView' => $isMobileView ?? false,
]);
