<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Columns
{
    use ColumnsProtectedMethods;

    // ------------
    // COLUMNS
    // ------------

    /**
     * Get the CRUD columns for the current operation.
     *
     * @return array CRUD columns.
     */
    public function columns()
    {
        return $this->getOperationSetting('columns') ?? [];
    }

    /**
     * Add a bunch of column names and their details to the CRUD object.
     *
     * @param array|string $columns
     */
    public function setColumns($columns)
    {
        // clear any columns already set
        $this->removeAllColumns();

        // if array, add a column for each of the items
        if (is_array($columns) && count($columns)) {
            foreach ($columns as $key => $column) {
                // if label and other details have been defined in the array
                if (is_array($column)) {
                    $this->addColumn($column);
                } else {
                    $this->addColumn([
                        'name'  => $column,
                        'label' => mb_ucfirst($column),
                        'type'  => 'text',
                    ]);
                }
            }
        }

        if (is_string($columns)) {
            $this->addColumn([
                'name'  => $columns,
                'label' => mb_ucfirst($columns),
                'type'  => 'text',
            ]);
        }
    }

    /**
     * Add a column at the end of to the CRUD object's "columns" array.
     *
     * @param array|string $column
     *
     * @return self
     */
    public function addColumn($column)
    {
        $column = $this->makeSureColumnHasNeededAttributes($column);

        $this->addColumnToOperationSettings($column);

        return $this;
    }

    /**
     * Add multiple columns at the end of the CRUD object's "columns" array.
     *
     * @param array $columns
     */
    public function addColumns($columns)
    {
        if (count($columns)) {
            foreach ($columns as $key => $column) {
                $this->addColumn($column);
            }
        }
    }

    /**
     * Move the most recently added column after the given target column.
     *
     * @param string|array $targetColumn The target column name or array.
     */
    public function afterColumn($targetColumn)
    {
        $this->moveColumn($targetColumn, false);
    }

    /**
     * Move the most recently added column before the given target column.
     *
     * @param string|array $targetColumn The target column name or array.
     */
    public function beforeColumn($targetColumn)
    {
        $this->moveColumn($targetColumn);
    }

    /**
     * Move this column to be first in the columns list.
     *
     * @return bool|null
     */
    public function makeFirstColumn()
    {
        if (! $this->columns()) {
            return false;
        }

        $firstColumn = array_keys(array_slice($this->columns(), 0, 1))[0];
        $this->beforeColumn($firstColumn);
    }

    /**
     * Add the default column type to the given Column, inferring the type from the database column type.
     *
     * @param array $column
     *
     * @return array|bool
     */
    public function addDefaultTypeToColumn($column)
    {
        if (array_key_exists('name', (array) $column)) {
            $default_type = $this->getFieldTypeFromDbColumnType($column['name']);

            return array_merge(['type' => $default_type], $column);
        }

        return false;
    }

    /**
     * Remove a column from the CRUD panel by name.
     *
     * @param string $columnKey The column key.
     */
    public function removeColumn($columnKey)
    {
        $columnsArray = $this->columns();
        Arr::forget($columnsArray, $columnKey);
        $this->setOperationSetting('columns', $columnsArray);
    }

    /**
     * Remove multiple columns from the CRUD panel by name.
     *
     * @param array $columns Array of column names.
     */
    public function removeColumns($columns)
    {
        if (! empty($columns)) {
            foreach ($columns as $columnKey) {
                $this->removeColumn($columnKey);
            }
        }
    }

    /**
     * Remove all columns from the CRUD panel.
     */
    public function removeAllColumns()
    {
        $this->setOperationSetting('columns', []);
    }

    /**
     * Change attributes for multiple columns.
     *
     * @param array $columns
     * @param array $attributes
     */
    public function setColumnsDetails($columns, $attributes)
    {
        foreach ($columns as $columnKey) {
            $this->setColumnDetails($columnKey, $attributes);
        }
    }

    /**
     * Change attributes for a certain column.
     *
     * @param string $columnKey           Column key.
     * @param array  $attributesAndValues
     */
    public function setColumnDetails($columnKey, $attributesAndValues)
    {
        $columnsArray = $this->columns();

        if (isset($columnsArray[$columnKey])) {
            foreach ($attributesAndValues as $attributeName => $attributeValue) {
                $columnsArray[$columnKey][$attributeName] = $attributeValue;
            }
        }

        $this->setOperationSetting('columns', $columnsArray);
    }

    /**
     * Alias for setColumnDetails().
     * Provides a consistent syntax with Fields, Buttons, Filters modify functionality.
     *
     * @param string $column     Column name.
     * @param array  $attributes
     */
    public function modifyColumn($column, $attributes)
    {
        $this->setColumnDetails($column, $attributes);
    }

    /**
     * Set label for a specific column.
     *
     * @param string $column
     * @param string $label
     */
    public function setColumnLabel($column, $label)
    {
        $this->setColumnDetails($column, ['label' => $label]);
    }

    /**
     * Get the relationships used in the CRUD columns.
     *
     * @return array Relationship names
     */
    public function getColumnsRelationships()
    {
        $columns = $this->columns();

        return collect($columns)->pluck('entity')->reject(function ($value, $key) {
            return $value == null;
        })->toArray();
    }

    /**
     * Order the CRUD columns. If certain columns are missing from the given order array, they will be pushed to the
     * new columns array in the original order.
     *
     * @param array $order An array of column names in the desired order.
     */
    public function orderColumns($order)
    {
        $orderedColumns = [];
        foreach ($order as $columnName) {
            if (array_key_exists($columnName, $this->columns())) {
                $orderedColumns[$columnName] = $this->columns()[$columnName];
            }
        }

        if (empty($orderedColumns)) {
            return;
        }

        $remaining = array_diff_key($this->columns(), $orderedColumns);
        $this->setOperationSetting('columns', array_merge($orderedColumns, $remaining));
    }

    /**
     * Get a column by the id, from the associative array.
     *
     * @param int $column_number Placement inside the columns array.
     *
     * @return array Column details.
     */
    public function findColumnById($column_number)
    {
        $result = array_slice($this->columns(), $column_number, 1);

        return reset($result);
    }

    /**
     * Get the visibility priority for the actions column
     * in the CRUD table view.
     *
     * @return int The priority, from 1 to infinity. Lower is better.
     */
    public function getActionsColumnPriority()
    {
        return (int) $this->getOperationSetting('actionsColumnPriority') ?? 1;
    }

    /**
     * Set a certain priority for the actions column
     * in the CRUD table view. Usually set to 10000 in order to hide it.
     *
     * @param int $number The priority, from 1 to infinity. Lower is better.
     *
     * @return self
     */
    public function setActionsColumnPriority($number)
    {
        $this->setOperationSetting('actionsColumnPriority', (int) $number);

        return $this;
    }
}
