<!-- Sent Messages Middle Column Content -->
<div class="bg-white rounded-lg shadow">
    <!-- Birthday Navigation Tabs -->
    <div class="border-b border-gray-200">
        <div class="flex space-x-4 px-6">
            <a href="/birthday" class="py-4 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium transition">
                <?= __('Birthday List') ?>
            </a>
            <a href="/birthday/sent" class="py-4 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">
                <?= __('Sent Messages') ?>
            </a>
            <a href="/birthday/messages" class="py-4 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium transition">
                <?= __('Birthday Messages') ?>
            </a>
        </div>
    </div>

    <!-- Sent Messages Content -->
    <div class="p-6">
        <?php if (!empty($messages)): ?>
            <div class="mb-6">
                <h1 class="text-2xl font-black text-gray-900 mb-1">
                    Sent Messages 💌
                </h1>
                <p class="text-sm text-gray-500">
                    <?= count($messages) ?> message<?= count($messages) !== 1 ? 's' : '' ?> sent to your followings
                </p>
            </div>
            <div class="space-y-3">
                <?php foreach ($messages as $message): ?>
                    <?php
                    // Check if 5 minutes have passed since message was sent
                    $now = new \DateTime();
                    $createdAt = $message->created_at;
                    $diffInMinutes = ($now->getTimestamp() - $createdAt->getTimestamp()) / 60;
                    $canEdit = $diffInMinutes <= 5;
                    
                    // Truncate message if longer than 80 characters
                    $displayMessage = mb_strlen($message->message) > 80 
                        ? mb_substr($message->message, 0, 80) . '...' 
                        : $message->message;
                    $isLongMessage = mb_strlen($message->message) > 80;
                    ?>
                    <div class="rounded-xl border border-gray-200 bg-white p-4 transition-all hover:border-blue-300 hover:shadow-md">
                        <div class="flex items-start gap-3">
                            <a href="/profile/<?= h($message->recipient->username) ?>" class="flex-shrink-0" onclick="event.stopPropagation()">
                                <img src="<?= h($message->recipient->profile_photo_path ?: '/assets/default-avatar.png') ?>" 
                                     alt="<?= h($message->recipient->full_name) ?>" 
                                     class="w-12 h-12 rounded-full object-cover border-2 border-gray-100 shadow-sm">
                            </a>
                            <button 
                                onclick="openSentMessageDetail(<?= h(json_encode([
                                    'recipientName' => $message->recipient->full_name,
                                    'recipientUsername' => '@' . $message->recipient->username,
                                    'recipientPhoto' => $message->recipient->profile_photo_path ?: '/assets/default-avatar.png',
                                    'message' => $message->message,
                                    'createdAt' => $message->created_at->timeAgoInWords(),
                                    'isRead' => (bool)$message->is_read
                                ])) ?>)"
                                class="flex-1 min-w-0 text-left">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-gray-900 hover:text-purple-600 transition">
                                        <?= h($message->recipient->full_name) ?>
                                    </span>
                                    <?php if ($message->is_read): ?>
                                        <span class="text-xs font-bold text-green-600">• Seen</span>
                                    <?php endif; ?>
                                    <span class="text-xs text-gray-400 font-medium">
                                        <?= $message->created_at->timeAgoInWords() ?>
                                    </span>
                                </div>
                                <p class="text-gray-700 text-sm"><?= h($displayMessage) ?></p>
                            </button>
                            <div class="flex gap-1 flex-shrink-0" onclick="event.stopPropagation()">
                                <button onclick="editMessage(<?= $message->id ?>, '<?= h(addslashes($message->message)) ?>')" 
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors <?= !$canEdit ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>"
                                        title="<?= $canEdit ? __('Edit') : __('Cannot edit after 5 minutes') ?>"
                                        <?= !$canEdit ? 'disabled' : '' ?>>
                                    <span class="material-symbols-outlined text-base">edit</span>
                                </button>
                                <button onclick="deleteMessage(<?= $message->id ?>)" 
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors <?= !$canEdit ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>"
                                        title="<?= $canEdit ? __('Delete') : __('Cannot delete after 5 minutes') ?>"
                                        <?= !$canEdit ? 'disabled' : '' ?>>
                                    <span class="material-symbols-outlined text-base">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <span class="material-symbols-outlined text-6xl text-gray-300">mail</span>
                <p class="mt-4 text-gray-600 font-semibold">You haven't sent birthday messages</p>
                <p class="text-sm text-gray-500 mt-2">Send birthday wishes to people you follow</p>
                <a href="/birthday" class="inline-block mt-4 px-6 py-3 bg-[#3B82F6] text-white rounded-xl font-semibold transition-transform hover:scale-105 active:scale-95">
                    <?= __('View Birthdays') ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Message Detail Modal (for long messages) -->
