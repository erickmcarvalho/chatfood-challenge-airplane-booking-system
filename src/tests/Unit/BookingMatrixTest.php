<?php

namespace Tests\Unit;

use Illuminate\Support\Arr;
use PHPUnit\Framework\TestCase;

class BookingMatrixTest extends TestCase
{
    private const SITS_NUMBER = 156;
    private const SEAT_COLUMNS = 3;
    private const SITS_BY_SIDE = self::SITS_NUMBER / 2;
    private const SEAT_ROWS = self::SITS_BY_SIDE / self::SEAT_COLUMNS;

    private function mockMatrix()
    {
        $matrix = [[], []];
        $airplaneId = 0;

        for ($y = 0; $y < static::SEAT_ROWS; $y++) {
            $matrix[0][] = [];
            $matrix[1][] = [];

            for ($x = 0; $x < static::SEAT_COLUMNS; $x++) {
                $matrix[0][$y][] = [
                    'id' => $airplaneId++,
                    'name' => chr(65 + $x).($y + 1),
                    'isFree' => true
                ];

                $matrix[1][$y][] = [
                    'id' => $airplaneId++,
                    'name' => chr(65 + static::SEAT_COLUMNS + $x).($y + 1),
                    'isFree' => true
                ];
            }
        }

        return $matrix;
    }

    private function makeBookingMatrix(int $booking, int &$columns, int &$rows): void {
        if ($booking % static::SEAT_COLUMNS === 0) {
            $columns = static::SEAT_COLUMNS;
            $rows = ($booking / $columns);
        } else {
            $columns = intval($booking / static::SEAT_COLUMNS) + ($booking % static::SEAT_COLUMNS);
            $rows = intval(ceil($booking / $columns));
        }
    }

    private function searchAndReserveSits(array &$sits, int $booking): ?array
    {
        $columns = 0; $rows = 0;
        $this->makeBookingMatrix($booking, $columns, $rows);

        $pending = $booking;

        for ($i = 0; $i < static::SEAT_ROWS && $pending > 0; $i += static::SEAT_COLUMNS) {
            // Left side
            $side = 0;
            $pending = $booking;
            $reserves = [];

            $start = $i / static::SEAT_COLUMNS;

            for ($y = $start; $y < $rows + $start && $pending > 0; $y++) {
                for ($x = 0; $x < $columns && $pending > 0; $x++) {
                    if ($sits[$side][$y][$x]['isFree'] === true) {
                        $reserves[] = [
                            'seat' => $sits[$side][$y][$x]['name'],
                            'side' => $side,
                            'row' => $y,
                            'column' => $x
                        ];

                        --$pending;
                    }
                }
            }

            // Right side
            if ($pending > 0) {
                $side = 1;
                $pending = $booking;
                $reserves = [];

                for ($y = $start; $y < $rows + $start && $pending > 0; $y++) {
                    for ($x = 0; $x < $columns && $pending > 0; $x++) {
                        if ($sits[$side][$y][2 - $x]['isFree'] === true) {
                            $reserves[] = [
                                'seat' => $sits[$side][$y][2 - $x]['name'],
                                'side' => $side,
                                'row' => $y,
                                'column' => 2 - $x
                            ];

                            --$pending;
                        }
                    }
                }
            }

            // Both sides
            if ($pending > 0) {
                $pending = $booking;
                $reserves = [];

                for ($y = $start; $y < $rows && $pending > 0; $y++) {
                    for ($ix = 0; $ix < $columns * 2 && $pending > 0; $ix++) {
                        $side = intval($ix / static::SEAT_COLUMNS);
                        $x = intval($ix % static::SEAT_COLUMNS);

                        if ($sits[$side][$y][$x]['isFree'] === true) {
                            $reserves[] = [
                                'seat' => $sits[$side][$y][$x]['name'],
                                'side' => $side,
                                'row' => $y,
                                'column' => $x
                            ];

                            --$pending;
                        }
                    }
                }
            }
        }

        // There are no seats available
        if ($pending > 0) {
           return null;
        }

        // Apply booking
        foreach ($reserves as $reserve) {
            $sits[$reserve['side']][$reserve['row']][$reserve['column']]['isFree'] = false;
        }

        return Arr::sort($reserves, function ($item) {
            return $item['side'] + $item['row'] + $item['column'];
        });
    }

    /**
     * Testing 3-seat reservation in a single row, starting by window
     *
     * Result:
     *
     * A1, B1, C1
     */
    public function test_all_on_the_same_line()
    {
        // 3 sits
        $booking = 3;

        // Test
        $matrix = $this->mockMatrix();
        $reserves = $this->searchAndReserveSits($matrix, $booking);

        $this->assertIsArray($reserves);
        $this->assertFalse($matrix[0][0][0]['isFree']); // A1
        $this->assertFalse($matrix[0][0][1]['isFree']); // B1
        $this->assertFalse($matrix[0][0][2]['isFree']); // B2
    }

