<?php

use Google\Cloud\Firestore\FirestoreClient;

if (!function_exists('firestore')) {
    function firestore()
    {
        return new FirestoreClient([
            'projectId' => config('firestore.project_id'),
            'keyFilePath' => config('firestore.credentials'),
            'transport' => 'rest', // Force REST API instead of gRPC to avoid extension issues
        ]);
    }
}

if (!function_exists('placeholder_image')) {
    /**
     * Get the placeholder image URL
     *
     * @return string
     */
    function placeholder_image()
    {
        return asset('assets/images/placeholder-image.png');
    }
}

if (!function_exists('image_with_fallback')) {
    /**
     * Get image URL with fallback to placeholder
     *
     * @param string|null $imagePath
     * @return string
     */
    function image_with_fallback($imagePath = null)
    {
        if (empty($imagePath) || $imagePath === null || $imagePath === 'null') {
            return placeholder_image();
        }
        return $imagePath;
    }
}
