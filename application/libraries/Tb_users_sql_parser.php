<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tb_users_sql_parser
{
    public function parse($sql, $tableFields)
    {
        if (!preg_match('//u', $sql)) throw new RuntimeException('Berkas SQL harus menggunakan UTF-8.');
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);
        $statements = $this->statements($sql);
        $rows = [];
        foreach ($statements as $statement) {
            if ($this->isDumpMetadata($statement)) continue;
            if (!preg_match('/^INSERT\s+INTO\s+`?tb_users`?\s*(?:\(([^)]*)\))?\s+VALUES\s+(.+)$/is', $statement, $match)) {
                throw new RuntimeException('Hanya dump phpMyAdmin tb_users dan INSERT INTO tb_users yang diizinkan.');
            }
            $columns = isset($match[1]) && trim($match[1]) !== '' ? $this->columns($match[1]) : $tableFields;
            if (!$columns || count($columns) !== count(array_unique($columns))) {
                throw new RuntimeException('Daftar kolom SQL tidak valid atau berulang.');
            }
            foreach ($columns as $column) {
                if (!in_array($column, $tableFields, true)) throw new RuntimeException('Kolom SQL tidak dikenal: ' . $column . '.');
            }
            foreach ($this->tuples($match[2]) as $values) {
                if (count($values) !== count($columns)) throw new RuntimeException('Jumlah nilai tidak sesuai dengan daftar kolom.');
                $rows[] = array_combine($columns, $values);
                if (count($rows) > 1000) throw new RuntimeException('Maksimal 1.000 baris user per berkas.');
            }
        }
        if (!$rows) throw new RuntimeException('Tidak ada data INSERT tb_users di dalam berkas.');
        return $rows;
    }

    private function isDumpMetadata($statement)
    {
        $statement = trim($statement);
        if (preg_match('/^(?:START\s+TRANSACTION|COMMIT)$/i', $statement)) return true;
        if (preg_match('/^SET\s+SQL_MODE\s*=\s*[\x27\"]NO_AUTO_VALUE_ON_ZERO[\x27\"]$/i', $statement)) return true;
        if (preg_match('/^SET\s+time_zone\s*=\s*[\x27\"]\+00:00[\x27\"]$/i', $statement)) return true;
        if (preg_match('/^CREATE\s+TABLE\s+`?tb_users`?\s*\(/is', $statement)) return true;
        if (preg_match('/^ALTER\s+TABLE\s+`?tb_users`?\s+(?:ADD\s+(?:PRIMARY\s+KEY|KEY)|MODIFY\s+`?id`?\b)/is', $statement)) return true;
        return false;
    }

    private function statements($sql)
    {
        $statements = [];
        $buffer = '';
        $length = strlen($sql);
        $quote = '';
        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';
            if ($quote !== '') {
                $buffer .= $char;
                if ($char === '\\' && $next !== '') {
                    $buffer .= $next;
                    $i++;
                } elseif ($char === $quote) {
                    if ($next === $quote) {
                        $buffer .= $next;
                        $i++;
                    } else {
                        $quote = '';
                    }
                }
                continue;
            }
            if ($char === "'" || $char === '`') {
                $quote = $char;
                $buffer .= $char;
                continue;
            }
            if ($char === '-' && $next === '-' && ($i + 2 >= $length || ctype_space($sql[$i + 2]))) {
                while ($i < $length && $sql[$i] !== "\n") $i++;
                $buffer .= ' ';
                continue;
            }
            if ($char === '#') {
                while ($i < $length && $sql[$i] !== "\n") $i++;
                $buffer .= ' ';
                continue;
            }
            if ($char === '/' && $next === '*') {
                $end = strpos($sql, '*/', $i + 2);
                if ($end === false) throw new RuntimeException('Komentar SQL tidak ditutup.');
                $i = $end + 1;
                $buffer .= ' ';
                continue;
            }
            if ($char === ';') {
                if (trim($buffer) !== '') $statements[] = trim($buffer);
                $buffer = '';
                continue;
            }
            $buffer .= $char;
        }
        if ($quote !== '') throw new RuntimeException('Tanda kutip SQL tidak ditutup.');
        if (trim($buffer) !== '') throw new RuntimeException('Setiap INSERT harus diakhiri titik koma.');
        return $statements;
    }

    private function columns($text)
    {
        $columns = [];
        foreach (explode(',', $text) as $part) {
            $part = trim($part);
            if (!preg_match('/^`?([A-Za-z_][A-Za-z0-9_]*)`?$/', $part, $match)) {
                throw new RuntimeException('Nama kolom SQL tidak valid.');
            }
            $columns[] = $match[1];
        }
        return $columns;
    }

    private function tuples($text)
    {
        $rows = [];
        $length = strlen($text);
        $i = 0;
        while (true) {
            $this->space($text, $i, $length);
            if ($i >= $length) break;
            if ($text[$i] !== '(') throw new RuntimeException('Format VALUES SQL tidak valid.');
            $i++;
            $values = [];
            while (true) {
                $this->space($text, $i, $length);
                $values[] = $this->value($text, $i, $length);
                $this->space($text, $i, $length);
                if ($i >= $length) throw new RuntimeException('Kurung VALUES SQL tidak ditutup.');
                if ($text[$i] === ')') {
                    $i++;
                    break;
                }
                if ($text[$i] !== ',') throw new RuntimeException('Pemisah nilai SQL tidak valid.');
                $i++;
            }
            $rows[] = $values;
            if (count($rows) > 1000) throw new RuntimeException('Maksimal 1.000 baris user per berkas.');
            $this->space($text, $i, $length);
            if ($i >= $length) break;
            if ($text[$i] !== ',') throw new RuntimeException('Bagian setelah VALUES tidak diizinkan.');
            $i++;
            $this->space($text, $i, $length);
            if ($i >= $length) throw new RuntimeException('VALUES tidak boleh diakhiri koma.');
        }
        return $rows;
    }

    private function value($text, &$i, $length)
    {
        if ($i >= $length) throw new RuntimeException('Nilai SQL kosong.');
        if ($text[$i] === "'") {
            $i++;
            $value = '';
            $closed = false;
            while ($i < $length) {
                $char = $text[$i++];
                if ($char === "'") {
                    if ($i < $length && $text[$i] === "'") {
                        $value .= "'";
                        $i++;
                    } else {
                        $closed = true;
                        break;
                    }
                } elseif ($char === '\\' && $i < $length) {
                    $escaped = $text[$i++];
                    $map = ['n' => "\n", 'r' => "\r", 't' => "\t", 'b' => "\x08", 'Z' => "\x1A", '0' => "\0"];
                    $value .= isset($map[$escaped]) ? $map[$escaped] : $escaped;
                } else {
                    $value .= $char;
                }
            }
            if (!$closed) throw new RuntimeException('String SQL tidak ditutup.');
            if (strpos($value, "\0") !== false) throw new RuntimeException('Karakter nol tidak diizinkan.');
            return $value;
        }
        $start = $i;
        while ($i < $length && $text[$i] !== ',' && $text[$i] !== ')') $i++;
        $raw = trim(substr($text, $start, $i - $start));
        if (strcasecmp($raw, 'NULL') === 0) return null;
        if (!preg_match('/^-?\d+(?:\.\d+)?$/', $raw)) throw new RuntimeException('Nilai SQL harus string, angka, atau NULL.');
        return $raw;
    }

    private function space($text, &$i, $length)
    {
        while ($i < $length && ctype_space($text[$i])) $i++;
    }
}
