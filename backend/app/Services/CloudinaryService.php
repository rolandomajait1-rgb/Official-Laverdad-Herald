<?php

namespace App\Services;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception;

class CloudinaryService
{
    /**
     * Upload an image to Cloudinary
     * 
     * @param UploadedFile $file
     * @return string The secure HTTPS URL of the uploaded image
     * @throws Exception
     */
    public function uploadImage(UploadedFile $file): string
    {
        try {
            // Upload to Cloudinary with 'articles' folder
            $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
                'folder' => 'articles'
            ])->getSecurePath();
            
            if (!$uploadedFileUrl) {
                throw new Exception('Cloudinary upload failed: No secure URL returned');
            }
            
            Log::info('Image uploaded to Cloudinary successfully', [
                'original_name' => $file->getClientOriginalName(),
                'cloudinary_url' => $uploadedFileUrl
            ]);
            
            return $uploadedFileUrl;
        } catch (Exception $e) {
            Log::error('Cloudinary upload failed', [
                'original_name' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            throw new Exception('Failed to upload image to Cloudinary: ' . $e->getMessage());
        }
    }
}
