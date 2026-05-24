<?php

namespace App\Services;

use Aws\S3\S3Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * S3 Storage Service
 * 
 * Simplified S3 service following the conviction-ats pattern.
 * - Stores only filename in database
 * - Uses folder + filename to generate signed URLs
 * - Hash-based unique filename generation
 */
class S3StorageService
{
    protected S3Client $s3Client;
    protected string $bucket;

    public function __construct()
    {
        $config = [
            'version' => 'latest',
            'region'  => config('filesystems.disks.s3.region'),
        ];

        // Only pass explicit credentials if set (local dev)
        // In cloud, the SDK auto-discovers credentials via IAM Role
        $key = env('AWS_ACCESS_KEY_ID');
        $secret = env('AWS_SECRET_ACCESS_KEY');
        if ($key && $secret) {
            $config['credentials'] = [
                'key'    => $key,
                'secret' => $secret,
            ];
        }

        $this->s3Client = new S3Client($config);
        
        $this->bucket = config('filesystems.disks.s3.bucket');
    }

    /**
     * Generate a unique hash-based filename
     * 
     * @param string $originalName Original filename to extract extension
     * @return string Unique filename with extension
     */
    public function generateFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'bin';
        $hash = bin2hex(random_bytes(16));
        
        return $hash . '.' . strtolower($extension);
    }

    /**
     * Upload a file to S3
     * 
     * @param UploadedFile $file The uploaded file
     * @param string $folder The S3 folder (e.g., 'avatars', 'logos', 'invoices')
     * @return string The generated filename (store this in database)
     */
    public function uploadFile(UploadedFile $file, string $folder): string
    {
        $filename = $this->generateFilename($file->getClientOriginalName());
        $key = $folder . '/' . $filename;

        $this->s3Client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
            'Body' => file_get_contents($file->getRealPath()),
            'ContentType' => $file->getMimeType(),
        ]);

        return $filename;
    }

    /**
     * Upload raw content to S3 (for PDFs, etc.)
     * 
     * @param string $content The file content
     * @param string $folder The S3 folder
     * @param string $filename The filename to use
     * @param string $contentType The MIME type
     * @return string The filename
     */
    public function uploadContent(string $content, string $folder, string $filename, string $contentType = 'application/octet-stream'): string
    {
        $key = $folder . '/' . $filename;

        $this->s3Client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
            'Body' => $content,
            'ContentType' => $contentType,
        ]);

        return $filename;
    }

    /**
     * Delete a file from S3
     * 
     * @param string $filename The filename
     * @param string $folder The S3 folder
     * @return void
     */
    public function deleteFile(string $filename, string $folder): void
    {
        try {
            $key = $folder . '/' . $filename;
            
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
        } catch (\Exception $e) {
            Log::error('S3 delete error: ' . $e->getMessage());
        }
    }

    /**
     * Generate a signed URL for downloading/viewing a file
     * 
     * @param string $filename The filename stored in database
     * @param string $folder The S3 folder
     * @param int $expiresIn Expiration time in seconds (default 5 minutes)
     * @return string The signed URL
     */
    public function getSignedUrl(string $filename, string $folder, int $expiresIn = 300): string
    {
        $key = $folder . '/' . $filename;

        $cmd = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);

        $request = $this->s3Client->createPresignedRequest($cmd, "+{$expiresIn} seconds");

        return (string) $request->getUri();
    }

    /**
     * Get public URL for a file (for public-read objects)
     * 
     * @param string $filename The filename stored in database
     * @param string $folder The S3 folder
     * @return string The public URL
     */
    public function getPublicUrl(string $filename, string $folder): string
    {
        $key = $folder . '/' . $filename;
        $region = config('filesystems.disks.s3.region');
        
        return "https://{$this->bucket}.s3.{$region}.amazonaws.com/{$key}";
    }

    /**
     * Check if a file exists in S3
     * 
     * @param string $filename The filename
     * @param string $folder The S3 folder
     * @return bool
     */
    public function fileExists(string $filename, string $folder): bool
    {
        try {
            $key = $folder . '/' . $filename;
            
            $this->s3Client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get file contents from S3
     * 
     * @param string $filename The filename
     * @param string $folder The S3 folder
     * @return string|null
     */
    public function getFileContents(string $filename, string $folder): ?string
    {
        try {
            $key = $folder . '/' . $filename;
            
            $result = $this->s3Client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
            
            return (string) $result['Body'];
        } catch (\Exception $e) {
            Log::error('S3 get error: ' . $e->getMessage());
            return null;
        }
    }
}
