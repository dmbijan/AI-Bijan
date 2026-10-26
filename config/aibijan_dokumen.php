<?php

// Tema dokumen (berasingan dari tema dashboard). Font DejaVu Sans: sokong Unicode & tersedia dalam dompdf.
return [
    'themes' => [
        'neutral' => ['name' => 'Neutral', 'accent' => '#374151', 'header_bg' => '#FFFFFF', 'header_fg' => '#111827', 'rule' => '#D1D5DB', 'font' => 'DejaVu Sans', 'title_case' => 'upper'],
        'korporat' => ['name' => 'Korporat', 'accent' => '#1E3A5F', 'header_bg' => '#1E3A5F', 'header_fg' => '#FFFFFF', 'rule' => '#1E3A5F', 'font' => 'DejaVu Sans', 'title_case' => 'upper'],
        'moden' => ['name' => 'Moden', 'accent' => '#B45309', 'header_bg' => '#FFFBEB', 'header_fg' => '#241B16', 'rule' => '#F59E0B', 'font' => 'DejaVu Sans', 'title_case' => 'title'],
        'ringkas' => ['name' => 'Ringkas', 'accent' => '#000000', 'header_bg' => '#FFFFFF', 'header_fg' => '#000000', 'rule' => '#000000', 'font' => 'DejaVu Serif', 'title_case' => 'upper'],
    ],
    'default_theme' => 'korporat',
    'paper' => ['A4' => 'A4', 'Letter' => 'letter'],
];
