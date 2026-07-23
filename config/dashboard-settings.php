<?php

declare(strict_types=1);

return [
    // Config files under /config that are safe to expose in the dashboard editor.
    'editable_files' => [
        'achievements',
        'announce',
        'audit',
        'captcha',
        'chat',
        'donation',
        'graveyard',
        'hitrun',
        'image',
        'language',
        'other',
        'pruning',
        'torrent',
        'unit3d',
        'user',
        'welcomepm',
    ],

    // Dot-notation key patterns that should never be editable in the dashboard.
    'blocked_key_patterns' => [
        '/(^|\.)password($|\.)/i',
        '/(^|\.)secret($|\.)/i',
        '/(^|\.)token($|\.)/i',
        '/(^|\.)apikey($|\.)/i',
        '/(^|\.)api_key($|\.)/i',
        '/(^|\.)private($|\.)/i',
        '/(^|\.)cert($|\.)/i',
        '/(^|\.)key($|\.)/i',
        '/(^|\.)mailers?($|\.)/i',
        '/(^|\.)connections?($|\.)/i',
    ],
];
