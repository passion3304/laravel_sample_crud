<?php

namespace Backpack\CRUD\PanelTraits;

trait Read
{
   /*
    |--------------------------------------------------------------------------
    |                                   READ
    |--------------------------------------------------------------------------
    */

    /**
     * Find and retrieve an entry in the database or fail.
     *
     * @param  [int] The id of the row in the db to fetch.
     *
     * @return [Eloquent Collection] The row in the db.
     */
    public function getEntry($id)
    {
        $entry = $this->model->findOrFail($id);

        return $entry->withFakes();
    }

    /**
     * Get all entries from the database.
     *
     * @return [Collection of your model]
     */
    public function getEntries()
    {
        $entries = $this->query->get();

        // add the fake columns for each entry
        foreach ($entries as $key => $entry) {
            $entry->addFakes($this->getFakeColumnsAsArray());
        }

        return $entries;
    }

    /**
     * Get the fields for the create or update forms.
     *
     * @param  [form] create / update / both - defaults to 'both'
     * @param  [integer] the ID of the entity to be edited in the Update form
     *
     * @return [array] all the fields that need to be shown and their information
     */
    public function getFields($form, $id = false)
    {
        switch (strtolower($form)) {
            case 'create':
                return $this->getCreateFields();
                break;

            case 'update':
                return $this->getUpdateFields($id);
                break;

            default:
                return $this->getCreateFields();
                break;
        }
    }

    /**
     * Enable the DETAILS ROW functionality:.
     *
     * In the table view, show a plus sign next to each entry.
     * When clicking that plus sign, an AJAX call will bring whatever content you want from the EntityCrudController::showDetailsRow($id) and show it to the user.
     */
    public function enableDetailsRow()
    {
        $this->details_row = true;
    }

    /**
     * Disable the DETAILS ROW functionality:.
     */
    public function disableDetailsRow()
    {
        $this->details_row = false;
    }
}
