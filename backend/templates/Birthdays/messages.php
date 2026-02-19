<!-- Birthday Messages Middle Column Content -->
<div class="bg-white rounded-lg shadow">
    <!-- Birthday Navigation Tabs -->
    <div class="border-b border-gray-200">
        <div class="flex space-x-4 px-6">
            <a href="/birthday" class="py-4 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium transition">
                <?= __('Birthday List') ?>
            </a>
            <a href="/birthday/sent" class="py-4 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium transition">
                <?= __('Sent Messages') ?>
            </a>
            <a href="/birthday/messages" class="py-4 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">
                <?= __('Birthday Messages') ?>
            </a>
        </div>
    </div>

    <!-- Birthday Messages Content -->
    <div class="p-6">
        <?php if (!empty($messages)): ?>
            <div class="mb-6">
                <h1 class="text-2xl font-black text-gray-900 mb-1">
                    Birthday Messages 🎁
                </h1>
                <p class="text-sm text-gray-500">
                    <?= count($messages) ?> message<?= count($messages) !== 1 ? 's' : '' ?> from your followers
                </p>
            </div>
            
            <div class="space-y-3">
                <?php foreach ($messages as $message): ?>
                    <button
                        data-message-id="<?= $message->id ?>"
                        onclick="openMessageDetail(<?= h(json_encode([
                            'messageId' => $message->id,
                            'isRead' => (bool)$message->is_read,
                            'senderName' => $message->sender->full_name,
                            'senderUsername' => '@' . $message->sender->username,
                            'senderPhoto' => $message->sender->profile_photo_path ?: '/assets/default-avatar.png',
                            'message' => $message->message
                        ])) ?>)"
                        class="flex w-full items-center gap-4 rounded-xl <?= !$message->is_read ? 'border border-[#3B82F6]' : 'border border-gray-200' ?> bg-white p-4 text-left transition-all hover:border-blue-300 hover:shadow-md active:scale-[0.98]">
                        <img
                            src="<?= h($message->sender->profile_photo_path ?: '/assets/default-avatar.png') ?>"
                            alt="<?= h($message->sender->full_name) ?>"
                            class="h-12 w-12 flex-shrink-0 rounded-full object-cover"/>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm <?= !$message->is_read ? 'font-bold' : 'font-semibold' ?> text-gray-900">
                                    <?= h($message->sender->full_name) ?>
                                </h3>
                                <?php if ($message->is_read): ?>
                                    <span class="text-xs font-bold text-green-600">• Seen</span>
                                <?php endif; ?>
                            </div>
                            <p class="truncate text-xs text-gray-500 <?= !$message->is_read ? 'font-semibold' : '' ?>">
                                <?= h($message->message) ?>
                            </p>
                        </div>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <span class="material-symbols-outlined text-6xl text-gray-300">cake</span>
                <p class="mt-4 text-gray-600"><?= __('No birthday messages yet') ?></p>
                <p class="text-sm text-gray-500 mt-2"><?= __('Birthday wishes from your followers will appear here') ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Message Detail Modal -->
<div id="messageDetailModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeMessageDetail()">
    <div class="animate-scale-in relative mx-4 w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-8 text-center shadow-2xl" onclick="event.stopPropagation()">
        <button
            onclick="closeMessageDetail()"
            class="absolute right-4 top-4 rounded-full p-1 text-gray-400 transition-colors hover:text-gray-900">
            ✕
        </button>
        <img
            id="detailSenderPhoto"
            alt="Sender"
            class="mx-auto mb-4 h-20 w-20 rounded-full border-4 border-purple-100 object-cover"/>
        <h3 id="detailSenderName" class="text-lg font-bold text-gray-900"></h3>
        <p id="detailSenderUsername" class="mb-5 text-sm text-gray-500"></p>
        <p id="detailMessage" class="text-xl font-bold leading-relaxed text-gray-900 whitespace-pre-wrap break-words"></p>
    </div>
</div>

<script>
// Open message detail modal
async function openMessageDetail(messageData) {
    // Show the modal immediately
    document.getElementById('detailSenderPhoto').src = messageData.senderPhoto;
    document.getElementById('detailSenderName').textContent = messageData.senderName;
    document.getElementById('detailSenderUsername').textContent = messageData.senderUsername;
    document.getElementById('detailMessage').textContent = '"' + messageData.message + '"';
    document.getElementById('messageDetailModal').classList.remove('hidden');
    
    // Mark as read if not already read (in background)
    if (!messageData.isRead) {
        try {
            const response = await fetch(`/birthday/mark-as-read/${messageData.messageId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            if (response.ok) {
                // Refresh the badge count
                if (typeof refreshBirthdayMessageBadge === 'function') {
                    refreshBirthdayMessageBadge();
                }
                
                // Update the UI without reloading
                // Find only the specific button that was clicked
                const button = document.querySelector('button[data-message-id="' + messageData.messageId + '"]');
                if (button) {
                    // Change border from blue to gray
                    button.classList.remove('border', 'border-[#3B82F6]');
                    button.classList.add('border', 'border-gray-200');
                    
                    // Update font weight
                    const nameElement = button.querySelector('h3');
                    if (nameElement) {
                        nameElement.classList.remove('font-bold');
                        nameElement.classList.add('font-semibold');
                    }
                    
                    // Remove bold from message text
                    const messageElement = button.querySelector('p');
                    if (messageElement) {
                        messageElement.classList.remove('font-semibold');
                    }
                    
                    // Add "Seen" badge
                    const nameContainer = button.querySelector('.flex.items-center.gap-2');
                    if (nameContainer && !nameContainer.querySelector('.text-green-600')) {
                        const seenBadge = document.createElement('span');
                        seenBadge.className = 'text-xs font-bold text-green-600';
                        seenBadge.textContent = '• Seen';
                        nameContainer.appendChild(seenBadge);
                    }
                }
            }
        } catch (error) {
            console.error('Error marking message as read:', error);
        }
    }
}

// Close message detail modal
function closeMessageDetail() {
    document.getElementById('messageDetailModal').classList.add('hidden');
}

// Refresh the birthday message badge after viewing messages
if (typeof window.refreshBirthdayMessageBadge === 'function') {
    window.refreshBirthdayMessageBadge();
}
</script>
