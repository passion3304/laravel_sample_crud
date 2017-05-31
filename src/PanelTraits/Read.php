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
        if (! $this->entry) {
            $this->entry = $this->model->findOrFail($id);
            $this->entry = $this->entry->withFakes();
        }

        return $this->entry;
    }

    /**
     * Make the query JOIN all relationships used in the columns, too,
     * so there will be less database queries overall.
     */
    public function autoEagerLoadRelationshipColumns()
    {
        $relationships = $this->getColumnsRelationships();

        if (count($relationships)) {
            $this->with($relationships);
        }
    }

    /**
     * Get all entries from the database.
     *
     * @return [Collection of your model]
     */
    public function getEntries()
    {
        $this->autoEagerLoadRelationshipColumns();

        $entries = $this->query->get();

        // add the fake columns for each entry
        foreach ($entries as $key => $entry) {
            $entry->addFakes($this->getFakeColumnsAsArray());
        }

        return $entries;
    }

    /**
     * Get all entries from the database with conditions.
     *
     * @param string $length the number of records requested
     * @param string $skip how many to skip
     * @param string $orderBy the column to order by
     * @param string $orderDirection how to order asc/desc
     * @param string $filter what string to filter the name by
     *
     * @return [Collection of your model]
     */
    public function getEntriesWithConditions(
        $length = null,
        $skip = 0,
        $orderBy = null,
        $orderDirection = 'asc',
        $filter = null
    ) {
        $modifiers = 0;

        if ($filter !== null) {
            $modifiers = 1;
            $entries = $this->query->where(function ($query) use ($filter) {
                foreach ($this->columns as $column) {
                    if ($this->getColumnQuery($column) !== null) {
                        $query->orWhere(
                            $this->getColumnQuery($column),
                            'like',
                            '%'.$filter.'%'
                        );
                    }
                }
            });
        }

        if ($length !== null) {
            $modifiers = 1;
            $entries = $this->query->skip($skip)->take($length);
        }

        if ($orderBy !== null) {
            $modifiers = 1;
            $entries = $this->query->orderBy($orderBy, $orderDirection);
        }

        if ($modifiers == 0) {
            $entries = $this->query->get();
        } else {
            $entries = $entries->get();
        }

        // add the fake columns for each entry
        foreach ($entries as $key => $entry) {
            $entry->addFakes($this->getFakeColumnsAsArray());
        }

        return $entries;
    }

    /**
     * Receives a filter and tries to get all the columns to be filtered by that filter *Work in progress*.
     *
     * @param $column
     * @return null|string
     */
    private function getColumnQuery($column)
    {
        if (isset($column['type']) && $column['type'] == 'model_function') {
            return;
        }

        if (is_array($column)) {
            return $column['name'];
        }

        return Model::resolveConnection()->getQueryGrammar()->wrap($column);
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
     * Check if the create/update form has upload fields.
     * Upload fields are the ones that have "upload" => true defined on them.
     * @param  [form] create / update / both - defaults to 'both'
     * @param  [id] id of the entity - defaults to false
     * @return bool
     */
    public function hasUploadFields($form, $id = false)
    {
        $fields = $this->getFields($form, $id);
        $upload_fields = array_where($fields, function ($value, $key) {
            return isset($value['upload']) && $value['upload'] == true;
        });

        return count($upload_fields) ? true : false;
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

    /**
     * Set the number of rows that should be show on the table page (list view).
     */
    public function setDefaultPageLength($value)
    {
        $this->default_page_length = $value;
    }

    /**
     * Get the number of rows that should be show on the table page (list view).
     */
    public function getDefaultPageLength()
    {
        // return the custom value for this crud panel, if set using setPageLength()
        if ($this->default_page_length) {
            return $this->default_page_length;
        }

        // otherwise return the default value in the config file
        if (config('backpack.crud.default_page_length')) {
            return config('backpack.crud.default_page_length');
        }

        return 25;
    }

    /*
    |--------------------------------------------------------------------------
    |                                AJAX TABLE
    |--------------------------------------------------------------------------
    */

    /**
     * Tell the list view to use AJAX for loading multiple rows.
     */
    public function enableAjaxTable()
    {
        $this->ajax_table = true;
    }

    /**
     * Check if ajax is enabled for the table view.
     * @return bool
     */
    public function ajaxTable()
    {
        return $this->ajax_table;
    }

    /**
     * Get the HTML of the cells in a table row, for a certain DB entry.
     * @param  Entity $entry A db entry of the current entity;
     * @return array         Array of HTML cell contents.
     */
    public function getRowViews($entry)
    {
        $response = [];
        foreach ($this->columns as $key => $column) {
            $response[] = $this->getCellView($column, $entry);
        }

        return $response;
    }

    /**
     * Get the HTML of a cell, using the column types.
     * @param  array $column
     * @param  Entity $entry A db entry of the current entity;
     * @return HTML
     */
    public function getCellView($column, $entry)
    {
        // if column type not set, show as text
        if (! isset($column['type'])) {
            return \View::make('crud::columns.text')
                            ->with('crud', $this)
                            ->with('column', $column)
                            ->with('entry', $entry)
                            ->render();
        } else {
            // if the column has been overwritten show that one
            if (view()->exists('vendor.backpack.crud.columns.'.$column['type'])) {
                return \View::make('vendor.backpack.crud.columns.'.$column['type'])
                                ->with('crud', $this)
                                ->with('column', $column)
                                ->with('entry', $entry)
                                ->render();
            } else {
                // show the column from the package
                if (view()->exists('crud::columns.'.$column['type'])) {
                    return \View::make('crud::columns.'.$column['type'])
                                    ->with('crud', $this)
                                    ->with('column', $column)
                                    ->with('entry', $entry)
                                    ->render();
                } else {
                    return \View::make('crud::columns.text')
                                    ->with('crud', $this)
                                    ->with('column', $column)
                                    ->with('entry', $entry)
                                    ->render();
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    |                                EXPORT BUTTONS
    |--------------------------------------------------------------------------
    */

    /**
     * Tell the list view to show the DataTables export buttons.
     */
    public function enableExportButtons()
    {
        $this->export_buttons = true;
    }

    /**
     * Check if export buttons are enabled for the table view.
     * @return bool
     */
    public function exportButtons()
    {
        return $this->export_buttons;
    }
}
