<?php

abstract class TestCase
{
    protected function assertSame($expected, $actual, $message = '')
    {
        if ($expected !== $actual) {
            $this->fail($message ?: sprintf("Failed asserting that %s is identical to %s.", var_export($actual, true), var_export($expected, true)));
        }
    }

    protected function assertTrue($condition, $message = '')
    {
        if ($condition !== true) {
            $this->fail($message ?: 'Failed asserting that condition is true.');
        }
    }

    protected function assertFalse($condition, $message = '')
    {
        if ($condition !== false) {
            $this->fail($message ?: 'Failed asserting that condition is false.');
        }
    }

    protected function assertEmpty($value, $message = '')
    {
        if (!empty($value)) {
            $this->fail($message ?: 'Failed asserting that value is empty.');
        }
    }

    protected function assertIsArray($value, $message = '')
    {
        if (!is_array($value)) {
            $this->fail($message ?: 'Failed asserting that value is an array.');
        }
    }

    protected function assertContains($needle, $haystack, $message = '')
    {
        $found = false;

        if (is_array($haystack)) {
            $found = in_array($needle, $haystack, true);
        } elseif (is_string($haystack)) {
            $found = strpos($haystack, $needle) !== false;
        }

        if (!$found) {
            $this->fail($message ?: sprintf('Failed asserting that "%s" is contained.', $needle));
        }
    }

    protected function fail($message)
    {
        throw new Exception($message);
    }
}
