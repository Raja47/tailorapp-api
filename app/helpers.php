<?php

if (!function_exists('complete_url')) {
    function complete_url($relativePath)
    {
        return $relativePath
            ? url('') . '/' . ltrim($relativePath, '/')
            : null;
    }
}
