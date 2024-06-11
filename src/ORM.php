<?php

declare(strict_types=1);

namespace ManasahTech\Data;

use Exception;
use ReflectionClass;
use ReflectionProperty;
use ManasahTech\Data\Database;
use ManasahTech\Data\Attributes\Table;
use ManasahTech\Data\Attributes\Column;
use ManasahTech\Data\Attributes\ID;
// use ManasahTech\Data\Attributes\Unique;

final class ORM
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function createTable(string $entityClass)
    {
        $reflectionClass = new ReflectionClass($entityClass);
        $tableName = $this->getTableName($reflectionClass);
        $columns = $this->getColumns($reflectionClass);
        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (\n" . implode(",\n", $columns) . "\n)";
        $this->db->query($sql);
    }

    private function getTableName(ReflectionClass $reflectionClass): string
    {
        $attributes = $reflectionClass->getAttributes(Table::class);
        if (empty($attributes)) {
            throw new Exception("No table attribute found on class " . $reflectionClass->getName());
        }

        $tableAttribute = $attributes[0]->newInstance();
        return $tableAttribute->name;
    }

    private function getColumns(ReflectionClass $reflectionClass): array
    {
        $columns = [];
        foreach ($reflectionClass->getProperties() as $property) {
            $column = $this->getColumnDefinition($property);
            if ($column) {
                $columns[] = $column;
            }
        }
        return $columns;
    }

    private function getColumnDefinition(ReflectionProperty $property): ?string
    {
        $columnAttributes = $property->getAttributes(Column::class);
        if (empty($columnAttributes)) {
            return null;
        }

        $columnAttribute = $columnAttributes[0]->newInstance();
        $idAttribute = $property->getAttributes(ID::class);
        // $uniqueAttribute = $property->getAttributes(Unique::class);

        $name = $columnAttribute->name ?? $property->getName();
        $type = $this->mapType($columnAttribute);
        $columnDefinition = "{$name} {$type}";

        if (!empty($idAttribute)) {
            $columnDefinition .= " AUTO_INCREMENT PRIMARY KEY";
        }
        if ($columnAttribute->unique || !empty($uniqueAttribute)) {
            $columnDefinition .= " UNIQUE";
        }
        if (!$columnAttribute->nullable) {
            $columnDefinition .= " NOT NULL";
        }
        if ($columnAttribute->default !== null) {
            $default = is_string($columnAttribute->default) ? "'{$columnAttribute->default}'" : $columnAttribute->default;
            $columnDefinition .= " DEFAULT {$default}";
        }
        if ($columnAttribute->generated !== null) {
            $columnDefinition .= " GENERATED ALWAYS AS ({$columnAttribute->generated}) STORED";
        }

        return $columnDefinition;
    }

    private function mapType(Column $columnAttribute): string
    {
        switch ($columnAttribute->type) {
            case 'int':
                return 'INT';
            case 'float':
                return 'FLOAT';
            case 'string':
                return 'VARCHAR(' . ($columnAttribute->length ?? 255) . ')';
            case 'text':
                return 'TEXT';
            case 'bool':
                return 'BOOLEAN';
            case 'decimal':
                $precision = $columnAttribute->precision ?? 10;
                $scale = $columnAttribute->scale ?? 0;
                return "DECIMAL({$precision},{$scale})";
            case 'enum':
                return "ENUM(" . implode(", ", array_map(fn($val) => "'{$val}'", $columnAttribute->options)) . ")";
            default:
                throw new Exception("Unsupported data type '{$columnAttribute->type}'");
        }
    }
}