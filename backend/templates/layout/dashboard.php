<?php
/**
 * Dashboard Layout - Used for pages that need the three-column dashboard structure
 */
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->fetch('title', 'WeLinked') ?></title>
    <link rel="icon" href="/favicon.ico" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    
    <!-- Vue.js 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <style>
        html,body{height:100%;margin:0;padding:0}
        body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial;background:#fafafa}
        a {text-decoration: none}
        
        /* Birthday Confetti Animation */
        @keyframes confetti-fall {
            0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
        .animate-confetti {
            animation: confetti-fall var(--duration, 3s) linear var(--delay, 0s) infinite;
        }
        
        /* Scale In Animation */
        @keyframes scale-in {
            0% { transform: scale(0.9); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-scale-in {
            animation: scale-in 0.3s ease-out;
        }
        
        /* Float Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-float {
            animation: float 2s ease-in-out infinite;
        }
        
        /* Birthday Gradient Background */
        .birthday-gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        /* Birthday Glow Effect */
        .birthday-glow {
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
        }
        .birthday-glow:hover {
            box-shadow: 0 0 30px rgba(102, 126, 234, 0.7);
        }
    </style>
    
    <?= $this->Html->css('dashboard') ?>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <?= $this->Flash->render() ?>
    
    <script>
        window.csrfToken = '<?= $this->request->getAttribute('csrfToken') ?>';
        window.currentUserId = <?= json_encode($currentUser->id ?? null) ?>;
        window.currentUserPhoto = <?= json_encode($currentUser->profile_photo_path ?? '') ?>;
        window.currentUserInitial = <?= json_encode(strtoupper(substr($currentUser->username ?? 'U', 0, 1))) ?>;
    </script>

    <!-- Desktop / large view navbar -->
    <?php $navHasPhoto = !empty($currentUser->profile_photo_path); ?>
    <?php if (empty($isMobileView)): ?>
<nav class="bg-white shadow-sm fixed top-0 left-0 right-0 z-50 h-16">
    <div class="flex items-center justify-between px-6 h-full max-w-screen-2xl mx-auto">
        <!-- Left: Logo + Search -->
        <div class="flex items-center space-x-6">
            <a href="<?= $this->Url->build('/') ?>" class="flex items-center space-x-2">
                <picture>
                    <source srcset="/assets/logo.avif" type="image/avif">
                    <img src="/assets/logo.png" alt="WeLinked logo" class="w-10 h-10" />
                </picture>
                <span class="text-xl font-bold text-gray-900 hidden sm:block" style="margin-left: -2px;">eLinked</span>
            </a>
            <div class="hidden md:flex items-center bg-gray-100 rounded-full px-4 py-2">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="search" id="global-search-input" placeholder="Search users & posts..." class="bg-transparent border-0 focus:ring-0 focus:outline-none focus:border-transparent text-sm w-64 placeholder-gray-400" autocomplete="off" />
            </div>
        </div>

        <!-- Right: User actions -->
        <div class="flex items-center space-x-4">
            <!-- Notifications Bell -->
            <div class="relative flex items-center space-x-2">
                <button id="notifications-bell" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span id="notifications-badge" class="absolute top-1 right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                </button>
                <span class="text-gray-700 font-medium text-sm">Notification</span>
            </div>
            <!-- Profile Avatar with gradient border -->
            <div class="relative">
                <div class="w-10 h-10 rounded-full p-0.5 bg-gradient-to-tr from-yellow-400 via-pink-500 to-purple-600">
                    <div class="w-full h-full rounded-full bg-white flex items-center justify-center overflow-hidden">
                        <img
                            data-avatar="current-user"
                            src="<?= $navHasPhoto ? h($currentUser->profile_photo_path) : '' ?>"
                            alt="<?= h($currentUser->username ?? 'Profile photo') ?>"
                            class="w-full h-full object-cover <?= $navHasPhoto ? '' : 'hidden' ?>"
                        >
                        <div
                            data-avatar-fallback="current-user"
                            class="w-full h-full rounded-full flex items-center justify-center text-white font-semibold text-sm <?= $navHasPhoto ? 'hidden' : '' ?>"
                        >
                            <span data-user-initial><?= strtoupper(substr($currentUser->username ?? 'U', 0, 1)) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>



<?php else: ?>
<!-- Mobile top bar: logo (eLinked) + right icons -->
<nav class="bg-white border-b fixed top-0 left-0 right-0 z-50 h-14 flex items-center px-4">
    <div class="flex items-center justify-between w-full max-w-screen-2xl mx-auto">
        <a href="<?= $this->Url->build('/') ?>" class="flex items-center space-x-2">
            <picture class="inline-block">
                <source srcset="/assets/logo.avif" type="image/avif">
                <img src="/assets/logo.png" alt="eLinked logo" class="w-8 h-8" />
            </picture>
            <div class="brand-name header-name text-lg font-bold">eLinked</div>
        </a>

        <div class="flex items-center space-x-3">
            <!-- Create icon -->
            <button aria-label="Create" class="p-2 rounded-md text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
            <!-- Search icon -->
            <button aria-label="Search" class="p-2 rounded-md text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
        </div>
    </div>
</nav>
<?php endif; ?>

<!-- Main layout -->
<?php $mobilePad = (!empty($isMobileView) ? 'pt-14 pb-20' : 'pt-20'); ?>
<div class="bg-gray-50 min-h-screen <?= $mobilePad ?>">
    <div class="max-w-screen-2xl mx-auto flex">
        <!-- Left Sidebar -->
        <aside id="left-component" class="hidden lg:block w-72 flex-shrink-0 sticky top-20 h-[calc(100vh-5rem)] overflow-y-auto px-4">
            <?= $this->element('left_sidebar') ?>
        </aside>

        <!-- Center Content Area -->
        <main id="middle-component" class="flex-1 min-w-0 px-4 lg:px-8">
            <?= $this->fetch('content') ?>
        </main>

        <!-- Right Sidebar -->
        <aside id="right-component" class="hidden xl:block w-80 flex-shrink-0 sticky top-20 h-[calc(100vh-5rem)] overflow-y-auto px-4">
            <?= $this->element('right_sidebar') ?>
        </aside>
    </div>
</div>

<?php if (!empty($isMobileView)): ?>
<!-- Mobile bottom navigation -->
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t z-50">
    <div class="max-w-screen-2xl mx-auto px-4">
        <div class="grid grid-cols-5 gap-2 text-center py-2">
            <a href="/dashboard" class="flex flex-col items-center justify-center text-gray-700">
                <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M4 10v10a1 1 0 001 1h3m10-11v10a1 1 0 01-1 1h-3m-6 0h6"/>
                </svg>
                <span class="text-xs leading-tight">Home</span>
            </a>
            <a href="/reels" class="flex flex-col items-center justify-center text-gray-700">
                <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A2 2 0 0122 9.618v4.764a2 2 0 01-2.447 1.894L15 14M4 6h9v12H4z"/>
                </svg>
                <span class="text-xs leading-tight">Reels</span>
            </a>
            <a href="/friends" class="flex flex-col items-center justify-center text-gray-700">
                <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m8-4a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span class="text-xs leading-tight">Friends</span>
            </a>
            <a href="/notifications" class="flex flex-col items-center justify-center text-gray-700">
                <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1"/>
                </svg>
                <span class="text-xs leading-tight">Notifications</span>
            </a>
            <a href="/users/dashboard" class="flex flex-col items-center justify-center text-gray-700">
                <div class="w-6 h-6 rounded-full mb-0.5 overflow-hidden bg-gradient-to-tr from-yellow-400 via-pink-500 to-purple-600 p-[1px]">
                    <div class="w-full h-full rounded-full bg-white flex items-center justify-center overflow-hidden">
                        <img
                            data-avatar="current-user"
                            src="<?= $navHasPhoto ? h($currentUser->profile_photo_path) : '' ?>"
                            alt="<?= h($currentUser->username ?? 'Profile photo') ?>"
                            class="w-full h-full object-cover <?= $navHasPhoto ? '' : 'hidden' ?>"
                        >
                        <div
                            data-avatar-fallback="current-user"
                            class="w-full h-full rounded-full flex items-center justify-center text-white text-xs font-semibold <?= $navHasPhoto ? 'hidden' : '' ?>"
                        >
                            <span data-user-initial><?= strtoupper(substr($currentUser->username ?? 'U', 0, 1)) ?></span>
                        </div>
                    </div>
                </div>
                <span class="text-xs leading-tight">Menu</span>
            </a>
        </div>
    </div>
</nav>
<?php endif; ?>

<!-- Confetti Overlay (only shown during first and second modals) -->
<div id="confettiOverlay" class="hidden pointer-events-none fixed inset-0 z-50 overflow-hidden">
    <!-- Confetti pieces will be generated dynamically -->
</div>

<!-- First Birthday Modal -->
<div id="birthdayCelebrationModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="animate-scale-in mx-4 w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-2xl">
        <div class="animate-float mb-4 flex justify-center">
            <picture>
                <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f973/512.webp" type="image/webp">
                <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f973/512.gif" alt="🥳" width="80" height="80">
            </picture>
        </div>
        <h2 class="mb-1 text-lg font-semibold text-[#3B82F6]">
            Happy Birthday!
        </h2>
        <h1 class="mb-2 text-3xl font-black tracking-tight text-gray-900" id="birthdayUserName"></h1>
        <p class="mb-8 text-sm text-gray-500">
            You're turning <span id="birthdayAgeText"></span> today!
        </p>
        <button
            onclick="closeBirthdayCelebration()"
            class="bg-[#3B82F6] w-full rounded-xl py-4 text-base font-semibold text-white transition-transform hover:scale-[1.02] active:scale-[0.98]">
            Continue
        </button>
    </div>
</div>

<!-- Second Birthday Modal -->
<div id="birthdayMessagesModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="animate-scale-in mx-4 w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-2xl">
        <div class="animate-float mb-4 flex justify-center">
            <picture>
                <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f382/512.webp" type="image/webp">
                <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f382/512.gif" alt="🎂" width="80" height="80">
            </picture>
        </div>
        <h2 class="mb-2 text-2xl font-black text-gray-900">
            It's Your Special Day!
        </h2>
        <p class="mb-8 text-sm text-gray-500">
            Another year of amazing memories awaits you. Enjoy every moment of today. You truly deserve it! 🎉
        </p>
        
        <!-- Message count button (only shown if there are messages) -->
        <div id="messageButtonContainer" class="hidden">
            <a href="/birthday/messages"
                class="bg-[#3B82F6] mb-3 flex w-full items-center justify-center gap-2 rounded-xl py-4 text-base font-semibold text-white transition-transform hover:scale-[1.02] active:scale-[0.98]">
                 View Birthday Message
            </a>
        </div>
        
        <button
            onclick="closeBirthdayMessages()"
            class="w-full rounded-xl py-4 text-gray-500 transition-colors hover:text-gray-900">
            Close
        </button>
    </div>
</div>

<!-- Load JS modules -->
<script src="/js/dashboard.js"></script>
<script src="/js/mentions.js"></script>
<script src="/js/middle.js"></script>
<script src="/js/comments.js"></script>
<script src="/js/reactions.js"></script>
<script src="/js/gallery.js"></script>
<script src="/js/composer-modal.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/post-composer.js"></script>
<script src="/js/search.js"></script>

<!-- Birthday Check Script -->
<script>
// Confetti colors
const CONFETTI_COLORS = ['#667eea', '#764ba2', '#f093fb', '#4facfe'];

// Generate confetti pieces
function generateConfetti() {
    const overlay = document.getElementById('confettiOverlay');
    if (!overlay) return;
    
    overlay.innerHTML = ''; // Clear existing confetti
    
    for (let i = 0; i < 30; i++) {
        const piece = document.createElement('div');
        piece.className = 'animate-confetti absolute ' + (Math.random() > 0.5 ? 'rounded-full' : 'rounded-sm');
        piece.style.left = Math.random() * 100 + '%';
        piece.style.width = (6 + Math.random() * 6) + 'px';
        piece.style.height = (6 + Math.random() * 6) + 'px';
        piece.style.backgroundColor = CONFETTI_COLORS[i % CONFETTI_COLORS.length];
        piece.style.setProperty('--duration', (2 + Math.random() * 3) + 's');
        piece.style.setProperty('--delay', Math.random() * 3 + 's');
        overlay.appendChild(piece);
    }
}

// Check for birthday on dashboard load
async function checkBirthday() {
    // Only check on actual /dashboard route, not when loading other sections
    if (window.location.pathname !== '/dashboard') {
        return;
    }
    
    // Check if already shown in this session
    if (sessionStorage.getItem('birthdayShown') === 'true') {
        return;
    }
    
    try {
        const response = await fetch('/birthday/check-today');
        const result = await response.json();
        
        if (result.success && result.is_birthday) {
            // Mark as shown for this session
            sessionStorage.setItem('birthdayShown', 'true');
            
            // Generate and show confetti
            generateConfetti();
            document.getElementById('confettiOverlay').classList.remove('hidden');
            
            // Update first modal content
            const ordinal = getOrdinalSuffix(result.age);
            document.getElementById('birthdayTitle').textContent = `Happy ${ordinal} Birthday`;
            document.getElementById('birthdayUserName').textContent = result.full_name;
            document.getElementById('birthdayAge').textContent = result.age;
            document.getElementById('birthdayCelebrationModal').classList.remove('hidden');
            
            // Store message count for second modal
            if (result.unread_count > 0) {
                sessionStorage.setItem('birthdayMessageCount', result.unread_count);
            }
        }
    } catch (error) {
        console.error('Error checking birthday:', error);
    }
}

function closeBirthdayCelebration() {
    document.getElementById('birthdayCelebrationModal').classList.add('hidden');
    
    // Small delay to allow first modal to close before showing second
    setTimeout(() => {
        // Check if we need to show messages modal
        const messageCount = sessionStorage.getItem('birthdayMessageCount');
        if (messageCount && parseInt(messageCount) > 0) {
            document.getElementById('messageButtonContainer').classList.remove('hidden');
        } else {
            // Show second modal without messages
            document.getElementById('messageButtonContainer').classList.add('hidden');
        }
        document.getElementById('birthdayMessagesModal').classList.remove('hidden');
    }, 100);
}

function closeBirthdayMessages() {
    document.getElementById('birthdayMessagesModal').classList.add('hidden');
    // Hide confetti when closing second modal
    document.getElementById('confettiOverlay').classList.add('hidden');
}

function getOrdinalSuffix(num) {
    const j = num % 10,
          k = num % 100;
    if (j == 1 && k != 11) {
        return num + "st";
    }
    if (j == 2 && k != 12) {
        return num + "nd";
    }
    if (j == 3 && k != 13) {
        return num + "rd";
    }
    return num + "th";
}

// Run check when page loads
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkBirthday);
} else {
    checkBirthday();
}
</script>
</body>
</html>