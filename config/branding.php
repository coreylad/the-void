<?php

declare(strict_types=1);

return [
    // Slots that banners can be assigned to. Keys are stored values, values are display labels.
    'banner_slots' => [
        'homepage'  => 'Homepage',
        'login'     => 'Login page',
        'dashboard' => 'Staff dashboard',
    ],

    // Maximum upload size for banner images, in kilobytes.
    'max_upload_kb' => 8192,

    // Maximum dimensions (in pixels) that a static (non-animated) banner
    // image is resized to fit within, preserving its aspect ratio. Images
    // smaller than these dimensions are not upscaled. Animated images
    // (APNG/GIF) are stored unmodified so their animation isn't lost.
    'banner_max_width'  => 1600,
    'banner_max_height' => 400,
];
