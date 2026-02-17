<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\AppController;
use App\Service\CloudinaryUploader;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;

class DashboardUploadsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->disableAutoLayout();
    }

    /**
     * Generic upload endpoint. Query param `type` should be `post` or `comment`.
     * Accepts image/* and video/* files. Max file size: 250 MB.
     * Returns JSON with saved file URLs.
     */
    public function upload()
    {
        $this->request->allowMethod(['post']);
        
        $this->log('===== UPLOAD REQUEST START =====', 'info');
        $this->log('Request method: ' . $this->request->getMethod(), 'info');
        $this->log('Content-Type: ' . $this->request->contentType(), 'info');
        $this->log('Query params: ' . json_encode($this->request->getQueryParams()), 'info');
        
        try {
            $type = $this->request->getQuery('type', 'post');
            $allowedTypes = ['post' => 'posts', 'comment' => 'comments'];
            
            $this->log('Upload type requested: ' . $type, 'info');
            
            if (!in_array($type, array_keys($allowedTypes), true)) {
                $this->log('Invalid upload type: ' . $type, 'error');
                throw new BadRequestException('Invalid upload type');
            }

            $identity = $this->request->getAttribute('identity');
            $userId = 0;
            if (is_object($identity) && isset($identity->id)) {
                $userId = (int)$identity->id;
            } elseif (is_array($identity) && isset($identity['id'])) {
                $userId = (int)$identity['id'];
            }
            
            $this->log('User ID from identity: ' . $userId, 'info');

            $uploaded = $this->getRequest()->getUploadedFiles();
            
            $this->log('Uploaded files count: ' . count($uploaded), 'info');
            $this->log('Uploaded files structure: ' . json_encode(array_map(function($file) {
                if (is_array($file)) {
                    return array_map(function($f) {
                        return [
                            'name' => method_exists($f, 'getClientFilename') ? $f->getClientFilename() : 'unknown',
                            'size' => method_exists($f, 'getSize') ? $f->getSize() : 0,
                            'type' => method_exists($f, 'getClientMediaType') ? $f->getClientMediaType() : 'unknown'
                        ];
                    }, $file);
                } else {
                    return [
                        'name' => method_exists($file, 'getClientFilename') ? $file->getClientFilename() : 'unknown',
                        'size' => method_exists($file, 'getSize') ? $file->getSize() : 0,
                        'type' => method_exists($file, 'getClientMediaType') ? $file->getClientMediaType() : 'unknown'
                    ];
                }
            }, $uploaded)), 'info');

            if (empty($uploaded)) {
                $this->log('No files uploaded in request', 'error');
                $this->response = $this->response->withType('application/json')
                    ->withStringBody(json_encode(['error' => 'No files uploaded']));
                return $this->response;
            }

            // Extract files from uploaded files array
            // Handle both 'file', 'files[]', and multiple field names
            $filesToProcess = [];
            foreach ($uploaded as $key => $value) {
                if (is_array($value)) {
                    // Multiple files in array (e.g., 'files[]' from FormData)
                    $filesToProcess = array_merge($filesToProcess, $value);
                } else {
                    // Single file
                    $filesToProcess[] = $value;
                }
            }

            if (empty($filesToProcess)) {
                $this->log('No valid files found after processing uploaded files array', 'error');
                $this->response = $this->response->withType('application/json')
                    ->withStringBody(json_encode(['error' => 'No valid files found']));
                return $this->response;
            }
            
            $this->log('Files to process: ' . count($filesToProcess), 'info');

            $saved = [];
            $maxSize = 250 * 1024 * 1024; // 250 MB
            $subdir = $allowedTypes[$type];
            $webrootDir = WWW_ROOT . 'uploads' . DIRECTORY_SEPARATOR . 'attachments' . DIRECTORY_SEPARATOR . $subdir . DIRECTORY_SEPARATOR;

            $this->log(sprintf(
                'Upload request: type=%s, user=%s, files=%d',
                $type,
                $userId ?: 'guest',
                count($filesToProcess)
            ), 'info');

            // Create directory if it doesn't exist
            if (!is_dir($webrootDir)) {
                mkdir($webrootDir, 0755, true);
            }

            // Use Cloudinary if configured (app_local.php/cloudinary.php define credentials)
            $cloudinaryConfig = [];
            try {
                Configure::load('cloudinary', 'default');
                $cloudinaryConfig = (array)Configure::read('Cloudinary');
                $this->log('Cloudinary config loaded successfully', 'info');
                $this->log('Cloudinary config keys: ' . json_encode(array_keys($cloudinaryConfig)), 'debug');
            } catch (\Throwable $configError) {
                $this->log('Cloudinary config not loaded: ' . $configError->getMessage(), 'warning');
            }

            $useCloudinary = !empty($cloudinaryConfig['api_key']) && !empty($cloudinaryConfig['cloud_name']);
            $uploader = $useCloudinary ? new CloudinaryUploader() : null;
            
            $this->log('Upload method: ' . ($useCloudinary ? 'Cloudinary' : 'Local storage'), 'info');
            if ($useCloudinary) {
                $this->log('Cloudinary cloud_name: ' . ($cloudinaryConfig['cloud_name'] ?? 'N/A'), 'info');
            }

            foreach ($filesToProcess as $idx => $file) {
                // $file implements Psr\Http\Message\UploadedFileInterface
                $size = $file->getSize() ?? 0;
                $mediaType = $file->getClientMediaType() ?? '';
                $original = $file->getClientFilename() ?? 'file';
                
                $this->log(sprintf(
                    '\n----- Processing file %d/%d -----\nFile: %s\nType: %s\nSize: %.2f MB',
                    $idx + 1,
                    count($filesToProcess),
                    $original,
                    $mediaType,
                    $size / (1024 * 1024)
                ), 'info');
                
                if ($size > $maxSize) {
                    $this->log(sprintf('File too large: %s (%.2f MB > 250 MB)', $original, $size / (1024 * 1024)), 'warning');
                    continue;
                }
                
                if (strpos($mediaType, 'image/') !== 0 && strpos($mediaType, 'video/') !== 0) {
                    $this->log(sprintf('Invalid media type: %s (%s)', $original, $mediaType), 'warning');
                    continue;
                }

                if ($useCloudinary && $uploader) {
                    $this->log('Using Cloudinary upload for: ' . $original, 'info');
                    
                    // Move to temporary file and upload via Cloudinary service
                    $tempPath = TMP . 'uploads' . DIRECTORY_SEPARATOR . uniqid('upl_', true);
                    if (!is_dir(dirname($tempPath))) {
                        mkdir(dirname($tempPath), 0755, true);
                    }
                    
                    $this->log('Creating temporary file: ' . $tempPath, 'debug');
                    
                    $stream = $file->getStream();
                    $stream->rewind();
                    file_put_contents($tempPath, $stream->getContents());
                    
                    $this->log('Temporary file created, size: ' . filesize($tempPath) . ' bytes', 'debug');

                    // Decide image or video
                    $isImage = strpos($mediaType, 'image/') === 0;
                    $this->log('Upload type: ' . ($isImage ? 'image' : 'video'), 'info');
                    
                    try {
                        if ($isImage) {
                            $this->log('Calling CloudinaryUploader::uploadPostImage()', 'info');
                            $result = $uploader->uploadPostImage($tempPath, $userId, null);
                        } else {
                            $this->log('Calling CloudinaryUploader::uploadPostVideo()', 'info');
                            $result = $uploader->uploadPostVideo($tempPath, $userId, null);
                        }
                        
                        $this->log('Cloudinary upload result: ' . json_encode($result), 'info');
                    } catch (\Throwable $uploadError) {
                        $this->log('Cloudinary upload exception: ' . $uploadError->getMessage(), 'error');
                        $this->log('Stack trace: ' . $uploadError->getTraceAsString(), 'debug');
                        $result = ['success' => false, 'error' => $uploadError->getMessage()];
                    }

                    if ($result && isset($result['success']) && $result['success']) {
                        $this->log(sprintf(
                            'Cloudinary upload success: public_id=%s folder=/posts',
                            $result['public_id'] ?? 'n/a'
                        ), 'info');
                        // Cloudinary upload succeeded
                        if (file_exists($tempPath)) {
                            unlink($tempPath);
                        }
                        $saved[] = [
                            'url' => $result['url'],
                            'original' => $original,
                            'size' => $size,
                            'resource_type' => $result['resource_type'] ?? (strpos($mediaType, 'image/') === 0 ? 'image' : 'video'),
                            'public_id' => $result['public_id'] ?? null,
                        ];
                    } else {
                        // Cloudinary failed, fallback to local storage
                        $this->log('Cloudinary upload failed, falling back to local storage', 'warning');
                        $ext = pathinfo($original, PATHINFO_EXTENSION);
                        $basename = uniqid('att_', true) . ($ext ? '.' . $ext : '');
                        $target = $webrootDir . $basename;

                        // Move temp file to local storage
                        if (file_exists($tempPath)) {
                            rename($tempPath, $target);
                        }

                        $url = $this->request->getAttribute('webroot') . 'uploads/attachments/' . $subdir . '/' . $basename;
                        $saved[] = [
                            'url' => $url,
                            'original' => $original,
                            'size' => $size,
                        ];
                    }
                } else {
                    $ext = pathinfo($original, PATHINFO_EXTENSION);
                    $basename = uniqid('att_', true) . ($ext ? '.' . $ext : '');
                    $target = $webrootDir . $basename;

                    // Move stream to target
                    $stream = $file->getStream();
                    $stream->rewind();
                    file_put_contents($target, $stream->getContents());

                    $url = $this->request->getAttribute('webroot') . 'uploads/attachments/' . $subdir . '/' . $basename;
                    $this->log('Stored attachment locally at ' . $url, 'info');
                    $saved[] = [
                        'url' => $url,
                        'original' => $original,
                        'size' => $size,
                    ];
                }
            }
            
            $this->log('\n===== UPLOAD COMPLETE =====', 'info');
            $this->log('Total files saved: ' . count($saved), 'info');
            $this->log('Saved files: ' . json_encode(array_map(function($f) {
                return ['url' => $f['url'], 'original' => $f['original']];
            }, $saved)), 'info');

            $this->response = $this->response->withType('application/json')
                ->withStringBody(json_encode(['files' => $saved]));
            return $this->response;
        } catch (\Exception $e) {
            $this->log('===== UPLOAD ERROR =====', 'error');
            $this->log('Error message: ' . $e->getMessage(), 'error');
            $this->log('Error file: ' . $e->getFile() . ':' . $e->getLine(), 'error');
            $this->log('Stack trace: ' . $e->getTraceAsString(), 'error');
            
            error_log('Upload error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $this->response = $this->response->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Upload failed: ' . $e->getMessage()
                ]));
            return $this->response;
        }
    }

    /**
     * Delete uploaded resource (Cloudinary public_id). Expects JSON body with public_id and resource_type (image|video).
     */
    public function delete()
    {
        $this->autoRender = false; // Prevent view rendering for JSON response
        $this->request->allowMethod(['post', 'delete']);
        
        try {
            $data = $this->getRequest()->getData();
            $publicId = $data['public_id'] ?? null;
            $resourceType = $data['resource_type'] ?? 'image';

            if (!$publicId) {
                $this->response = $this->response->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode(['success' => false, 'message' => 'public_id required']));
                return $this->response;
            }

            // Only attempt Cloudinary delete if configured
            $useCloudinary = (bool)Configure::read('Cloudinary.api_key');
            if ($useCloudinary) {
                $uploader = new CloudinaryUploader();
                $ok = $uploader->delete($publicId, $resourceType);
                $this->response = $this->response->withType('application/json')
                    ->withStringBody(json_encode(['success' => $ok]));
                return $this->response;
            }

            $this->response = $this->response->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode(['success' => false, 'message' => 'Cloudinary not configured']));
            return $this->response;
            
        } catch (\Exception $e) {
            error_log('Delete error: ' . $e->getMessage());
            $this->response = $this->response->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Delete failed: ' . $e->getMessage()
                ]));
            return $this->response;
        }
    }
}
