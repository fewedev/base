<?php

declare(strict_types=1);

namespace FeWeDev\Base;

use stdClass;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Variables
{
    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isEmpty($value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value) && strlen(trim($value)) === 0) {
            return true;
        }

        if (is_array($value)) {
            return count($value) === 0;
        }

        if ($value instanceof stdClass) {
            return count((array)$value) == 0;
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
                if (strcasecmp((string)$oldDataAttributeCode, 'updated_at') === 0) {
                    continue;
                }

                if (! array_key_exists($oldDataAttributeCode, $newData)) {
                    $changedAttributeCodes[] = $oldDataAttributeCode;
                } else {
                    $newDataAttributeValue = $newData[ $oldDataAttributeCode ];

                    if (is_scalar($oldDataAttributeValue) && is_scalar($newDataAttributeValue)) {
                        if (is_numeric($oldDataAttributeValue) && is_numeric($newDataAttributeValue)) {
                            if ((float)$oldDataAttributeValue != (float)$newDataAttributeValue) {
                                $changedAttributeCodes[] = $oldDataAttributeCode;
                            }
                        } elseif (is_bool($oldDataAttributeValue) && is_bool($newDataAttributeValue)) {
                            if ($oldDataAttributeValue !== $newDataAttributeValue) {
                                $changedAttributeCodes[] = $oldDataAttributeValue;
                            }
                        } elseif (is_bool($oldDataAttributeValue) && is_numeric($newDataAttributeValue)) {
                            if ($oldDataAttributeValue !== ($newDataAttributeValue !== 0)) {
                                $changedAttributeCodes[] = $oldDataAttributeValue;
                            }
                        } elseif (is_numeric($oldDataAttributeValue) && is_bool($newDataAttributeValue)) {
                            if (($oldDataAttributeValue !== 0) !== $newDataAttributeValue) {
                                $changedAttributeCodes[] = $oldDataAttributeValue;
                            }
                        } else {
                            if (strcasecmp((string)$oldDataAttributeValue, (string)$newDataAttributeValue) !== 0) {
                                $changedAttributeCodes[] = $oldDataAttributeCode;
                            }
                        }
                    }

                    unset($newData[ $oldDataAttributeCode ]);
                }
            }
        }

        return array_values(array_unique($changedAttributeCodes));
    }
}
