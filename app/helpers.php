<?php
use Illuminate\Support\Str;



if (!function_exists('complete_url')) {
    function complete_url($relativePath)
    {
        return $relativePath
            ? url('') . '/' . ltrim($relativePath, '/')
            : null;
    }
}

if (!function_exists('relative_url')) {
    function relative_url($fullUrl)
    {
         if (is_null($fullUrl)) {
            return null;
        }
        
        $baseUrl = url('');
        return $fullUrl && Str::startsWith($fullUrl, $baseUrl)
            ? Str::replaceFirst($baseUrl . '/', '', $fullUrl)
            : $fullUrl;
    }
}