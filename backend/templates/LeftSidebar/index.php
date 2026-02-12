<?php
// Left sidebar component template
/** @var \App\Model\Entity\User|null $currentUser */
?>
<div class="left-sidebar">
    <div class="brand"> <strong>WeLinked</strong> </div>
    <nav class="menu">
        <ul>
            <li>Home</li>
            <li>🔍 Search</li>
            <li>✚ Create</li>
            <li>🔔 Activity</li>
            <li>👤 Profile</li>
        </ul>
    </nav>

    <div style="height:16px"></div>
    <hr />

    <nav class="menu-bottom">
        <ul>
            <li>⚙️ Settings</li>
            <li style="color:var(--danger)">🚪 Logout</li>
        </ul>
    </nav>
</div>
