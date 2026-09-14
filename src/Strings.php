<?php

declare(strict_types=1);

namespace FeWeDev\Base;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Strings
{
    protected Variables $variables;

    public function __construct(?Variables $variables = null)
    {
        if (null === $variables) {
            $variables = new Variables();
        }

        $this->variables = $variables;
    }

    /**
     * Generates with the help ov the  method generateUUID a GUID Version 5.
     * A GUID has the format %08s-%04s-%04x-%04x-%12s e.g. :
     * 32c8 f9ff-4352-545e-964c-7d5167e396ba .
     */
    public function generateGUID5(): string
    {
        $hash = $this->generateUUID();

        return sprintf(
            '%08s-%04s-%04x-%04x-%12s', // 32 bits for "time_low"
            substr($hash, 0, 8), // 16 bits for "time_mid"
            substr($hash, 8, 4), // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 5
            (hexdec(substr($hash, 12, 4)) & 0x0FFF) | 0x5000, // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            (hexdec(substr($hash, 16, 4)) & 0x3FFF) | 0x8000, // 48 bits for "node"
            substr($hash, 20, 12)
        );
    }

    /**
     * Generate a 40 characters long uuid. The uuid is a sha1 hash over a
     * string build with the magento base url, the micro time and a 7 digit
     * long random number e.g. : 216908463793cd292cad4756525ed23dafcf7af0 .
     *
     * @return string a 40 character long hex value
     */
    public function generateUUID(string $namespace = 'foo'): string
    {
        $pid = getmypid();
        $time = (string) microtime(true);
        $rand = (string) mt_rand(1000000, 9999999);

        return sha1($namespace.'|'.$pid.'|'.$time.'|'.$rand);
    }

    /**
     * Clean non UTF-8 characters.
     */
    public function cleanString(string $string): string
    {
        $cleaned = mb_convert_encoding($string, 'UTF-8');

        return false === $cleaned ? $string : $cleaned;
    }

    /**
     * Retrieve string length using UTF-8 charset.
     */
    public function strlen(string $string): int
    {
        return mb_strlen($string, 'UTF-8');
    }

    public function cutString(string $string, int $maxLength): string
    {
        if (strlen($string) > $maxLength) {
            $length = strpos(wordwrap($string, $maxLength - 3), "\n");

            return (false === $length ? substr($string, 0) : substr($string, 0, $length)).'...';
        }

        return $string;
    }

    /**
     * @param array<string, mixed> $placeholderValues
     *
     * @throws \InvalidArgumentException
     */
    public function replacePlaceHolders(
        string $text,
        array $placeholderValues,
        string $opening = '{',
        string $closing = '}',
        bool $useKeyIfMissing = false,
        bool $allowEmptyValue = false
    ): string {
        do {
            preg_match(
                sprintf('/%s[\w_-]+?%s/', preg_quote($opening), preg_quote($closing)),
                $text,
                $textMatch,
                PREG_OFFSET_CAPTURE
            );

            $hasMatch = !$this->variables->isEmpty($textMatch);

            if ($hasMatch) {
                $textMatch = $textMatch[0];

                [$placeHolder, $position] = $textMatch;

                $placeHolderKey = substr($placeHolder, strlen($opening), strlen($closing) * -1);

                $placeHolderValue = array_key_exists(
                    $placeHolderKey,
                    $placeholderValues
                ) ? $this->variables->stringValue(
                    $placeholderValues[$placeHolderKey]
                ) : ($useKeyIfMissing ? $placeHolderKey : '');

                if ($this->variables->isEmpty($placeHolderValue) && !$allowEmptyValue) {
                    throw new \InvalidArgumentException(sprintf('Missing value for placeholder: %s', $placeHolderKey));
                }

                $text = substr_replace(
                    $text,
                    $placeHolderValue,
                    $position,
                    strlen($placeHolder)
                );
            }
        } while ($hasMatch);

        return $text;
    }
}
