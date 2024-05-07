<?php

declare(strict_types=1);

namespace FeWeDev\Base;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Arrays
{
    /** @var Variables */
    protected $variables;

    /** @var Strings */
    protected $strings;

    public function __construct(Variables $variables = null, Strings $strings = null)
    {
        if (null === $variables) {
            $variables = new Variables();
        }

        $this->variables = $variables;

        if (null === $strings) {
            $strings = new Strings();
        }

        $this->strings = $strings;
    }

    /**
     * @param array<mixed, mixed> $array1
     * @param array<mixed, mixed> $array2
     *
     * @return array<mixed, mixed>
     */
    public function mergeArrays(array $array1, array $array2): array
    {
        $allKeysNumeric = true;

        $config1Keys = array_keys($array1);
        $config2Keys = array_keys($array2);

        foreach ($config1Keys as $configKey) {
            if (!is_numeric($configKey)) {
                $allKeysNumeric = false;

                break;
            }
        }

        if ($allKeysNumeric) {
            foreach ($config2Keys as $configKey) {
                if (!is_numeric($configKey)) {
                    $allKeysNumeric = false;

                    break;
                }
            }
        }

        if ($allKeysNumeric) {
            return array_merge($array1, $array2);
        }

        $combined = [];

        foreach ($array1 as $key1 => $value1) {
            $value2 = false !== array_key_exists($key1, $array2) ? $array2[$key1] : null;

            if ((!is_scalar($value1) && !is_array($value1)) || (!is_scalar($value2) && !is_array($value2))) {
                $combined[$key1] = $value1;

                continue;
            }

            if (is_scalar($value1)) {
                if (is_array($value2)) {
                    $combined[$key1] = $this->mergeArrays([$value1], $value2);
                } else {
                    $combined[$key1] = $value2;
                }

                continue;
            }

            if (is_array($value1)) {
                if (is_scalar($value2)) {
                    $combined[$key1] = $value1;
                    $combined[$key1][] = $value2;

                    continue;
                }

                if (is_array($value2)) {
                    $combined[$key1] = $this->mergeArrays($value1, $value2);
                }
            }
        }

        foreach ($array2 as $key2 => $value2) {
            if (!is_numeric($key2) && false !== !array_key_exists($key2, $combined)) {
                if (preg_match('/(.*)\+$/', $key2, $matches)) {
                    $key2 = array_key_exists(1, $matches) ? $matches[1] : null;

                    if (null !== $key2 && false !== array_key_exists($key2, $combined)) {
                        if (!is_array($combined[$key2])) {
                            $combined[$key2] = [$combined[$key2]];
                        }

                        $combined[$key2][] = $value2;
                    } else {
                        $combined[$key2] = $value2;
                    }
                } elseif (preg_match('/(.*)\-$/', $key2, $matches)) {
                    $key2 = array_key_exists(1, $matches) ? $matches[1] : null;

                    if (null !== $key2 && false !== array_key_exists($key2, $combined)) {
                        $combinedValue = $combined[$key2];

                        if (is_array($combinedValue)) {
                            foreach ($combinedValue as $combinedValueKey => $combinedValueValue) {
                                if ($combinedValueValue === $value2) {
                                    unset($combinedValue[$combinedValueKey]);
                                }
                            }

                            $combined[$key2] = $combinedValue;
                        } else {
                            if ($combinedValue === $value2) {
                                unset($combined[$key2]);
                            }
                        }
                    }
                } else {
                    $combined[$key2] = $value2;
                }
            }
        }

        return $combined;
    }

    /**
     * @return array<mixed, mixed>
     */
    public function stdClassToArray(\stdClass $stdClassObject): array
    {
        $array = (array) $stdClassObject;

        return $this->stdClassToArrayCheckValues($array);
    }

