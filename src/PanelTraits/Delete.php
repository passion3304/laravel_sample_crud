<?php

namespace Backpack\CRUD\PanelTraits;

trait Delete
{
    /*
    |--------------------------------------------------------------------------
    |                                   DELETE
    |--------------------------------------------------------------------------
    */

    /**
     * Delete a row from the database.
     *
     * @param  int $id The id of the item to be deleted.
     *
     * @return bool True if the item was deleted.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if the model was not found.
     *
     * TODO: should this delete items with relations to it too?
     */
    public function delete($id)
    {
        return (string) $this->model->findOrFail($id)->delete();
    }

    /**
     * Add the needed columns and buttons for the bulk delete functionality.
     */
    public function addBulkDeleteButton()
    {
        $this->addColumn([
            'type' => 'checkbox',
            'name' => 'bulk_actions',
            'label' => ' <input type="checkbox" class="crud_bulk_actions_main_checkbox" />',
            'priority' => 1,
            'searchLogic' => false,
            'orderable' => false,
            'visibleInModal' => false,
        ])->makeFirstColumn();

        $this->addColumn([
            'type' => 'custom_html',
            'name' => 'blank',
            'label' => ' ',
            'priority' => 1,
            'searchLogic' => false,
            'orderable' => false,
            'visibleInModal' => false,
        ])->makeFirstColumn();

        $this->addButton('bottom', 'bulk_delete', 'view', 'crud::buttons.bulk_delete');
    }
}
