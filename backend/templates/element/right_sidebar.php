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
        <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
            <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12,6A3,3 0 0,0 9,9A3,3 0 0,0 12,12A3,3 0 0,0 15,9A3,3 0 0,0 12,6M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z"/></svg>
            <div class="flex-1">
                <p class="text-sm text-gray-900">
                    <span class="font-semibold">Jessica, Erica</span> and <span class="font-semibold">2 others</span> have birthdays today.
                </p>
            </div>
        </div>
    </div>

    <!-- Contacts Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-gray-500 font-semibold text-sm">Contacts</h2>
            <div class="flex items-center space-x-2">
                <button class="p-1 hover:bg-gray-100 rounded">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
                <button class="p-1 hover:bg-gray-100 rounded">
                    <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 24 24"><path d="M16,12A2,2 0 0,1 18,10A2,2 0 0,1 20,12A2,2 0 0,1 18,14A2,2 0 0,1 16,12M10,12A2,2 0 0,1 12,10A2,2 0 0,1 14,12A2,2 0 0,1 12,14A2,2 0 0,1 10,12M4,12A2,2 0 0,1 6,10A2,2 0 0,1 8,12A2,2 0 0,1 6,14A2,2 0 0,1 4,12Z"/></svg>
                </button>
            </div>
        </div>
        <div class="space-y-2">
            <?php if (!empty($suggested)): ?>
                <?php foreach ($suggested as $u): ?>
                    <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                        <div class="relative">
                            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold text-sm"><?= strtoupper(substr($u->username ?? 'U', 0, 1)) ?></div>
                            <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></div>
                        </div>
                        <span class="text-sm text-gray-900 font-medium"><?= h($u->username) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Additional contacts -->
            <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                <div class="relative">
                    <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white font-semibold text-sm">D</div>
                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></div>
                </div>
                <span class="text-sm text-gray-900 font-medium">Dennis Han</span>
            </div>
            <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                <div class="relative">
                    <div class="w-8 h-8 rounded-full bg-purple-500 flex items-center justify-center text-white font-semibold text-sm">E</div>
                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></div>
                </div>
                <span class="text-sm text-gray-900 font-medium">Eric Jones</span>
            </div>
            <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                <div class="relative">
                    <div class="w-8 h-8 rounded-full bg-pink-500 flex items-center justify-center text-white font-semibold text-sm">C</div>
                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></div>
                </div>
                <span class="text-sm text-gray-900 font-medium">Cynthia Lopez</span>
            </div>
            <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                <div class="relative">
                    <div class="w-8 h-8 rounded-full bg-orange-500 flex items-center justify-center text-white font-semibold text-sm">B</div>
                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></div>
                </div>
                <span class="text-sm text-gray-900 font-medium">Betty Chen</span>
            </div>
            <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                <div class="relative">
                    <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center text-white font-semibold text-sm">T</div>
                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></div>
                </div>
                <span class="text-sm text-gray-900 font-medium">Tina Lim</span>
            </div>
            <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                <div class="relative">
                    <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-sm">M</div>
                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-gray-300 border-2 border-white rounded-full"></div>
                </div>
                <span class="text-sm text-gray-900 font-medium">Molly Carter</span>
            </div>
        </div>
    </div>
</div>