    /**
     * @param array<mixed, mixed> $array
     *
     * @return array<mixed, mixed>
     */
    public function stdClassToArrayCheckValues(array $array): array
    {
        foreach ($array as $key => $value) {
            if ($value instanceof \stdClass) {
                $array[$key] = $this->stdClassToArray($value);
            } elseif (is_array($value)) {
                $array[$key] = $this->stdClassToArrayCheckValues($value);
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * @param array<mixed, mixed> $array
     * @param mixed               $defaultValue
     *
     * @return mixed
     */
    public function getDirectValue(array $array, string $key, $defaultValue = null)
    {
        return array_key_exists($key, $array) ? $array[$key] : $defaultValue;
    }

    /**
     * @param array<mixed, mixed> $array
     * @param mixed               $defaultValue
     *
     * @return mixed
     */
    public function getValue(
        array $array,
        string $key,
        $defaultValue = null,
        bool $splitKey = true,
        string $checkedValue = null
    ) {
        if (empty($array) || (empty($key) && 0 != $key)) {
            return $defaultValue;
        }

        if (!$splitKey && array_key_exists($key, $array)) {
            return $array[$key];
        }

        $keys = $splitKey ? explode(':', $key) : [$key];

        $firstKey = trim((string) array_shift($keys));

        if ($checkedValue && 0 === count($keys)) {
            if (array_key_exists($firstKey, $array) && $array[$firstKey] == $checkedValue) {
                return $array;
            }

            foreach ($array as $arrayItem) {
                if (is_array($arrayItem) && array_key_exists($firstKey, $arrayItem)
                    && $arrayItem[$firstKey] == $checkedValue) {
                    return $arrayItem;
                }
            }
        } elseif (preg_match('/^\[([\w_-]+)=(.+)\]$/', $firstKey, $matches)) {
            $valueKey = array_key_exists(1, $matches) ? $matches[1] : null;
            $valueValue = array_key_exists(2, $matches) ? $matches[2] : null;

            foreach ($array as $arrayKey => $arrayValue) {
                if (is_array($arrayValue)) {
                    $valueKeys = array_keys($arrayValue);

                    foreach ($valueKeys as $nextValueKey) {
                        if (null !== $valueKey && 0 == strcasecmp($nextValueKey, $valueKey)
                            && array_key_exists($nextValueKey, $arrayValue)
                            && $arrayValue[$nextValueKey] == $valueValue) {
                            if (count($keys) > 0) {
                                return $this->getValue($arrayValue, join(':', $keys), $defaultValue);
                            }

                            return $arrayValue;
                        }
                    }
                } elseif (null !== $valueKey && 0 == strcasecmp((string) $arrayKey, $valueKey)
                    && $valueValue == $arrayValue) {
                    if (count($keys) > 0) {
                        return $this->getValue($array, join(':', $keys), $defaultValue);
                    }

                    return $array;
                }
            }
        } else {
            if (!$this->isAssociative($array) && !is_numeric($firstKey)) {
                $result = [];
                foreach ($array as $arrayValue) {
                    if (is_array($arrayValue)) {
                        $arrayResult = $this->getValue($arrayValue, $key, null, $splitKey, $checkedValue);
                        if (null !== $arrayResult) {
                            $result[] = $arrayResult;
                        }
                    }
                }

                return empty($result) ? $defaultValue : $result;
            }
            $arrayKeys = array_keys($array);

            foreach ($arrayKeys as $arrayKey) {
                if (0 == strcasecmp((string) $arrayKey, (string) $firstKey)) {
                    $result = array_key_exists($arrayKey, $array) ? $array[$arrayKey] : null;

                    if (is_array($result) && count($keys) > 0) {
                        return $this->getValue(
                            $result,
                            join(':', $keys),
                            $defaultValue,
                            $splitKey,
                            $checkedValue
                        );
                    }
                    if (0 === count($keys)) {
                        return $result;
                    }

                    return $defaultValue;
                }
            }
        }

        return $defaultValue;
    }

    /**
     * @param array<mixed, mixed> $array
     * @param mixed               $defaultValue
     *
     * @return int|mixed|string
     */
    public function getKey(array $array, string $value, $defaultValue = null)
    {
        foreach ($array as $key => $nextValue) {
            if (is_scalar($nextValue) && 0 == strcasecmp(strval($nextValue), $value)) {
                return $key;
            }
        }

        return $defaultValue;
    }

    /**
     * @param mixed               $value
     * @param array<mixed, mixed> $array
     */
    public function inArray($value, array $array, bool $caseSensitive = false): bool
    {
        $arrayCopy = $this->arrayCopy($array);

        if (false === $caseSensitive) {
            $function = function ($useInput) {
                return is_string($useInput) ? strtolower($useInput) : $useInput;
            };

            $arrayCopy = array_map($function, $arrayCopy);
        }

        return in_array(is_string($value) && false === $caseSensitive ? strtolower($value) : $value, $arrayCopy);
    }

    /**
     * @param array<mixed, mixed> $array
     *
     * @return array<mixed, mixed>
     */
    public function arrayCopy(array $array): array
    {
        $copy = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $copy[$key] = $this->arrayCopy($value);
            } elseif (is_object($value)) {
                $copy[$key] = clone $value;
            } else {
                $copy[$key] = $value;
            }
        }

        return $copy;
    }

    /**
     * @param array<mixed, mixed> $array
     */
    public function isAssociative(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        for ($iterator = count($array) - 1; $iterator; --$iterator) {
            if (!array_key_exists($iterator, $array)) {
                return true;
            }
        }

        return !array_key_exists(0, $array);
    }

    /**
     * @param array<mixed, mixed> $array
     */
    public function output(array $array, string $lineBreak = "\n", int $level = 0): string
    {
        $output = '';

        foreach ($array as $key => $value) {
            if (!empty($output)) {
                $output .= $lineBreak;
            }

            $output .= str_repeat('    ', $level).$key.': ';

            if (is_array($value)) {
                $valueOutput = $this->output($value, $lineBreak, $level + 1);

                if (!empty($valueOutput)) {
                    $output .= $lineBreak.$valueOutput;
                }
            } elseif (is_object($value) && is_callable([$value, '__toArray'])) {
                $valueOutput = $this->output($value->__toArray(), $lineBreak, $level + 1);

                if (!empty($valueOutput)) {
                    $output .= $lineBreak.$valueOutput;
                }
            } else {
                $value = is_scalar($value) ? strval($value) : null;

                if (null !== $value && strlen($value) > 4096) {
                    $value = '['.strlen($value).' bytes]';
                }

                $output .= $value;
            }
        }

        return $output;
    }

    /**
     * @param array<mixed, mixed> $array
     *
     * @return array<mixed, mixed>
     */
    public function getAllValues(array $array, string $regex): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (preg_match('/'.$regex.'/', (string) $key)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param array<mixed, mixed> $array
     * @param array<int, string>  $keys
     * @param mixed               $value
     *
     * @return array<mixed, mixed>
     */
    public function addDeepValue(
        array $array,
        array $keys,
        $value,
        bool $overwrite = true,
        bool $add = false,
        bool $deepAdd = false
    ): array {
        if (empty($keys)) {
            return $array;
        }

        if (count($keys) > 1) {
            $key = array_shift($keys);
            $firstArray = array_key_exists($key, $array) ? $array[$key] : [];
            if (!is_array($firstArray)) {
                $array[$key] = [$firstArray, $this->addDeepValue([], $keys, $value, $overwrite, $add)];
            } else {
                $array[$key] = $this->addDeepValue($firstArray, $keys, $value, $overwrite, $add);
            }
        } else {
            $key = array_shift($keys);
            if ($overwrite) {
                if (!$add || !array_key_exists($key, $array)) {
                    $array[$key] = $value;
                } else {
                    if (is_array($array[$key])) {
                        if (is_array($value)) {
                            foreach ($value as $valueKey => $valueValue) {
                                $array[$key] =
                                    $this->addDeepValue($array[$key], [$valueKey], $valueValue, $overwrite, $deepAdd);
                            }
                        } else {
                            $array[$key][] = $value;
                        }
                    } else {
                        if (is_array($value)) {
                            $array[$key] = [$array[$key]];
                            foreach ($value as $valueKey => $valueValue) {
                                $array[$key][$valueKey] = $valueValue;
                            }
                        } else {
                            $array[$key] = $value;
                        }
                    }
                }
            } else {
                if (array_key_exists($key, $array)) {
                    if (!is_array($array[$key])) {
                        $array[$key] = [$array[$key]];
                    }
                    $array[$key][] = $value;
                } else {
                    $array[$key] = $value;
                }
            }
        }

        return $array;
    }

    /**
     * Method to filter empty elements from XML.
     *
     * @param array<mixed, mixed> $array
     *
     * @return array<mixed, mixed>
     */
    public function arrayFilterRecursive(array $array): array
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                /** @var array<mixed, mixed> $value */
                $value = 1 === count($value) && array_key_exists(0, $value) && is_string($value[0])
                && $this->variables->isEmpty(trim($value[0])) ? $value[0] : $this->arrayFilterRecursive($value);
            }
        }

