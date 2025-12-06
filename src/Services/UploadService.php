<?php

namespace App\Services;

use RuntimeException;
use finfo;

class UploadService {
    public const TYPE_CODE_OUTPUT = 'code_output';
    public const TYPE_MANUAL = 'manual';
    public const TYPE_HOMEWORK_ANSWER = 'homework_answer';

    private const MAX_SIZE = 10 * 1024 * 1024; // 10MB

    public static function handle(array $file, string $context): string {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload error: ' . (int)$file['error']);
        }

        if ($file['size'] > self::MAX_SIZE) {
            throw new RuntimeException('File too large (max 10MB).');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']) ?: 'application/octet-stream';

        $ext = '';
        $baseDir = __DIR__ . '/../../public/uploads';
        $webBase = '/uploads';

        // Define allowed types per context
        if ($context === self::TYPE_CODE_OUTPUT) {
            $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'application/pdf' => 'pdf'];
            if (!isset($allowed[$mime])) {
                throw new RuntimeException('Invalid output file type.');
            }
            $ext = '.' . $allowed[$mime];
            $targetDir = $baseDir . '/code_outputs';
            $webDir    = $webBase . '/code_outputs';
        } elseif ($context === self::TYPE_MANUAL) {
            if ($mime !== 'application/pdf') {
                throw new RuntimeException('Manuals must be PDF.');
            }
            $ext = '.pdf';
            $targetDir = $baseDir . '/manuals';
            $webDir    = $webBase . '/manuals';
        } elseif ($context === self::TYPE_HOMEWORK_ANSWER) {
            $allowed = [
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'image/png' => 'png',
                'image/jpeg' => 'jpg',
                'application/zip' => 'zip',
                'application/x-zip-compressed' => 'zip'
            ];
            if (!isset($allowed[$mime])) {
                throw new RuntimeException('Unsupported answer format.');
            }
            $ext = '.' . $allowed[$mime];
            $targetDir = $baseDir . '/homework_answers';
            $webDir    = $webBase . '/homework_answers';
        } else {
            throw new RuntimeException('Invalid upload context.');
        }

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                throw new RuntimeException('Failed to create upload directory.');
            }
        }

        $basename = bin2hex(random_bytes(8)) . $ext;
        $targetFs = $targetDir . '/' . $basename;

        if (!move_uploaded_file($file['tmp_name'], $targetFs)) {
            throw new RuntimeException('Failed to store uploaded file.');
        }

        return $webDir . '/' . $basename;
    }
}