    /**
     * Testing 4-seat reservation in two row, starting by window
     *
     * Result:
     *
     * A1, B1, A2, B2
     */
    public function test_reserve_matrix_2x2_sits_a1_b1_a2_b2()
    {
        // 4 sits
        $booking = 4;

        // Test
        $matrix = $this->mockMatrix();
        $reserves = $this->searchAndReserveSits($matrix, $booking);

        $this->assertIsArray($reserves);
        $this->assertFalse($matrix[0][0][0]['isFree']); // A1
        $this->assertFalse($matrix[0][0][1]['isFree']); // B1
        $this->assertFalse($matrix[0][1][0]['isFree']); // C1
        $this->assertFalse($matrix[0][1][1]['isFree']); // A2
        $this->assertTrue($matrix[0][0][2]['isFree']);  // B2
        $this->assertTrue($matrix[0][1][2]['isFree']);  // C2
    }

    /**
     * Testing 5-seat reservation in two row, starting by window
     *
     * Result:
     *
     * A1, B1, C1, A2, B2
     */
    public function test_reserve_matrix_3x2_sits_a1_b1_c1_a2_b2()
    {
        // 5 sits
        $booking = 5;

        // Test
        $matrix = $this->mockMatrix();
        $reserves = $this->searchAndReserveSits($matrix, $booking);

        $this->assertIsArray($reserves);
        $this->assertFalse($matrix[0][0][0]['isFree']); // A1
        $this->assertFalse($matrix[0][0][1]['isFree']); // B1
        $this->assertFalse($matrix[0][0][2]['isFree']); // C1
        $this->assertFalse($matrix[0][1][0]['isFree']); // A2
        $this->assertFalse($matrix[0][1][1]['isFree']); // B2
        $this->assertTrue($matrix[0][1][2]['isFree']);  // C2
    }

    /**
     * Testing 6-seat reservation in two row, starting by window
     *
     * Result:
     *
     * A1, B1, C1, A2, B2, C2
     */
    public function test_reserve_matrix_3x3_sits_a1_b1_c1_a2_b2_c2()
    {
        // 6 sits
        $booking = 6;

        // Test
        $matrix = $this->mockMatrix();
        $reserves = $this->searchAndReserveSits($matrix, $booking);

        $this->assertIsArray($reserves);
        $this->assertFalse($matrix[0][0][0]['isFree']); // A1
        $this->assertFalse($matrix[0][0][1]['isFree']); // B1
        $this->assertFalse($matrix[0][0][2]['isFree']); // C1
        $this->assertFalse($matrix[0][1][0]['isFree']); // A2
        $this->assertFalse($matrix[0][1][1]['isFree']); // B2
        $this->assertFalse($matrix[0][1][2]['isFree']); // C2
    }

    /**
     * Testing 4-seat reservation in two row, right side, starting by window
     *
     * Result:
     *
     * E1, F1, E2, F2
     */
    public function test_reserve_matrix_2x2_sits_e1_f1_e2_f2()
    {
        // 4 sits
        $booking = 4;

        // Test
        $matrix = $this->mockMatrix();

        // Reserve A B C sits
        foreach ($matrix[0] as &$rows) {
            foreach ($rows as &$sit) {
                $sit['isFree'] = false;
            }
        }

        $reserves = $this->searchAndReserveSits($matrix, $booking);

        $this->assertIsArray($reserves);
        $this->assertTrue($matrix[1][0][0]['isFree']);  // D1 (free)
        $this->assertFalse($matrix[1][0][1]['isFree']); // E1 (busy)
        $this->assertFalse($matrix[1][0][2]['isFree']); // F1 (busy, window)
        $this->assertTrue($matrix[1][1][0]['isFree']);  // D2 (free)
        $this->assertFalse($matrix[1][1][1]['isFree']); // E2 (busy)
        $this->assertFalse($matrix[1][1][2]['isFree']); // F2 (busy, window)
    }

    /**
     * Testing 4-seat reservation in two row, both sides, close to each other
     *
     * Result:
     *
     * C1, C2, D1, D2
     */
    public function test_reserve_matrix_2x2_both_sides_sits_c1_d1_c2_d2()
    {
        // 4 sits
        $booking = 4;

        // Test
        $matrix = $this->mockMatrix();

        // Reserve A B sits
        for ($i = 0; $i < static::SEAT_ROWS; $i++) {
            $matrix[0][$i][0]['isFree'] = false; // A
            $matrix[0][$i][1]['isFree'] = false; // B
            $matrix[1][$i][1]['isFree'] = false; // E
            $matrix[1][$i][2]['isFree'] = false; // F
        }

        $reserves = $this->searchAndReserveSits($matrix, $booking);

        $this->assertIsArray($reserves);
        $this->assertFalse($matrix[0][0][2]['isFree']);  // C1
        $this->assertFalse($matrix[0][1][2]['isFree']);  // C2
        $this->assertFalse($matrix[1][0][0]['isFree']);  // D1
        $this->assertFalse($matrix[1][1][0]['isFree']);  // D2
    }

