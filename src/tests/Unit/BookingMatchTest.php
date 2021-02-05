<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BookingMatchTest extends TestCase
{
    private const SITS_NUMBER = 156;
    private const SEAT_COLUMNS = 3;
    private const SITS_BY_SIDE = self::SITS_NUMBER / 2;
    private const SEAT_ROWS = self::SITS_BY_SIDE / self::SEAT_COLUMNS;

    private function mockMatrix()
    {
        $matrix = [[], []];

        $airplaneId = 0;
        $side = 0;

        for ($i = 0; $i < static::SITS_NUMBER; $i++) {
            if ($i === static::SITS_BY_SIDE) {
                $side = 1;
            }

            $matrix[$side][$i / static::SITS_BY_SIDE][$i % static::SITS_BY_SIDE] = [
                'id' => $airplaneId++,
                'isFree' => true
            ];
        }

        return $matrix;
    }

    private function makeBookingMatrix(int $booking, int &$columns, int &$rows): void {
        if ($booking % 3 === 0) {
            $columns = 3;
            $rows = $booking / $columns;
        } else {
            $columns = intval($booking / 3) + ($booking % 3);
            $rows = ceil($booking / $columns);
        }
    }

    private function setSitsBusy(array &$sits, int $first, int $columns, int $rows)
    {
        for ($y = $first; $y < $rows; $y++) {
            //$sits[$y][0]['isFree'] = false;

            for ($x = 0; $x < $columns; $x++) {
                $sits[$y][$x]['isFree'] = false;
            }
        }

        return $sits;
    }

    public function test_all_on_the_same_line()
    {
        // Data
        $booking = 3;

        // Test
        $matrix = $this->mockMatrix();

        $columns = 0; $rows = 0;
        $this->makeBookingMatrix($booking, $columns, $rows);

        $this->setSitsBusy($matrix[0], 0, $columns, $rows);

        for ($y = 0; $y < $rows; $y++) {
            for ($x = 0; $x < $columns; $x++) {
                $this->assertFalse($matrix[0][$y][$x]['isFree']);
            }
        }
    }
}
