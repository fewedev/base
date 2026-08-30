<?php

declare(strict_types=1);

use FeWeDev\Base\Arrays;
use PHPUnit\Framework\TestCase;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ArraysKsortRecursiveTest extends TestCase
{
    public function testKsortRecursiveSimple(): void
    {
        $arrays = new Arrays();

        $data = ['a' => 'a', 'c' => 'c', 'b' => 'b'];

        $arrays->ksortRecursive($data);

        $this->assertSame(['a' => 'a', 'b' => 'b', 'c' => 'c'], $data);
    }

    public function testKsortRecursiveComplex(): void
    {
        $arrays = new Arrays();

        $data = ['a' => 'a', 'c' => ['e' => 'e', 'd' => 'd'], 'b' => 'b'];

        $arrays->ksortRecursive($data);

        $this->assertSame(['a' => 'a', 'b' => 'b', 'c' => ['d' => 'd', 'e' => 'e']], $data);
    }

    public function testKsortRecursiveNatural(): void
    {
        $arrays = new Arrays();

        $data = ['key10' => 'value10', 'key9' => 'value9', 'key11' => 'value11'];

        $arrays->ksortRecursive($data, SORT_NATURAL);

        $this->assertSame(['key9' => 'value9', 'key10' => 'value10', 'key11' => 'value11'], $data);
    }

    public function testKsortRecursiveCase(): void
    {
        $arrays = new Arrays();

        $data = ['a' => 'a', 'C' => 'C', 'b' => 'b'];

        $arrays->ksortRecursive($data, SORT_STRING | SORT_FLAG_CASE);

        $this->assertSame(['a' => 'a', 'b' => 'b', 'C' => 'C'], $data);
    }

}
