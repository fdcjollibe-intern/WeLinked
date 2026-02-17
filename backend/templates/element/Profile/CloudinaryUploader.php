<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cloudinary\Cloudinary;
use Cloudinary\Transformation\Resize;
use Cloudinary\Transformation\Delivery;
use Cloudinary\Transformation\Quality;
use Cloudinary\Transformation\Format;

/**
 * Cloudinary Upload Service
 * 
 * Handles all media uploads to Cloudinary CDN with proper optimization.
 */
class CloudinaryUploader
{
    private Cloudinary $cloudinary;
    private array $config;

    public function __construct()
    {
        // Load cloudinary config
        Configure::load('cloudinary', 'default');
        $this->config = Configure::read('Cloudinary');
        
        // Initialize Cloudinary
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $this->config['cloud_name'],
                'api_key' => $this->config['api_key'],
                'api_secret' => $this->config['api_secret'],
            ],
            'url' => [
                'secure' => $this->config['secure'] ?? true,
            ],
        ]);
    }

    /**
     * Upload profile photo
     * 
     * @param string $filePath Local file path or uploaded file
     * @param int $userId User ID for unique naming
     * @return array Upload result with URL
     */
    public function uploadProfilePhoto(string $filePath, int $userId): array
    {
        $publicId = 'user_' . $userId . '_' . bin2hex(random_bytes(8));
        $folder = $this->config['folders']['profile_photos'];
        
        try {
            // Detect MIME type to preserve animated GIFs
            $mime = @mime_content_type($filePath) ?: '';

            $transformation = [
                'width' => 400,
                'height' => 400,
                'crop' => 'fill',
                'gravity' => 'face',
                'quality' => 'auto',
                'fetch_format' => 'auto',
            ];

            $options = [
                'folder' => $folder,
                'public_id' => $publicId,
                'transformation' => $transformation,
                'overwrite' => true,
                'invalidate' => true,
            ];

            // If the uploaded file is an animated GIF, request GIF output to preserve animation
            if (stripos($mime, 'gif') !== false) {
                // Remove fetch_format to avoid conversion to static formats
                unset($options['transformation']['fetch_format']);
                // Ensure output format is GIF
                $options['format'] = 'gif';
            }

            $result = $this->cloudinary->uploadApi()->upload($filePath, $options);
            
            return [
                'success' => true,
                'url' => $result['secure_url'],
                'public_id' => $result['public_id'],
            ];
        } catch (\Exception $e) {
            error_log('Cloudinary upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Upload post image
     * 
     * @param string $filePath Local file path
     * @param int $userId User ID
     * @param int $postId Post ID (optional, for naming)
     * @return array Upload result
     */
    public function uploadPostImage(string $filePath, int $userId, ?int $postId = null): array
    {
        $hash = bin2hex(random_bytes(12));
        $publicId = $postId 
            ? "post_{$postId}_{$hash}" 
            : "user_{$userId}_{$hash}";
        $folder = $this->config['folders']['posts'];
        
        try {
            $result = $this->cloudinary->uploadApi()->upload($filePath, [
                'folder' => $folder,
                'public_id' => $publicId,
                'transformation' => [
                    'width' => 1000,
                    'quality' => 'auto',
                    'fetch_format' => 'auto',
                ],
                'resource_type' => 'image',
            ]);
            
            return [
                'success' => true,
                'url' => $result['secure_url'],
                'public_id' => $result['public_id'],
                'resource_type' => 'image',
            ];
        } catch (\Exception $e) {
            error_log('Cloudinary upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Upload post video
     * 
     * @param string $filePath Local file path
     * @param int $userId User ID
     * @param int $postId Post ID (optional)
     * @return array Upload result
     */
    public function uploadPostVideo(string $filePath, int $userId, ?int $postId = null): array
    {
        $hash = bin2hex(random_bytes(12));
        $publicId = $postId 
            ? "post_{$postId}_{$hash}" 
            : "user_{$userId}_{$hash}";
        $folder = $this->config['folders']['posts'];
        
        try {
            $result = $this->cloudinary->uploadApi()->upload($filePath, [
                'folder' => $folder,
                'public_id' => $publicId,
                'transformation' => [
                    'quality' => 'auto',
                    'fetch_format' => 'auto',
                ],
                'resource_type' => 'video',
            ]);
            
            return [
                'success' => true,
                'url' => $result['secure_url'],
                'public_id' => $result['public_id'],
                'resource_type' => 'video',
            ];
        } catch (\Exception $e) {
            error_log('Cloudinary upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete resource from Cloudinary using Admin API
     * 
     * @param string $publicId Public ID of the resource
     * @param string $resourceType 'image' or 'video'
     * @return bool
     */
    public function delete(string $publicId, string $resourceType = 'image'): bool
    {
        try {
            // Use Admin API delete_resources method
            $result = $this->cloudinary->adminApi()->deleteAssets(
                [$publicId], // Array of public IDs (supports up to 100)
                [
                    'resource_type' => $resourceType,
                    'type' => 'upload', // Delivery type
                    'invalidate' => true, // Invalidate CDN cache
                ]
            );
            
            // Check if deletion was successful
            $deleted = $result['deleted'] ?? [];
            return isset($deleted[$publicId]) && $deleted[$publicId] === 'deleted';
        } catch (\Exception $e) {
            error_log('Cloudinary delete error: ' . $e->getMessage());
            return false;
        }
    }
}
