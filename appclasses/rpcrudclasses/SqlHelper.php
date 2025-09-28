<?php
// ### Updated:  Sun Sep 28 2025 00:36:51 CDT
// Help file: http://localhost/app5/dashboard/index.php#../app/chatgpt_chats/markdown_files/binding_and_automating_php_queries/bind_pdo_queries_class_part2_php.php
namespace RpCrudClasses;

class SqlHelper
{
    protected string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public static function filterInput(array $allowed, array $input): array
    {
        return array_intersect_key($input, array_flip($allowed));
    }

    public static function buildBindArray(array $columns, array $values): array
    {
        $bind = [];
        foreach ($columns as $col) {
            $bind[":$col"] = $values[$col] ?? null;
        }
        return $bind;
    }

    public static function formatInsertParts(array $columns): array
    {
        $cols = implode(', ', $columns);
        $placeholders = ':' . implode(', :', $columns);
        return [$cols, $placeholders];
    }

    public static function buildUpdateSet(array $columns): string
    {
        $sets = [];
        foreach ($columns as $col) {
            $sets[] = "$col = :$col";
        }
        return implode(', ', $sets);
    }

    public static function buildWhereClause(array $conditions): string
    {
        $whereParts = [];
        foreach ($conditions as $index => $cond) {
            $boolean = $index === 0 ? '' : strtoupper($cond['boolean'] ?? 'AND');
            $col = $cond['column'];
            $op = strtoupper($cond['operator'] ?? '=');

            if ($op === 'IN' && is_array($cond['value'])) {
                $placeholders = [];
                foreach ($cond['value'] as $i => $val) {
                    $placeholders[] = ":{$col}_in_{$i}";
                }
                $whereParts[] = trim("$boolean $col IN (" . implode(', ', $placeholders) . ")");
            } else {
                $whereParts[] = trim("$boolean $col $op :$col");
            }
        }
        return implode(' ', $whereParts);
    }

    public static function buildWhereBindArray(array $conditions): array
    {
        $bind = [];
        foreach ($conditions as $cond) {
            $col = $cond['column'];
            $val = $cond['value'];
            $op = strtoupper($cond['operator'] ?? '=');

            if ($op === 'IN' && is_array($val)) {
                foreach ($val as $i => $v) {
                    $bind[":{$col}_in_{$i}"] = $v;
                }
            } else {
                $bind[":$col"] = $val;
            }
        }
        return $bind;
    }
}