    /**
     * Challenge example test 1
     *
     * Marco: 4 people;
     * Gerard: 2 people; Result:
     * Marco seats: 'A1', 'B1', 'A2', 'B2';
     * Gerard seats: 'E1', 'F1';
     */
    public function test_challenge_example_1()
    {
        // Database
        $matrix = $this->mockMatrix();

        // Marco booking
        $result = $this->searchAndReserveSits($matrix, 4);

        $this->assertIsArray($result);
        $this->assertFalse($matrix[0][0][0]['isFree']); // A1
        $this->assertFalse($matrix[0][0][1]['isFree']); // B1
        $this->assertFalse($matrix[0][1][0]['isFree']); // A2
        $this->assertFalse($matrix[0][1][1]['isFree']); // B2

        $marco = Arr::pluck($result, "seat");

        // Gerard booking
        $result = $this->searchAndReserveSits($matrix, 2);

        $this->assertIsArray($result);
        $this->assertFalse($matrix[1][0][1]['isFree']); // E1
        $this->assertFalse($matrix[1][0][2]['isFree']); // F1

        $gerard = Arr::pluck($result, "seat");

        // Assert contents
        $this->assertEquals("A1,B1,A2,B2", implode(",", $marco));
        $this->assertEquals("E1,F1", implode(",", $gerard));
    }

    /**
     * Challenge example test 2
     *
     * Iosu: 2 people;
     * Oriol: 5 people;
     * David: 2 people; Result:
     * Iosu seats: 'A1', 'B1';
     * Oriol seats: 'D1', 'E1', 'F1', 'E2', 'F2';
     * David seats: 'A2', 'B2';
     */
    public function test_challenge_example_2()
    {
        // Database
        $matrix = $this->mockMatrix();

        // Iosu booking
        $result = $this->searchAndReserveSits($matrix, 2);

        $this->assertIsArray($result);
        $this->assertFalse($matrix[0][0][0]['isFree']); // A1
        $this->assertFalse($matrix[0][0][1]['isFree']); // B1

        $iosu = Arr::pluck($result, "seat");

        // Oriol booking
        $result = $this->searchAndReserveSits($matrix, 5);

        $this->assertIsArray($result);
        $this->assertFalse($matrix[1][0][0]['isFree']); // D1
        $this->assertFalse($matrix[1][0][1]['isFree']); // E1
        $this->assertFalse($matrix[1][0][2]['isFree']); // F1
        $this->assertFalse($matrix[1][1][1]['isFree']); // E2
        $this->assertFalse($matrix[1][1][2]['isFree']); // F2

        $oirol = Arr::pluck($result, "seat");

        // David booking
        $result = $this->searchAndReserveSits($matrix, 2);

        $this->assertIsArray($result);
        $this->assertFalse($matrix[0][1][0]['isFree']); // A2
        $this->assertFalse($matrix[0][1][1]['isFree']); // B2

        $david = Arr::pluck($result, "seat");

        // Assert contents
        $this->assertEquals("A1,B1", implode(",", $iosu));
        $this->assertEquals("D1,E1,F1,E2,F2", implode(",", $oirol));
        $this->assertEquals("A2,B2", implode(",", $david));
    }

    /**
     * Challenge example test 3
     *
     * Iosu: 2 people;
     * Gerard: 2 people; Result:
     * Iosu seats: 'A1', 'B1';
     * Gerard seats: 'E1', 'F1';
     */
    public function test_challenge_example_3()
    {
        // Database
        $matrix = $this->mockMatrix();

        // Iosu booking
        $result = $this->searchAndReserveSits($matrix, 2);

        $this->assertIsArray($result);
        $this->assertFalse($matrix[0][0][0]['isFree']); // A1
        $this->assertFalse($matrix[0][0][1]['isFree']); // B1

        $iosu = Arr::pluck($result, "seat");

        // Gerard booking
        $result = $this->searchAndReserveSits($matrix, 2);

        $this->assertIsArray($result);
        $this->assertFalse($matrix[1][0][1]['isFree']); // E1
        $this->assertFalse($matrix[1][0][2]['isFree']); // F1

        $gerard = Arr::pluck($result, "seat");

        // Assert contents
        $this->assertEquals("A1,B1", implode(",", $iosu));
        $this->assertEquals("E1,F1", implode(",", $gerard));
    }
}
