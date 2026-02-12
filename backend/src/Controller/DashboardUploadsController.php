<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\AppController;
use Cake\Filesystem\Folder;
use Cake\Http\Exception\BadRequestException;

class DashboardUploadsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->disableAutoLayout();
    }

    /**
     * Generic upload endpoint. Query param `type` should be `post` or `comment`.
     * Accepts image/* and video/* files. Max file size: 250 MB.
     * Returns JSON with saved file URLs.
     */
    public function upload()
    {
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

        $saved = [];
        $maxSize = 250 * 1024 * 1024; // 250 MB
        $subdir = $allowedTypes[$type];
        $webrootDir = WWW_ROOT . 'uploads' . DIRECTORY_SEPARATOR . 'attachments' . DIRECTORY_SEPARATOR . $subdir . DIRECTORY_SEPARATOR;

        $folder = new Folder($webrootDir, true, 0755);

        foreach ($uploaded as $file) {
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

        $this->response = $this->response->withType('application/json')
            ->withStringBody(json_encode(['files' => $saved]));
        return $this->response;
    }
}
