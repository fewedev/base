<?php

declare(strict_types=1);

namespace FeWeDev\Base;

use Exception;
use JsonSerializable;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Json
{
    /** @var Variables */
    protected $variables;

    /**
     * @param Variables|null $variables
     */
    public function __construct(Variables $variables = null)
    {
        if ($variables === null) {
            $variables = new Variables();
        }

        $this->variables = $variables;
    }

    /**
     * @param string|null $encodedValue
     *
     * @return mixed
     */
    public function decode(?string $encodedValue)
    {
        if (! $this->variables->isEmpty($encodedValue)) {
            return json_decode((string)$encodedValue, true);
        }

        return null;
    }

    /**
     * @param mixed $valueToEncode
     * @param bool  $unescaped
     * @param bool  $pretty
     * @param bool  $checkEncoding
     *
     * @return string|null
     */
    public function encode(
        $valueToEncode,
        bool $unescaped = false,
        bool $pretty = false,
        bool $checkEncoding = false
    ): ?string {
        if ($checkEncoding) {
            $checkValue = is_array($valueToEncode) ? $valueToEncode : [$valueToEncode];
            try {
                $this->checkEncoding($checkValue);
            } catch (Exception $exception) {
                return null;
            }
        }

        $options = 0;

        if ($unescaped) {
            $options |= JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        }

        if ($pretty) {
            $options |= JSON_PRETTY_PRINT;
        }

        $result = $options > 0 ? json_encode($valueToEncode, $options) : json_encode($valueToEncode);

        return $result === false ? null : $result;
    }

    /**
     * @param array<mixed, mixed> $array
     * @param string              $parentPath
     *
     * @return void
     * @throws Exception
     */
    public function checkEncoding(array $array, string $parentPath = ''): void
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->checkEncoding($value, empty($parentPath) ? (string)$key : ($parentPath . ':' . $key));
                continue;
            } elseif (is_object($value)) {
                if ($value instanceof JsonSerializable) {
                    $value = $value->jsonSerialize();
                } else {
                    if (method_exists($value, '__toString')) {
                        $value = (string)$value;
                    } else {
                        continue;
                    }
                }
            }

            if (is_string($value) && mb_detect_encoding($value, null, true) === false) {
                throw new Exception(
                    sprintf(
                        'Invalid encoding found in path: %s with value: %s',
                        empty($parentPath) ? $key : ($parentPath . ':' . $key),
                        $value
                    )
                );
            }
        }
    }
}
