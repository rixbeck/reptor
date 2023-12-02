<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor\PhpOffice;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate as AbstractCoordinate;

class Coordinate extends AbstractCoordinate
{
    public static function calcDistance(string $origin, string $target): array
    {
        $origin = self::coordinateFromString($origin);
        $target = self::coordinateFromString($target);

        return [
            self::columnIndexFromString($target[0]) - self::columnIndexFromString($origin[0]),
            $target[1] - $origin[1],
        ];
    }

    public static function addRangeDistance(string $range, array $distance): string
    {
        $addresses = explode(':', $range);
        $coords = array_map(function ($row) use ($distance) {
            $indexes = self::indexesFromString($row);

            return [$indexes[0] + $distance[0], $indexes[1] + $distance[1]];
        }, $addresses);

        return implode(
            ':',
            array_map(fn ($row) => self::stringFromColumnIndex($row[0]).$row[1], $coords)
        );
    }

    public static function cellCoordinate(int $pColumn, int $pRow): string
    {
        $pCol = self::stringFromColumnIndex($pColumn);

        return $pCol . $pRow;
    }

    public static function convertRangeFormat(string $range): string
    {
        // converts ranges from format 'Sheet1!$B$4:$BL$5' to 'B4:BL5'
        $range = explode('!', $range)[1];
        $range = str_replace('$', '', $range);

        return $range;
    }

    public static function isRangeInside(string $range, string $inside): bool
    {
        $range = explode(':', $range);
        $inside = explode(':', $inside);

        return
            self::columnIndexFromString($range[0][0]) >= self::columnIndexFromString($inside[0][0]) &&
            self::columnIndexFromString($range[1][0]) <= self::columnIndexFromString($inside[1][0]) &&
            $range[0][1] >= $inside[0][1] &&
            $range[1][1] <= $inside[1][1];
    }
}