<div id="sentMessageDetailModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeSentMessageDetail()">
    <div class="animate-scale-in relative mx-4 w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-8 text-center shadow-2xl" onclick="event.stopPropagation()">
        <button
            onclick="closeSentMessageDetail()"
            class="absolute right-4 top-4 rounded-full p-1 text-gray-400 transition-colors hover:text-gray-900">
            ✕
        </button>
        <img
            id="sentDetailRecipientPhoto"
            alt="Recipient"
            class="mx-auto mb-4 h-20 w-20 rounded-full border-4 border-purple-100 object-cover"/>
        <h3 id="sentDetailRecipientName" class="text-lg font-bold text-gray-900"></h3>
        <p id="sentDetailRecipientUsername" class="mb-2 text-sm text-gray-500"></p>
        <div id="sentDetailSeenBadge" class="hidden mb-2">
            <span class="text-sm font-bold text-green-600">✓ Seen</span>
        </div>
        <p id="sentDetailCreatedAt" class="mb-4 text-xs text-gray-400"></p>
        <p id="sentDetailMessage" class="text-xl font-bold leading-relaxed text-gray-900 whitespace-pre-wrap break-words"></p>
    </div>
</div>

<!-- Edit Message Modal -->
<div id="editMessageModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="animate-scale-in bg-white rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto mx-4 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-black text-gray-900">
                <?= __('Edit Birthday Message') ?>
            </h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-900 transition rounded-full p-1">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="editMessageForm" onsubmit="submitEditMessage(event)">
            <input type="hidden" id="editMessageId">
            <div class="mb-4">
                <textarea 
                    id="editMessageText" 
                    rows="6" 
                    maxlength="500"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                    required></textarea>
                <div class="text-right text-xs text-gray-500 mt-1 font-medium">
                    <span id="editCharCount">0</span>/500
                </div>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="closeEditModal()" 
                        class="flex-1 px-4 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition">
                    <?= __('Cancel') ?>
                </button>
                <button type="submit" 
                        class="bg-[#3B82F6] flex-1 px-4 py-3 text-white rounded-xl font-semibold hover:scale-105 active:scale-95 transition-transform">
                    <?= __('Update') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal (for edit/delete) -->
