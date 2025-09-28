<?php

namespace RpGeneralHelpers;

class SlugHelper
{
    public static function generate(string $string): string
    {
        $slug = strtolower($string);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }

    public static function ensureUnique(\PDO $pdo, string $table, string $baseSlug, string $column = 'slug'): string
    {
        $slug = $baseSlug;
        $i = 1;
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :slug";
        $stmt = $pdo->prepare($sql);

        while (true) {
            $stmt->execute(['slug' => $slug]);
            if ($stmt->fetchColumn() == 0) break;
            $slug = $baseSlug . '-' . $i++;
        }

        return $slug;
    }
}
