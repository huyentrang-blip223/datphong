<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class FoundationSchemaService
{
    public function initialize(string $path): bool
    {
        if (Schema::hasTable('users')) {
            return false;
        }
        if (DB::select('SHOW TABLES')) {
            throw new RuntimeException('Database is not empty; import/reconcile schema manually. No existing tables were changed.');
        }
        $sql = file_get_contents($path);
        $sql = preg_replace('/^DROP DATABASE[^;]*;|^CREATE DATABASE[^;]*;|^USE\s+[^;]*;/mi', '', $sql);
        $delimiter = ';';
        $buffer = '';
        try {
            foreach (preg_split('/\r?\n/', $sql) as $line) {
                if (str_starts_with(trim($line), '--') || trim($line) === '') {
                    continue;
                }
                if (preg_match('/^DELIMITER\s+(\S+)/i', trim($line), $match)) {
                    $delimiter = $match[1];

                    continue;
                }
                $buffer .= $line."\n";
                if (str_ends_with(trim($buffer), $delimiter)) {
                    $statement = substr(trim($buffer), 0, -strlen($delimiter));
                    if (preg_match('/\b(?:DROP\s+DATABASE|CREATE\s+DATABASE|USE\s+dt07_homestay)\b/i', $statement)) {
                        throw new RuntimeException('Database-level statement refused.');
                    }
                    DB::unprepared($statement);
                    $buffer = '';
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        return true;
    }
}
