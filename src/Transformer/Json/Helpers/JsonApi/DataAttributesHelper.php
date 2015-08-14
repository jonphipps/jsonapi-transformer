<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/25/15
 * Time: 5:05 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NilPortugues\Api\Transformer\Json\Helpers\JsonApi;

use NilPortugues\Api\Transformer\Helpers\RecursiveFormatterHelper;
use NilPortugues\Api\Transformer\Json\JsonApiTransformer;
use NilPortugues\Serializer\Serializer;

/**
 * Class DataAttributesHelper.
 */
final class DataAttributesHelper
{
    /**
     * @param \NilPortugues\Api\Mapping\Mapping[] $mappings
     * @param array                               $array
     *
     * @return array
     */
    public static function setResponseDataAttributes(array &$mappings, array &$array)
    {
        $attributes = [];
        $type = $array[Serializer::CLASS_IDENTIFIER_KEY];
        $idProperties = RecursiveFormatterHelper::getIdProperties($mappings, $type);

        foreach ($array as $propertyName => $value) {
            if (in_array($propertyName, $idProperties, true)) {
                continue;
            }

            $keyName = RecursiveFormatterHelper::camelCaseToUnderscore($propertyName);

            if (self::isScalarValue($value) && empty($mappings[$value[Serializer::SCALAR_TYPE]])) {
                $attributes[$keyName] = $value;
                continue;
            }

            if (is_array($value) && !array_key_exists(Serializer::CLASS_IDENTIFIER_KEY, $value)) {
                if (self::containsClassIdentifierKey($value)) {
                    $attributes[$keyName] = $value;
                }
            }
        }

        return [JsonApiTransformer::ATTRIBUTES_KEY => $attributes];
    }

    /**
     * @param array $input
     * @param bool  $foundIdentifierKey
     *
     * @return bool
     */
    private static function containsClassIdentifierKey(array $input, $foundIdentifierKey = false)
    {
        if (!is_array($input)) {
            return $foundIdentifierKey || false;
        }

        if (in_array(Serializer::CLASS_IDENTIFIER_KEY, $input, true)) {
            return true;
        }

        if (!empty($input[Serializer::SCALAR_VALUE])) {
            $input = $input[Serializer::SCALAR_VALUE];

            if (is_array($input)) {
                foreach ($input as $value) {
                    if (is_array($value)) {
                        $foundIdentifierKey = $foundIdentifierKey
                            || self::containsClassIdentifierKey($value, $foundIdentifierKey);
                    }
                }
            }
        }

        return !$foundIdentifierKey;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private static function isScalarValue($value)
    {
        return is_array($value)
        && array_key_exists(Serializer::SCALAR_TYPE, $value)
        && array_key_exists(Serializer::SCALAR_VALUE, $value);
    }
}
