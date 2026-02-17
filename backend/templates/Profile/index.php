<?php
echo $this->element('Profile/profile_content', [
    'user' => $user,
    'postCount' => $postCount,
    'followersCount' => $followersCount,
    'followingCount' => $followingCount,
    'identity' => $identity,
    'isMobileView' => $isMobileView ?? false,
    'posts' => $posts ?? []
]);
?>