<div id="successModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="animate-scale-in bg-white rounded-2xl p-8 w-full max-w-sm mx-4 shadow-2xl text-center">
        <div class="mb-4">
            <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined text-4xl text-green-600">check_circle</span>
            </div>
        </div>
        <h3 id="successModalTitle" class="text-2xl font-black text-gray-900 mb-2">
            <?= __('Success!') ?>
        </h3>
        <p id="successModalMessage" class="text-gray-600 mb-6">
            <?= __('Action completed successfully.') ?>
        </p>
        <button
            onclick="closeSuccessModal()"
            class="bg-[#3B82F6] w-full py-3 text-white rounded-xl font-semibold hover:scale-105 active:scale-95 transition-transform">
            <?= __('OK') ?>
        </button>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="animate-scale-in bg-white rounded-2xl p-8 w-full max-w-sm mx-4 shadow-2xl text-center">
        <div class="mb-4">
            <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined text-4xl text-red-600">warning</span>
            </div>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-2">
            <?= __('Delete Message?') ?>
        </h3>
        <p class="text-gray-600 mb-6">
            <?= __('Are you sure you want to delete this birthday message? This action cannot be undone.') ?>
        </p>
        <div class="flex gap-3">
            <button
                onclick="closeDeleteConfirmModal()"
                class="flex-1 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition">
                <?= __('Cancel') ?>
            </button>
            <button
                onclick="confirmDelete()"
                class="flex-1 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition">
                <?= __('Delete') ?>
            </button>
        </div>
    </div>
</div>

<script>
// Open sent message detail modal (for long messages)
function openSentMessageDetail(messageData) {
    document.getElementById('sentDetailRecipientPhoto').src = messageData.recipientPhoto;
    document.getElementById('sentDetailRecipientName').textContent = messageData.recipientName;
    document.getElementById('sentDetailRecipientUsername').textContent = messageData.recipientUsername;
    document.getElementById('sentDetailCreatedAt').textContent = 'Sent ' + messageData.createdAt;
    document.getElementById('sentDetailMessage').textContent = '"' + messageData.message + '"';
    
    // Show or hide the "Seen" badge
    const seenBadge = document.getElementById('sentDetailSeenBadge');
    if (messageData.isRead) {
        seenBadge.classList.remove('hidden');
    } else {
        seenBadge.classList.add('hidden');
    }
    
    document.getElementById('sentMessageDetailModal').classList.remove('hidden');
}

// Close sent message detail modal
function closeSentMessageDetail() {
    document.getElementById('sentMessageDetailModal').classList.add('hidden');
}

// Success modal functions
function showSuccessModal(title, message) {
    document.getElementById('successModalTitle').textContent = title;
    document.getElementById('successModalMessage').textContent = message;
    document.getElementById('successModal').classList.remove('hidden');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    location.reload();
}

// Delete confirmation modal functions
let pendingDeleteId = null;

function closeDeleteConfirmModal() {
    document.getElementById('deleteConfirmModal').classList.add('hidden');
    pendingDeleteId = null;
}

async function confirmDelete() {
    if (!pendingDeleteId) return;
    
    closeDeleteConfirmModal();
    
    try {
        const response = await fetch(`/birthday/delete-message/${pendingDeleteId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.csrfToken || ''
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccessModal('Message Deleted!', 'Your birthday message has been deleted successfully.');
        } else {
            alert('Failed to delete message: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error deleting message:', error);
        alert('An error occurred while deleting the message');
    }
}

function editMessage(messageId, messageText) {
    document.getElementById('editMessageId').value = messageId;
    document.getElementById('editMessageText').value = messageText;
    document.getElementById('editCharCount').textContent = messageText.length;
    document.getElementById('editMessageModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editMessageModal').classList.add('hidden');
}

document.getElementById('editMessageText')?.addEventListener('input', function() {
    document.getElementById('editCharCount').textContent = this.value.length;
});

async function submitEditMessage(event) {
    event.preventDefault();
    
    const messageId = document.getElementById('editMessageId').value;
    const message = document.getElementById('editMessageText').value;
    
    const formData = new FormData();
    formData.append('message', message);
    
    try {
        const response = await fetch(`/birthday/edit-message/${messageId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.csrfToken || ''
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeEditModal();
            showSuccessModal('Message Updated!', 'Your birthday message has been updated successfully.');
        } else {
            alert('Failed to update message: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error updating message:', error);
        alert('An error occurred while updating the message');
    }
}

async function deleteMessage(messageId) {
    pendingDeleteId = messageId;
    document.getElementById('deleteConfirmModal').classList.remove('hidden');
}
</script>
