<?php

declare(strict_types=1);

namespace FeWeDev\Base;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Variables
{
    /**
     * @param mixed $value
     */
    public function isEmpty($value): bool
    {
        if (null === $value) {
            return true;
        }

        if (is_string($value) && 0 === strlen(trim($value))) {
            return true;
        }

        if (is_array($value)) {
            return 0 === count($value);
        }

        if ($value instanceof \stdClass) {
            return 0 == count((array) $value);
        }

        return false;
    }

    /**
     * @param array<mixed, mixed> $oldData
     * @param array<mixed, mixed> $newData
     *
     * @return array<mixed, mixed>
     */
    public function getChangedData(array $oldData, array $newData): array
    {
        if ($this->isEmpty($oldData)) {
            $changedAttributeCodes = $this->isEmpty($newData) ? [] : array_keys($newData);
        } else {
            $changedAttributeCodes = empty($oldData) ? $newData : [];

            foreach ($oldData as $oldDataAttributeCode => $oldDataAttributeValue) {
                if (0 === strcasecmp((string) $oldDataAttributeCode, 'updated_at')) {
                    continue;
                }

                if (!array_key_exists($oldDataAttributeCode, $newData)) {
                    $changedAttributeCodes[] = $oldDataAttributeCode;
                } else {
                    $newDataAttributeValue = $newData[$oldDataAttributeCode];

                    if (is_scalar($oldDataAttributeValue) && is_scalar($newDataAttributeValue)) {
                        if (is_numeric($oldDataAttributeValue) && is_numeric($newDataAttributeValue)) {
                            if ((float) $oldDataAttributeValue != (float) $newDataAttributeValue) {
                                $changedAttributeCodes[] = $oldDataAttributeCode;
                            }
                        } elseif (is_bool($oldDataAttributeValue) && is_bool($newDataAttributeValue)) {
                            if ($oldDataAttributeValue !== $newDataAttributeValue) {
                                $changedAttributeCodes[] = $oldDataAttributeValue;
                            }
                        } elseif (is_bool($oldDataAttributeValue) && is_numeric($newDataAttributeValue)) {
                            if ($oldDataAttributeValue !== (0 !== $newDataAttributeValue)) {
                                $changedAttributeCodes[] = $oldDataAttributeValue;
                            }
                        } elseif (is_numeric($oldDataAttributeValue) && is_bool($newDataAttributeValue)) {
                            if ((0 !== $oldDataAttributeValue) !== $newDataAttributeValue) {
                                $changedAttributeCodes[] = $oldDataAttributeValue;
                            }
                        } else {
                            if (0 !== strcasecmp((string) $oldDataAttributeValue, (string) $newDataAttributeValue)) {
                                $changedAttributeCodes[] = $oldDataAttributeCode;
                            }
                        }
                    }

                    unset($newData[$oldDataAttributeCode]);
                }
            }
        }

        return array_values(array_unique($changedAttributeCodes));
    }

    /**
     * @param mixed $value
     */
    public function stringValue($value): string
    {
        if (\is_scalar($value)) {
            $value = (string) $value;
        } elseif (\is_object($value) && method_exists($value, '__toString')) {
            $value = (string) $value->__toString();
        } else {
            $value = var_export($value, true);
        }

        return $value;
    }
}
