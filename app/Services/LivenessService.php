<?php

namespace App\Services;

class LivenessService
{
    public function score(string $absoluteImagePath): float
    {
        // Deterministic stub: derive a pseudo-score from the hash so tests are stable.
        $hash = hexdec(substr(hash_file('sha1', $absoluteImagePath), 0, 6));
        $normalized = ($hash % 30) / 100; // 0.00 - 0.29

        return round(0.7 + $normalized, 2); // 0.70 - 0.99
    }
}

