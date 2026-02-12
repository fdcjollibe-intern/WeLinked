<?php
/**
 * Middle column component: post composer + posts list
 * Variables: $posts (array), $start, $limit
 */
?>
<section class="middle-column">
    <div class="composer">
        <textarea id="post-input" placeholder="Post Something Today"></textarea>
        <div class="controls">
            <div>
                <input id="attachment-input" type="file" multiple accept="image/*,video/*">
            </div>
            <div>
                <button id="post-submit" class="we-btn">Post</button>
            </div>
        </div>
    </div>

    <div id="attachment-preview"></div>

    <div id="posts-list" data-start="<?= h($start ?? 0) ?>">
        <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $i => $post): ?>
                                <?php
                                        $idx = ($start ?? 0) + $i + 1;
                                        $attachments = [];
                                        if (!empty($post->attachments)) {
                                                if (is_array($post->attachments)) {
                                                        $attachments = $post->attachments;
                                                } else {
                                                        // maybe stored as JSON string
                                                        $attachments = json_decode($post->attachments, true) ?: [];
                                                }
                                        }
                                ?>
                                <article class="post bg-white rounded-lg p-4" data-index="<?= $idx ?>" data-post-id="<?= h($post->id ?? $idx) ?>">
                                        <div class="post-header text-sm font-semibold"><?= h($post->user->username ?? 'user') ?></div>
                                        <div class="post-body text-sm mt-2"><?= h($post->body ?? '') ?></div>

                                        <?php if (!empty($attachments)): ?>
                                            <div class="post-gallery mt-3 overflow-hidden rounded-lg bg-gray-50">
                                                <div class="gallery-track flex transition-transform duration-200" style="width:<?= count($attachments) * 100 ?>%">
                                                    <?php foreach ($attachments as $a): ?>
                                                        <div class="w-full flex-shrink-0" style="width:<?= 100 / max(1, count($attachments)) ?>%">
                                                            <img src="<?= h($a['url'] ?? $a) ?>" alt="attachment" class="w-full h-auto object-cover">
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php if (count($attachments) > 1): ?>
                                                    <button class="gallery-left absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 p-2 rounded-full">◀</button>
                                                    <button class="gallery-right absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 p-2 rounded-full">▶</button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="mt-3 flex items-center gap-3">
                                            <button class="reaction-btn px-3 py-1 rounded-full border border-gray-200 text-sm flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 9l-1 1-1-1m0 6l1-1 1 1M5 13a7 7 0 0114 0v1a3 3 0 01-3 3H8a3 3 0 01-3-3v-1z"/></svg>
                                                Like
                                            </button>
                                            <button class="comment-btn px-3 py-1 rounded-full border border-gray-200 text-sm flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.774 9.774 0 01-4-.84L3 20l1.1-3.6A7.966 7.966 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                Comment
                                            </button>
                                            <div class="text-xs text-gray-400 ml-auto"><?= h($post->created ?? '') ?></div>
                                        </div>
                                </article>
                        <?php endforeach; ?>
        <?php else: ?>
            <div class="no-posts">No posts to show yet.</div>
        <?php endif; ?>
    </div>
</section>
<?= $this->Html->script('middle') ?>
