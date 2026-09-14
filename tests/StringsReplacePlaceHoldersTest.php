<?php

declare(strict_types=1);

use FeWeDev\Base\Strings;
use PHPUnit\Framework\TestCase;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class StringsReplacePlaceHoldersTest extends TestCase
{
    public function testReplacePlaceHolders(): void
    {
        $text = 'Hello {name}';
        $placeholderValues = ['name' => 'World'];

        $strings = new Strings();
        $text = $strings->replacePlaceHolders($text, $placeholderValues);

        $this->assertEquals('Hello World', $text);
    }

    public function testReplacePlaceHoldersMarkers(): void
    {
        $text = 'Hello #name#';
        $placeholderValues = ['name' => 'World'];

        $strings = new Strings();
        $text = $strings->replacePlaceHolders($text, $placeholderValues, '#', '#');

        $this->assertEquals('Hello World', $text);
    }

    public function testReplacePlaceHoldersInvalidMarkers(): void
    {
        $text = 'Hello {name}';
        $placeholderValues = ['name' => 'World'];

        $strings = new Strings();
        $text = $strings->replacePlaceHolders($text, $placeholderValues, '#', '#');

        $this->assertEquals('Hello {name}', $text);
    }

    public function testReplacePlaceHoldersMissingKey(): void
    {
        $text = 'Hello {name}';
        $placeholderValues = ['value' => 'World'];

        $strings = new Strings();
        try {
            $strings->replacePlaceHolders($text, $placeholderValues);
        } catch (\InvalidArgumentException $exception) {
            $this->assertEquals('Missing value for placeholder: name', $exception->getMessage());
        }
    }

    public function testReplacePlaceHoldersMissingKeyNoException(): void
    {
        $text = 'Hello {name}';
        $placeholderValues = ['value' => 'World'];

        $strings = new Strings();
        $text = $strings->replacePlaceHolders($text, $placeholderValues, '{', '}', false, true);

        $this->assertEquals('Hello ', $text);
    }

    public function testReplacePlaceHoldersMissingKeyUseIt(): void
    {
        $text = 'Hello {name}';
        $placeholderValues = ['value' => 'World'];

        $strings = new Strings();
        $text = $strings->replacePlaceHolders($text, $placeholderValues, '{', '}', true);

        $this->assertEquals('Hello name', $text);
    }
}