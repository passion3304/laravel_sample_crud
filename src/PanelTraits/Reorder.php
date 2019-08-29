<?php

namespace Backpack\CRUD\PanelTraits;

trait Reorder
{
    /*
    |--------------------------------------------------------------------------
    |                                   REORDER
    |--------------------------------------------------------------------------
    */

    /**
     * Change the order and parents of the given elements, according to the NestedSortable AJAX call.
     *
     * @param array $request The entire request from the NestedSortable AJAX Call.
     *
     * @return int The number of items whose position in the tree has been changed.
     */
    public function updateTreeOrder($request)
    {
        $count = 0;
        $primaryKey = $this->model->getKeyName();

        \DB::beginTransaction();
        foreach ($request as $key => $entry) {
            if ($entry['item_id'] != '' && $entry['item_id'] != null) {
                $item = $this->model->where($primaryKey, $entry['item_id'])->update([
                    'parent_id' => empty($entry['parent_id']) ? null : $entry['parent_id'],
                    'depth' => empty($entry['depth']) ? null : $entry['depth'],
                    'lft' => empty($entry['left']) ? null : $entry['left'],
                    'rgt' => empty($entry['right']) ? null : $entry['right'],
                ]);

                $count++;
            }
        }
        \DB::commit();

        return $count;
    }

    /**
     * Enable the Reorder functionality in the CRUD Panel for users that have the been given access to 'reorder' using:
     * $this->crud->allowAccess('reorder');.
     *
     * @param string $label     Column name that will be shown on the labels.
     * @param int    $max_level Maximum hierarchy level to which the elements can be nested (1 = no nesting, just reordering).
     */
    public function enableReorder($label = 'name', $max_level = 1)
    {
        $this->reorder = true;
        $this->reorder_label = $label;
        $this->reorder_max_level = $max_level;
    }

    /**
     * Disable the Reorder functionality in the CRUD Panel for all users.
     */
    public function disableReorder()
    {
        $this->reorder = false;
    }

    /**
     * Check if the Reorder functionality is enabled or not.
     *
     * @return bool
     */
    public function isReorderEnabled()
    {
        return $this->reorder;
    }
}
