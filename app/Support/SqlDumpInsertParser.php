<?php

namespace App\Support;

class SqlDumpInsertParser
{
    public function __construct(private readonly string $sql)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rowsForTable(string $table): array
    {
        $rows = [];

        foreach ($this->insertStatementsForTable($table) as $statement) {
            if (! preg_match('/INSERT INTO `'.preg_quote($table, '/').'` \((.*?)\) VALUES\s*(.*)$/s', rtrim($statement, ";\r\n\t "), $matches)) {
                continue;
            }

            preg_match_all('/`([^`]+)`/', $matches[1], $columnMatches);
            $columns = $columnMatches[1];

            foreach ($this->parseRows($matches[2]) as $values) {
                if (count($columns) === count($values)) {
                    $rows[] = array_combine($columns, $values);
                }
            }
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private function insertStatementsForTable(string $table): array
    {
        $needle = 'INSERT INTO `'.$table.'`';
        $statements = [];
        $offset = 0;

        while (($start = stripos($this->sql, $needle, $offset)) !== false) {
            $end = $this->findStatementEnd($start);

            if ($end === null) {
                break;
            }

            $statements[] = substr($this->sql, $start, $end - $start + 1);
            $offset = $end + 1;
        }

        return $statements;
    }

    private function findStatementEnd(int $start): ?int
    {
        $length = strlen($this->sql);
        $inString = false;
        $escaped = false;

        for ($i = $start; $i < $length; $i++) {
            $character = $this->sql[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                    continue;
                }

                if ($character === '\\') {
                    $escaped = true;
                    continue;
                }

                if ($character === "'") {
                    $inString = false;
                }

                continue;
            }

            if ($character === "'") {
                $inString = true;
                continue;
            }

            if ($character === ';') {
                return $i;
            }
        }

        return null;
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function parseRows(string $valuesSql): array
    {
        $rows = [];
        $currentRow = [];
        $token = '';
        $inRow = false;
        $inString = false;
        $escaped = false;
        $length = strlen($valuesSql);

        for ($i = 0; $i < $length; $i++) {
            $character = $valuesSql[$i];

            if ($inString) {
                $token .= $character;

                if ($escaped) {
                    $escaped = false;
                    continue;
                }

                if ($character === '\\') {
                    $escaped = true;
                    continue;
                }

                if ($character === "'") {
                    $inString = false;
                }

                continue;
            }

            if ($character === "'") {
                $inString = true;
                $token .= $character;
                continue;
            }

            if (! $inRow) {
                if ($character === '(') {
                    $inRow = true;
                    $currentRow = [];
                    $token = '';
                }

                continue;
            }

            if ($character === ',') {
                $currentRow[] = $this->decodeValue($token);
                $token = '';
                continue;
            }

            if ($character === ')') {
                $currentRow[] = $this->decodeValue($token);
                $rows[] = $currentRow;
                $currentRow = [];
                $token = '';
                $inRow = false;
                continue;
            }

            $token .= $character;
        }

        return $rows;
    }

    private function decodeValue(string $token): mixed
    {
        $token = trim($token);

        if (strcasecmp($token, 'NULL') === 0) {
            return null;
        }

        if ($token === '') {
            return '';
        }

        if ($token[0] === "'" && str_ends_with($token, "'")) {
            return strtr(substr($token, 1, -1), [
                '\\0' => "\0",
                "\\'" => "'",
                '\\"' => '"',
                '\\b' => "\b",
                '\\n' => "\n",
                '\\r' => "\r",
                '\\t' => "\t",
                '\\Z' => chr(26),
                '\\\\' => '\\',
            ]);
        }

        return is_numeric($token) ? $token + 0 : $token;
    }
}
