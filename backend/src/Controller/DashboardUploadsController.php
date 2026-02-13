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
        
        try {
            $type = $this->request->getQuery('type', 'post');
            $allowedTypes = ['post' => 'posts', 'comment' => 'comments'];
            if (!in_array($type, array_keys($allowedTypes), true)) {
                throw new BadRequestException('Invalid upload type');
            }

            $uploaded = $this->getRequest()->getUploadedFiles();
            if (empty($uploaded)) {
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
                $this->response = $this->response->withType('application/json')
                    ->withStringBody(json_encode(['error' => 'No valid files found']));
                return $this->response;
            }

            $saved = [];
            $maxSize = 250 * 1024 * 1024; // 250 MB
            $subdir = $allowedTypes[$type];
            $webrootDir = WWW_ROOT . 'uploads' . DIRECTORY_SEPARATOR . 'attachments' . DIRECTORY_SEPARATOR . $subdir . DIRECTORY_SEPARATOR;

            // Create directory if it doesn't exist
            if (!is_dir($webrootDir)) {
                mkdir($webrootDir, 0755, true);
            }

            // Use Cloudinary if configured (app_local.php has Cloudinary.api_key etc.)
            $useCloudinary = (bool)Configure::read('Cloudinary.api_key');
            $uploader = $useCloudinary ? new CloudinaryUploader() : null;

            foreach ($filesToProcess as $file) {
                // $file implements Psr\Http\Message\UploadedFileInterface
                $size = $file->getSize() ?? 0;
                if ($size > $maxSize) {
                    continue;
                }
                $mediaType = $file->getClientMediaType() ?? '';
                if (strpos($mediaType, 'image/') !== 0 && strpos($mediaType, 'video/') !== 0) {
                    continue;
                }

                $original = $file->getClientFilename() ?? 'file';

                if ($useCloudinary && $uploader) {
                    // Move to temporary file and upload via Cloudinary service
                    $tempPath = TMP . 'uploads' . DIRECTORY_SEPARATOR . uniqid('upl_', true);
                    if (!is_dir(dirname($tempPath))) {
                        mkdir(dirname($tempPath), 0755, true);
                    }
                    $stream = $file->getStream();
                    $stream->rewind();
                    file_put_contents($tempPath, $stream->getContents());

                    // Decide image or video
                    if (strpos($mediaType, 'image/') === 0) {
                        $result = $uploader->uploadPostImage($tempPath, 0, null);
                    } else {
                        $result = $uploader->uploadPostVideo($tempPath, 0, null);
                    }

                    if ($result && isset($result['success']) && $result['success']) {
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
                        error_log('Cloudinary upload failed, falling back to local storage');
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
                    $saved[] = [
                        'url' => $url,
                        'original' => $original,
                        'size' => $size,
                    ];
                }
            }

            $this->response = $this->response->withType('application/json')
                ->withStringBody(json_encode(['files' => $saved]));
            return $this->response;
        } catch (\Exception $e) {
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