        return array_filter($array, function ($value) {
            return !$this->variables->isEmpty($value)
                && !(is_string($value) && $this->variables->isEmpty(trim($value)));
        });
    }

    /**
     * Returns the last element of the array.
     *
     * @param array<mixed, mixed> $array
     *
     * @return mixed
     */
    public function getLast(array $array)
    {
        return array_slice($array, -1)[0];
    }

    /**
     * @param array<mixed, mixed> $array1
     * @param array<mixed, mixed> $array2
     *
     * @return array<mixed, mixed>
     */
    public function arrayDiffRecursive(array $array1, array $array2, bool $strict = false): array
    {
        $result = [];

        foreach ($array1 as $key1 => $value1) {
            if (array_key_exists($key1, $array2)) {
                $value2 = $array2[$key1];

                if (is_array($value1) || is_array($value2)) {
                    if (is_array($value1) && is_array($value2)) {
                        $valuesDiff = $this->arrayDiffRecursive($value1, $value2, $strict);

                        if (!empty($valuesDiff)) {
                            $result[$key1] = $valuesDiff;
                        }
                    } else {
                        $result[$key1] = $value2;
                    }
                } else {
                    if (($strict && $value1 !== $value2) || (!$strict && $value1 != $value2)) {
                        $result[$key1] = $value2;
                    }
                }
            } else {
                $result[$key1] = $value1;
            }
        }

        foreach ($array2 as $key2 => $value2) {
            if (!array_key_exists($key2, $array1)) {
                $result[$key2] = $value2;
            }
        }

        return $result;
    }

    /**
     * @param array<mixed, mixed> $array
     *
     * @return array<mixed, mixed>
     */
    public function cleanStrings(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->cleanStrings($value);
            } else {
                if (is_string($value)) {
                    $array[$key] = $this->strings->cleanString($value);
                }
            }
        }

        return $array;
    }

    /**
     * @param array<string, mixed> $cache
     *
     * @return mixed
     */
    public function getCachedValue(array &$cache, \Closure $keyCallable, \Closure $valueCallable)
    {
        $key = $keyCallable();

        if (array_key_exists($key, $cache)) {
            $value = $cache[$key];
        } else {
            $value = $valueCallable();
            $cache[$key] = $value;
        }

        return $value;
    }
}
