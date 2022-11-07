<?php

/*
 * Copyright (c) 2022 PartyCompass.
 */

namespace src\JsonDB;

use JsonException;
use src\InstantCache\InstantCache;

class JsonDB
{
    /**
     * @param string $table
     */
    public function __construct(public string $table)
    {
    }

    /**
     * @param string $idCol
     * @param string $idVal
     * @param string $column
     * @param mixed $value
     * @return void
     * @throws JsonException
     */
    public function change(string $idCol, string $idVal, string $column, mixed $value): void
    {
        $content = $this->load();

        // Search through each row of $content until $row[$idCol] = $idVal
        foreach ($content as $row) {
            if ($row[$idCol] === $idVal) {
                // Only change if the value is different
                if ($row[$column] !== $value) {
                    $row[$column] = $value;
                    $this->save($content);
                    return;
                }
                return;
            }
        }
    }

    /**
     * @throws JsonException
     */
    private function load(): array
    {
        $filename = __DIR__ . '/../../tables/' . $this->table . '.json';
        if (!file_exists($filename)) {
            file_put_contents($filename, '[]');
        }

        $content = file_get_contents($filename);

        if ($content === false) {
            return [];
        }

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    private function save(array $content): void
    {
        $filename = __DIR__ . '/../../tables/' . $this->table . '.json';

        file_put_contents($filename, json_encode($content, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        $this->clearCache();
    }

    /**
     * @return void
     */
    public function clearCache(): void
    {
        InstantCache::delete('jsondb_' . $this->table);
    }

    /**
     * @param string $idCol
     * @param string $idVal
     * @return void
     * @throws JsonException
     */
    public function delete(string $idCol, string $idVal): void
    {
        $content    = $this->load();
        $newContent = [];
        foreach ($content as $row) {
            if ($row[$idCol] !== $idVal) {
                $newContent[] = $row;
            }
        }
        $this->save($newContent);
    }

    /**
     * @param string $idCol
     * @param string $idVal
     * @return bool
     * @throws JsonException
     */
    public function exists(string $idCol, string $idVal): bool
    {
        $content = $this->load();

        // Search through each row of $content until $content[$idCol] = $idVal
        foreach ($content as $row) {
            if ($row[$idCol] === $idVal) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws JsonException
     */
    public function changeSelect(string $idCol, string $idVal, array $data): void
    {
        $content = $this->load();

        InstantCache::set('rowsUpdated', 0);

        // Search through each row of $content until $content[$idCol] = $idVal
        foreach ($content as $rowId => $row) {
            if ($row[$idCol] === $idVal) {
                // Only change if the value is different
                foreach ($data as $column => $value) {
                    if (isset($row[$column]) && $row[$column] !== $value) {
                        $content[$rowId][$column] = $value;
                        InstantCache::set('rowsUpdated', InstantCache::get('rowsUpdated') + 1);
                    } elseif (!isset($row[$column])) {
                        $content[$rowId][$column] = $value;
                        InstantCache::set('rowsUpdated', InstantCache::get('rowsUpdated') + 1);
                    }
                }
                $this->save($content);
                return;
            }
        }

        $this->add($idCol, $idVal, $data);
    }

    public function removeColumnSingle(string $idCol, string $idVal, string $column): void
    {
        $content = $this->load();

        foreach ($content as $rowId => $row) {
            if ($row[$idCol] === $idVal && isset($row[$column])) {
                unset($content[$rowId][$column]);
                $this->save($content);
                return;
            }
        }
    }

    public function removeColumnAll(string $column): void
    {
        $content = $this->load();

        foreach ($content as $rowId => $row) {
            if (isset($row[$column])) {
                unset($content[$rowId][$column]);
            }
        }
        $this->save($content);
    }

    public function getPage(
        int $page = 1,
        int $perpage = 3,
        array $where = [],
        bool $sortBy = true,
        string $sortByColumn = 'id',
        string $sortByType = 'string',
        bool $descending = true,
        bool $allowLike = false,
        bool $and = true,
        array $furtherWheres = [],
    ): array {
        return self::paginate(
            $this->selectWhere(
                    $where,
                    $allowLike,
                    $sortBy,
                    $sortByColumn,
                    $sortByType,
                    $descending,
                    $and,
                    $furtherWheres
                ),
            $page,
            $perpage
        );
    }

    private static function paginate(array $data, int $page = 1, int $perpage = 3): array
    {
        $page    = max($page, 1);
        $perpage = max($perpage, 1);
        $offset  = ($page - 1) * $perpage;
        return array_slice($data, $offset, $perpage);
    }

    /**
     */
    public function add(string $idCol, string $idVal, array $data): void
    {
        $data[$idCol] = $idVal;

        $content   = $this->load();
        $content[] = $data;
        $this->save($content);
    }

    public function getMaxId(): int
    {
        $content = $this->load();
        $max     = 0;
        foreach ($content as $row) {
            if (isset($row['id']) && $row['id'] > $max) {
                $max = $row['id'];
            }
        }
        return $max;
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function deleteAll(): void
    {
        $this->save([]);
    }

    /**
     * @param string $idCol
     * @param string $idVal
     * @param bool $useCache
     * @return array
     * @throws JsonException
     */
    public function get(string $idCol, string $idVal, bool $useCache = false): array
    {
        if ($useCache && InstantCache::isset('jsondb_' . $this->table)) {
            $content = InstantCache::get('jsondb_' . $this->table);
        } else {
            $content = $this->load();
            InstantCache::set('jsondb_' . $this->table, $content);
        }

        foreach ($content as $row) {
            if ($row[$idCol] === $idVal) {
                return $row;
            }
        }

        return [];
    }

    /**
     * @param string $idCol
     * @param string $idVal
     * @param string ...$columns
     * @return array
     * @throws JsonException
     */
    public function getSelect(string $idCol, string $idVal, string ...$columns): array
    {
        $content = $this->load();
        foreach ($content as $row) {
            if ($row[$idCol] === $idVal) {
                $result = [];
                foreach ($columns as $column) {
                    $result[$column] = $row[$column] ?? throw new \Exception('Column not given for row.');
                }
                return $result;
            }
        }
        return [];
    }

    /**
     * @param array $where
     * @param bool $allowLike
     * @param bool $sortBy
     * @param string $sortByColumn
     * @param string $sortByType
     * @param bool $descending
     * @param bool $whereIsAnd
     * @return array
     * @throws JsonException
     */
    public function selectWhere(
        array $where = [],
        bool $allowLike = false,
        bool $sortBy = true,
        string $sortByColumn = 'id',
        string $sortByType = 'string',
        bool $descending = true,
        bool $whereIsAnd = true,
        array $furtherWheres = [],
    ): array {
        $content = $this->getContent();

        $result = [];

        foreach ($content as $row) {
            $addThisRow = $whereIsAnd || $where === [];
            foreach ($where as $column => $value) {
                $matchType = $this->getMatchType($allowLike, $value);

                $value = $this->getTrimmedValue($matchType, $value);

                if (!isset($row[$column])) {
                    $addThisRow = false;
                    break;
                }

                $match = $this->isMatch($matchType, $row[$column], $value);

                if ($whereIsAnd) {
                    if (!$match) {
                        $addThisRow = false;
                        break;
                    }
                } elseif ($match) {
                    $addThisRow = true;
                    break;
                }
            }

            foreach ($furtherWheres as $furtherWhere) {
                foreach ($furtherWhere as $column => $value) {
                    $matchType = $this->getMatchType($allowLike, $value);

                    $value = $this->getTrimmedValue($matchType, $value);

                    $match = $this->isMatch($matchType, $row[$column], $value);

                    if (!$match) {
                        $addThisRow = false;
                        break;
                    }
                }
            }

            if ($addThisRow) {
                $result[] = $row;
            }
        }

        if ($sortBy) {
            $result = match ($sortByType) {
                'int'    => self::sortByInt($sortByColumn, $result, $descending),
                'date'   => self::sortByDate($sortByColumn, $result, $descending),
                'string' => self::sortByString($sortByColumn, $result, $descending),
                default  => $result,
            };
        }

        return $result;
    }

    /**
     * @return array
     * @throws JsonException
     */
    public function getContent(): array
    {
        return $this->load();
    }

    /**
     * @param string $column
     * @param array $data
     * @param bool $descending
     * @return array
     */
    public static function sortByInt(string $column, array $data, bool $descending = true): array
    {
        usort(
            $data,
            static function ($a, $b) use ($column) {
                return $a[$column] <=> $b[$column];
            }
        );
        return $descending ? array_reverse($data) : $data;
    }

    /**
     * @param string $column
     * @param array $data
     * @param bool $descending
     * @return array
     */
    public static function sortByDate(string $column, array $data, bool $descending = true): array
    {
        usort(
            $data,
            static function ($a, $b) use ($column) {
                return strtotime($a[$column]) <=> strtotime($b[$column]);
            }
        );
        return $descending ? array_reverse($data) : $data;
    }

    /**
     * @param string $column
     * @param array $data
     * @param bool $descending
     * @return array
     */
    public static function sortByString(string $column, array $data, bool $descending = true): array
    {
        usort(
            $data,
            static function ($a, $b) use ($column) {
                return strcmp($a[$column], $b[$column]);
            }
        );
        return $descending ? array_reverse($data) : $data;
    }

    /**
     * @param bool $allowLike
     * @param mixed $value
     * @return int
     */
    private function getMatchType(bool $allowLike, mixed $value): int
    {
        return match (true) {
            !$allowLike                                                => 3,
            str_starts_with($value, '%') && str_ends_with($value, '%') => 0,
            str_starts_with($value, '%')                               => 1,
            str_ends_with($value, '%')                                 => 2,
            default                                                    => 3,
        };
    }

    /**
     * @param int $matchType
     * @param mixed $value
     * @return mixed|string
     */
    private function getTrimmedValue(int $matchType, mixed $value): mixed
    {
        return match ($matchType) {
            0       => substr($value, 1, -1),
            1       => substr($value, 1),
            2       => substr($value, 0, -1),
            default => $value,
        };
    }

    /**
     * @param int $matchType
     * @param $row
     * @param mixed $value
     * @return bool
     */
    private function isMatch(int $matchType, ?string $row, mixed $value): bool
    {
        return match ($matchType) {
            0       => str_contains(mb_strtolower($row ?? ''), mb_strtolower($value)),
            1       => str_ends_with(mb_strtolower($row ?? ''), mb_strtolower($value)),
            2       => str_starts_with(mb_strtolower($row ?? ''), mb_strtolower($value)),
            default => mb_strtolower($row ?? '') === mb_strtolower($value),
        };
    }
}
