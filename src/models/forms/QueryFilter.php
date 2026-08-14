<?php

declare(strict_types=1);

namespace app\models\forms;

use ReflectionNamedType;
use ReflectionProperty;
use yii\base\Model;

abstract class QueryFilter extends Model
{
    public function formName(): string
    {
        return '';
    }

    /**
     * @param array<string, mixed> $data
     */
    public function load($data, $formName = null): bool
    {
        return parent::load($this->sanitise($data), $formName);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function sanitise(array $data): array
    {
        $clean = [];

        foreach ($data as $name => $value) {
            if ($value === '' || $value === []) {
                continue;
            }

            $type = $this->propertyType($name);

            if ($type === 'int' && !is_numeric($value)) {
                continue;
            }

            if ($type === 'array') {
                if (!is_array($value)) {
                    continue;
                }

                $value = array_values(array_filter(
                    $value,
                    static fn(mixed $item): bool => $item !== '' && $item !== null,
                ));

                if ($value === []) {
                    continue;
                }
            }

            if ($type === 'string' && !is_scalar($value)) {
                continue;
            }

            $clean[$name] = $value;
        }

        return $clean;
    }

    private function propertyType(string $name): ?string
    {
        if (!property_exists($this, $name)) {
            return null;
        }

        $type = (new ReflectionProperty($this, $name))->getType();

        return $type instanceof ReflectionNamedType ? $type->getName() : null;
    }
}
