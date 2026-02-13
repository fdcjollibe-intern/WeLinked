<?php
/**
 * Cloudinary Configuration
 * 
 * Credentials are configured in config/app_local.php
 * Update the Cloudinary section in app_local.php with your API credentials.
 */

return [
    'Cloudinary' => [
        'cloud_name' => \Cake\Core\Configure::read('Cloudinary.cloud_name', 'dn6rffrwk'),
        'api_key' => \Cake\Core\Configure::read('Cloudinary.api_key', ''), 
        'api_secret' => \Cake\Core\Configure::read('Cloudinary.api_secret', ''),  
        'secure' => true,
        
        // Upload presets
        'folders' => [
            'profile_photos' => 'profilephotos',
            'posts' => 'posts',
        ],
        
        // Transformation defaults
        'transformations' => [
            'profile_photo' => [
                'width' => 400,
                'height' => 400,
                'crop' => 'fill',
                'gravity' => 'face',
                'quality' => 'auto',
                'fetch_format' => 'auto',
            ],
            'post_image' => [
                'width' => 1000,
                'quality' => 'auto',
                'fetch_format' => 'auto',
            ],
            'post_video' => [
                'quality' => 'auto',
                'fetch_format' => 'auto',
            ],
        ],
    ],
];
