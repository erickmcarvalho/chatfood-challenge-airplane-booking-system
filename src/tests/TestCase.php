<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function makeMockData(array $data, array $replaces = [], array $removes = [])
    {
        if (count($replaces) > 0) {
            foreach ($replaces as $key => $value) {
                $data[$key] = $value;
            }
        }

        if (count($removes) > 0) {
            foreach ($removes as $key) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
