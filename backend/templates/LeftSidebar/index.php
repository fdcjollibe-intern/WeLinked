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
            <li>
                <a href="/logout" class="btn-logout-sidebar" aria-label="Logout">🚪 Logout</a>
            </li>
        </ul>
    </nav>

    <style>
    .btn-logout-sidebar {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 6px;
        background: #fff5f5;
        color: #c53030;
        text-decoration: none;
        border: 1px solid #fed7d7;
        font-weight: 600;
        font-size: 14px;
    }
    .btn-logout-sidebar:hover { background: #fff1f1; }
    </style>
</div>
