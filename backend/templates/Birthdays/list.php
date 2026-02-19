<!-- Birthday List Middle Column Content -->
<div class="bg-white rounded-lg shadow">
    <!-- Birthday Navigation Tabs -->
    <div class="border-b border-gray-200">
        <div class="flex space-x-4 px-6">
            <a href="/birthday" class="py-4 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">
                <?= __('Birthday List') ?>
            </a>
            <a href="/birthday/sent" class="py-4 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium transition">
                <?= __('Sent Messages') ?>
            </a>
            <a href="/birthday/messages" class="py-4 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium transition">
                <?= __('Birthday Messages') ?>
            </a>
        </div>
    </div>

    <!-- Birthday List Content -->
    <div class="p-6">
        <?php if (!empty($birthdays['upcoming']) || !empty($birthdays['past'])): ?>
            <!-- Upcoming Birthdays -->
            <?php if (!empty($birthdays['upcoming'])): ?>
                <div class="mb-6">
                    <h3 class="text-lg font-bold mb-4 text-gray-900"><?= __('Upcoming Birthdays') ?></h3>
                    <div class="space-y-3">
                        <?php foreach ($birthdays['upcoming'] as $birthday): ?>
                            <?php $user = $birthday['user']; ?>
                            <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-gradient-to-r from-blue-50 to-purple-50 p-4 transition-all hover:border-blue-300 hover:shadow-md">
                                <a href="/profile/<?= h($user->username) ?>" class="flex-shrink-0">
                                    <img src="<?= h($user->profile_photo_path ?: '/assets/default-avatar.png') ?>" 
                                         alt="<?= h($user->full_name) ?>" 
                                         class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm">
                                </a>
                                <div class="flex-1 min-w-0">
                                    <a href="/profile/<?= h($user->username) ?>" class="font-bold text-gray-900 hover:text-purple-600 transition">
                                        <?= h($user->full_name) ?>
                                    </a>
                                    <div class="text-sm font-medium">
                                        <?php if ($birthday['daysAway'] == 0): ?>
                                            <span class="text-green-600">🎉 <?= __('Today!') ?></span>
                                            <span class="text-gray-600"> • Turning <?= h($birthday['age']) ?></span>
                                        <?php elseif ($birthday['daysAway'] == 1): ?>
                                            <span class="text-purple-600"><?= __('Tomorrow') ?></span>
                                            <span class="text-gray-600"> • Turning <?= h($birthday['age']) ?></span>
                                        <?php else: ?>
                                            <span class="text-blue-600"><?= __('In {0} days', $birthday['daysAway']) ?></span>
                                            <span class="text-gray-600"> • Turning <?= h($birthday['age']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs text-gray-500 font-medium">
                                        <?= $birthday['date']->format('F j') ?>
                                    </div>
                                </div>
                                <button onclick="openSendMessageModal(<?= h($user->id) ?>, '<?= h($user->full_name) ?>')" 
                                        class="bg-[#3B82F6] flex-shrink-0 px-4 py-2 text-white rounded-lg font-semibold transition-transform hover:scale-105 active:scale-95 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">mail</span>
                                    <span><?= __('Send') ?></span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Past Birthdays -->
            <?php if (!empty($birthdays['past'])): ?>
                <div>
                    <h3 class="text-lg font-bold mb-4 text-gray-900"><?= __('Recent Birthdays') ?></h3>
                    <div class="space-y-3">
                        <?php foreach ($birthdays['past'] as $birthday): ?>
                            <?php $user = $birthday['user']; ?>
                            <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 transition-all hover:border-gray-300 hover:shadow-md">
                                <a href="/profile/<?= h($user->username) ?>" class="flex-shrink-0">
                                    <img src="<?= h($user->profile_photo_path ?: '/assets/default-avatar.png') ?>" 
                                         alt="<?= h($user->full_name) ?>" 
                                         class="w-12 h-12 rounded-full object-cover border-2 border-gray-100 shadow-sm">
                                </a>
                                <div class="flex-1 min-w-0">
                                    <a href="/profile/<?= h($user->username) ?>" class="font-bold text-gray-900 hover:text-gray-700 transition">
                                        <?= h($user->full_name) ?>
                                    </a>
                                    <div class="text-sm font-medium text-gray-600">
                                        <?php if ($birthday['daysAgo'] == 1): ?>
                                            <?= __('Yesterday') ?>
                                            <span> • Turned <?= h($birthday['age']) ?></span>
                                        <?php else: ?>
                                            <?= __('({0} days ago)', $birthday['daysAgo']) ?>
                                            <span> • Turned <?= h($birthday['age']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs text-gray-500 font-medium">
                                        <?= $birthday['date']->format('F j') ?>
                                    </div>
                                </div>
                                <button onclick="openSendMessageModal(<?= h($user->id) ?>, '<?= h($user->full_name) ?>')" 
                                        class="flex-shrink-0 px-4 py-2 bg-gray-600 text-white rounded-lg font-semibold transition-all hover:bg-gray-700 active:scale-95 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">mail</span>
                                    <span><?= __('Send') ?></span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-12">
                <span class="material-symbols-outlined text-6xl text-gray-300">cake</span>
                <p class="mt-4 text-gray-600"><?= __('No birthdays from people you follow') ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Send Birthday Message Modal -->
<div id="sendMessageModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="animate-scale-in bg-white rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto mx-4 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-black text-gray-900">
                 <?= __('Send Birthday Wishes') ?> 🎂
            </h3>
            <button onclick="closeSendMessageModal()" class="text-gray-400 hover:text-gray-900 transition rounded-full p-1">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <!-- Info Banner -->
        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <span class="material-symbols-outlined text-blue-600">info</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-blue-900 font-medium mb-1">
                        <?= __('Important Information') ?>
                    </p>
                    <p class="text-xs text-blue-700">
                        <?= __('You can only edit or delete your birthday message within 5 minutes after sending it.') ?>
                    </p>
                </div>
            </div>
        </div>
        
        <form id="sendMessageForm" onsubmit="sendBirthdayMessage(event)">
            <input type="hidden" id="recipientId" name="recipient_id">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <?= __('To: ') ?><span id="recipientName" class="text-[#3B82F6]"></span>
                </label>
                <textarea 
                    id="messageText" 
                    name="message" 
                    rows="6" 
                    maxlength="500"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                    placeholder="<?= __('Write your birthday wishes...') ?>"
                    required></textarea>
                <div class="text-right text-xs text-gray-500 mt-1 font-medium">
                    <span id="charCount">0</span>/500
                </div>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="closeSendMessageModal()" 
                        class="flex-1 px-4 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition">
                    <?= __('Cancel') ?>
                </button>
                <button type="submit" 
                        class="bg-[#3B82F6] flex-1 px-4 py-3 text-white rounded-xl font-semibold hover:scale-105 active:scale-95 transition-transform">
                    <?= __('Send') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="animate-scale-in bg-white rounded-2xl p-8 w-full max-w-sm mx-4 shadow-2xl text-center">
        <div class="mb-4">
            <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined text-4xl text-green-600">check_circle</span>
            </div>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-2">
            <?= __('Message Sent!') ?>
        </h3>
        <p class="text-gray-600 mb-6">
            <?= __('Your birthday wishes have been sent successfully.') ?>
        </p>
        <button
            onclick="closeSuccessModal()"
            class="bg-[#3B82F6] w-full py-3 text-white rounded-xl font-semibold hover:scale-105 active:scale-95 transition-transform">
            <?= __('Great!') ?>
        </button>
    </div>
</div>

<script>
function openSendMessageModal(userId, userName) {
    document.getElementById('recipientId').value = userId;
    document.getElementById('recipientName').textContent = userName;
    document.getElementById('messageText').value = '';
    document.getElementById('charCount').textContent = '0';
    document.getElementById('sendMessageModal').classList.remove('hidden');
}

function closeSendMessageModal() {
    document.getElementById('sendMessageModal').classList.add('hidden');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    location.reload(); // Reload to show updated data
}

document.getElementById('messageText')?.addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
});

async function sendBirthdayMessage(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/birthday/send-message', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.csrfToken || ''
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeSendMessageModal();
            document.getElementById('successModal').classList.remove('hidden');
            // Refresh birthday message badge in case user sent themselves a message
            if (typeof window.refreshBirthdayMessageBadge === 'function') {
                window.refreshBirthdayMessageBadge();
            }
        } else {
            alert('Failed to send message: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error sending message:', error);
        alert('An error occurred while sending the message');
    }
}
</script>
