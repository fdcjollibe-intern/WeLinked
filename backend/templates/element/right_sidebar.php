<div class="p-4">
    <!-- Sponsored Section -->
    <div class="mb-6">
        <h2 class="text-gray-500 font-semibold text-sm mb-3">Sponsored</h2>
        <div class="space-y-4">
            <div class="flex space-x-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=80&h=80&fit=crop&crop=center" alt="Pizza ad" class="w-16 h-16 rounded-lg object-cover">
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-gray-900">Lebo's Pizza</h3>
                    <p class="text-xs text-gray-500 mt-1">Experience the trendy pizza spot in Palo Alto being called the next big thing.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Birthdays Section -->
    <div class="mb-6">
        <h2 class="text-gray-500 font-semibold text-sm mb-3">Birthdays</h2>
        <div id="birthday-section" class="space-y-2">
            <div class="flex items-center justify-center p-4">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
            </div>
        </div>
    </div>

    <!-- Friend Suggestions Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-gray-500 font-semibold text-sm">Friend Suggestions</h2>
        </div>
        <?php if (isset($suggestions) && !empty($suggestions)): ?>
            <div class="space-y-3">
                <?php foreach ($suggestions as $suggestion): ?>
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            <?php if (!empty($suggestion['profile_photo_path'])): ?>
                                <img src="<?= h($suggestion['profile_photo_path']) ?>" alt="<?= h($suggestion['username']) ?>" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                                    <?= strtoupper(substr($suggestion['username'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-900 font-semibold truncate"><?= h($suggestion['full_name']) ?></p>
                                <?php if ($suggestion['mutual_count'] > 0): ?>
                                    <p class="text-xs text-gray-500"><?= $suggestion['mutual_count'] ?> mutual <?= $suggestion['mutual_count'] === 1 ? 'friend' : 'friends' ?></p>
                                <?php else: ?>
                                    <p class="text-xs text-gray-500 truncate">@<?= h($suggestion['username']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button class="follow-suggestion-btn text-blue-500 hover:text-blue-600 text-xs font-semibold px-3 py-1 flex-shrink-0"
                                data-user-id="<?= $suggestion['id'] ?>"
                                data-username="<?= h($suggestion['username']) ?>">
                            Follow
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-6">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <p class="text-sm text-gray-500">No suggestions available</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
    // Load birthdays on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadBirthdays();
    });

    function loadBirthdays() {
        const birthdaySection = document.getElementById('birthday-section');
        console.log('Loading birthdays for right sidebar...');
        
        fetch('/birthdays/get-sidebar-data', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Birthday fetch response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Birthday data received:', data);
            if (data.success) {
                displayBirthdays(data);
            } else {
                console.warn('Birthday API returned success=false:', data);
                displayNoBirthdays();
            }
        })
        .catch(error => {
            console.error('Error loading birthdays:', error);
            displayNoBirthdays();
        });
    }

    function displayBirthdays(data) {
        const birthdaySection = document.getElementById('birthday-section');
        const { today, upcoming, past } = data;
        
        // Combine all birthdays: past (max 1), today, upcoming (remaining slots up to 3 total)
        const allBirthdays = [];
        
        // Add past birthdays (show most recent one)
        if (past && past.length > 0) {
            allBirthdays.push(past[0]); // Most recent past birthday
        }
        
        // Add today's birthdays
        if (today && today.length > 0) {
            allBirthdays.push(...today);
        }
        
        // Add upcoming birthdays to fill remaining slots
        if (upcoming && upcoming.length > 0) {
            const remainingSlots = 3 - allBirthdays.length;
            if (remainingSlots > 0) {
                allBirthdays.push(...upcoming.slice(0, remainingSlots));
            }
        }
        
        if (allBirthdays.length === 0) {
            displayNoBirthdays();
            return;
        }

        let html = '';
        
        allBirthdays.forEach(birthday => {
            const user = birthday.user;
            const daysAway = birthday.daysAway || 0;
            const daysAgo = birthday.daysAgo || 0;
            
            let timeText = '';
            if (daysAgo > 0) {
                // Past birthday
                if (daysAgo === 1) {
                    timeText = 'Yesterday';
                } else {
                    timeText = `${daysAgo} days ago`;
                }
            } else if (daysAway === 0) {
                // Today
                timeText = 'Today';
            } else if (daysAway === 1) {
                // Tomorrow
                timeText = 'Tomorrow';
            } else {
                // Future
                timeText = `${daysAway} days`;
            }
            
            const profileImage = user.profile_photo_path ? 
                `<img src="${user.profile_photo_path}" alt="${user.full_name}" class="w-8 h-8 rounded-full object-cover">` :
                `<div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-semibold">
                    ${user.full_name.charAt(0).toUpperCase()}
                </div>`;
                
            // Different icons for past vs future birthdays
            const birthdayIcon = daysAgo > 0 ? 
                `<svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>` :
                `<svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>`;
            
            const ageText = daysAgo > 0 ? `Turned ${birthday.age}` : `Age ${birthday.age}`;
                
            html += `
                <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer" onclick="window.location.href='/birthdays/list'">
                    ${profileImage}
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">
                            <span class="font-semibold">${user.full_name}</span>
                        </p>
                        <p class="text-xs text-gray-500">${timeText} • ${ageText}</p>
                    </div>
                    ${birthdayIcon}
                </div>
            `;
        });
        
        // Add "See all" link if there are more birthdays
        if (data.total > allBirthdays.length) {
            html += `
                <div class="text-center pt-2">
                    <a href="/birthdays/list" class="text-xs text-blue-500 hover:text-blue-600 font-semibold">
                        See all ${data.total} birthdays
                    </a>
                </div>
            `;
        }
        
        birthdaySection.innerHTML = html;
    }

    function displayNoBirthdays() {
        const birthdaySection = document.getElementById('birthday-section');
        birthdaySection.innerHTML = `
            <div class="flex items-center justify-center p-4 text-gray-500">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-sm">No birthdays</span>
            </div>
        `;
    }

    // Handle follow button clicks
    document.addEventListener('click', function(e) {
        const followBtn = e.target.closest('.follow-suggestion-btn');
        if (!followBtn) return;
        
        const userId = followBtn.dataset.userId;
        const username = followBtn.dataset.username;
        
        followBtn.disabled = true;
        followBtn.textContent = 'Following...';
        
        fetch('/friends/follow', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ user_id: parseInt(userId) })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Remove suggestion from list
                const suggestionCard = followBtn.closest('.hover\\:bg-gray-50');
                if (suggestionCard) {
                    suggestionCard.style.transition = 'all 0.3s ease';
                    suggestionCard.style.opacity = '0';
                    suggestionCard.style.transform = 'scale(0.95)';
                    setTimeout(() => suggestionCard.remove(), 300);
                }
            } else {
                alert(data.message || 'Failed to follow user');
                followBtn.disabled = false;
                followBtn.textContent = 'Follow';
            }
        })
        .catch(error => {
            console.error('Follow error:', error);
            alert('An error occurred while following');
            followBtn.disabled = false;
            followBtn.textContent = 'Follow';
        });
    });
    </script>
</div>
