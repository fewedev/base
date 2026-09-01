<?php

declare(strict_types=1);

use FeWeDev\Base\Variables;
use PHPUnit\Framework\TestCase;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class VariablesIsEmptyTest extends TestCase
{
    public function testNull(): void
    {
        $variables = new Variables();

        $result = $variables->isEmpty(null);

        $this->assertTrue($result);
    }

    public function testEmptyString(): void
    {
        $variables = new Variables();

        $result = $variables->isEmpty('');

        $this->assertTrue($result);
    }

    public function testNonEmptyString(): void
    {
        $variables = new Variables();

        $result = $variables->isEmpty('test');

        $this->assertFalse($result);
    }

    public function testZeroInteger(): void
    {
        $variables = new Variables();

        $result = $variables->isEmpty(0);

        $this->assertFalse($result);
    }

    public function testNonZeroInteger(): void
    {
        $variables = new Variables();

        $result = $variables->isEmpty(1);

        $this->assertFalse($result);
    }

    public function testFalseBoolean(): void
    {
        $variables = new Variables();

        $result = $variables->isEmpty(false);

        $this->assertFalse($result);
    }

    public function testTrueBoolean(): void
    {
        $variables = new Variables();

        $result = $variables->isEmpty(true);

        $this->assertFalse($result);
    }

    public function testEmptyArray(): void
    {
        $variables = new Variables();

        $result = $variables->isEmpty([]);

        $this->assertTrue($result);
    }

    public function testNonEmptyArray(): void
    {
        $variables = new Variables();

        $result = $variables->isEmpty(['test']);

        $this->assertFalse($result);
    }

    public function testEmptyStdClass(): void
    {
        $variables = new Variables();

        $result = $variables->isEmpty(new stdClass());

        $this->assertTrue($result);
    }

    public function testNonEmptyStdClass(): void
    {
        $variables = new Variables();

        $result = $variables->isEmpty((object) ['test']);

        $this->assertFalse($result);
    }
}
