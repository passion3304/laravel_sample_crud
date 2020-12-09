<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

/**
 * Properties and methods used by the List operation.
 */
trait Read
{
    /**
     * Find and retrieve the id of the current entry.
     *
     * @return int|bool The id in the db or false.
     */
    public function getCurrentEntryId()
    {
        if ($this->entry) {
            return $this->entry->getKey();
        }

        $params = \Route::current()->parameters();

        return  // use the entity name to get the current entry
                // this makes sure the ID is corrent even for nested resources
                $this->getRequest()->input($this->entity_name) ??
                // otherwise use the next to last parameter
                array_values($params)[count($params) - 1] ??
                // otherwise return false
                false;
    }

    /**
     * Find and retrieve the current entry.
     *
     * @return \Illuminate\Database\Eloquent\Model|bool The row in the db or false.
     */
    public function getCurrentEntry()
    {
        $id = $this->getCurrentEntryId();

        if (! $id) {
            return false;
        }

        return $this->getEntry($id);
    }

    /**
     * Find and retrieve an entry in the database or fail.
     *
     * @param int The id of the row in the db to fetch.
     *
     * @return \Illuminate\Database\Eloquent\Model The row in the db.
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
     * Find and retrieve an entry in the database or fail.
     *
     * @param int The id of the row in the db to fetch.
     *
     * @return \Illuminate\Database\Eloquent\Model The row in the db.
     */
    public function getEntryWithoutFakes($id)
    {
        return $this->model->findOrFail($id);
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
     * @return array|\Illuminate\Database\Eloquent\Collection
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
     * Enable the DETAILS ROW functionality:.
     *
     * In the table view, show a plus sign next to each entry.
     * When clicking that plus sign, an AJAX call will bring whatever content you want from the EntityCrudController::showDetailsRow($id) and show it to the user.
     */
    public function enableDetailsRow()
    {
        $this->setOperationSetting('detailsRow', true);
    }

    /**
     * Disable the DETAILS ROW functionality:.
     */
    public function disableDetailsRow()
    {
        $this->setOperationSetting('detailsRow', false);
    }

    /**
     * Add two more columns at the beginning of the ListEntrie table:
     * - one shows the checkboxes needed for bulk actions
     * - one is blank, in order for evenual detailsRow or expand buttons
     * to be in a separate column.
     */
    public function enableBulkActions()
    {
        if ($this->getOperationSetting('bulkActions') == true) {
            return;
        }

        $this->setOperationSetting('bulkActions', true);

        $this->addColumn([
            'type'            => 'checkbox',
            'name'            => 'bulk_actions',
            'label'           => ' <input type="checkbox" class="crud_bulk_actions_main_checkbox" style="width: 16px; height: 16px;" />',
            'priority'        => 0,
            'searchLogic'     => false,
            'orderable'       => false,
            'visibleInTable'  => true,
            'visibleInModal'  => false,
            'visibleInExport' => false,
            'visibleInShow'   => false,
            'hasActions'      => true,
        ])->makeFirstColumn();

        $this->addColumn([
            'type'            => 'custom_html',
            'name'            => 'blank_first_column',
            'label'           => ' ',
            'priority'        => 0,
            'searchLogic'     => false,
            'orderable'       => false,
            'visibleInTabel'  => true,
            'visibleInModal'  => false,
            'visibleInExport' => false,
            'visibleInShow'   => false,
            'hasActions'      => true,
        ])->makeFirstColumn();
    }

    /**
     * Remove the two columns needed for bulk actions.
     */
    public function disableBulkActions()
    {
        $this->setOperationSetting('bulkActions', false);

        $this->removeColumn('bulk_actions');
        $this->removeColumn('blank_first_column');
    }

    /**
     * Set the number of rows that should be show on the list view.
     */
    public function setDefaultPageLength($value)
    {
        if ($value === 0) {
            abort(500, 'You should not use 0 as a key in paginator. If you are looking for "ALL" option, use -1 instead.');
        }

        $this->setOperationSetting('defaultPageLength', $value);
    }

    /**
     * Get the number of rows that should be show on the list view.
     *
     * @return int
     */
    public function getDefaultPageLength()
    {
        return $this->getOperationSetting('defaultPageLength') ?? config('backpack.crud.operations.list.defaultPageLength') ?? 25;
    }

    /**
     * If a custom page length was specified as default, make sure it
     * also show up in the page length menu.
     */
    public function addCustomPageLengthToPageLengthMenu()
    {
        $values = $this->getOperationSetting('pageLengthMenu')[0];
        $labels = $this->getOperationSetting('pageLengthMenu')[1];
        // this is a condition that should be always true.
        if (is_array($values) && is_array($labels)) {
            $position = array_search($this->getDefaultPageLength(), $values);
            // if position is not false we already have that value in the pagination array
            // we are just going to make it first element in array
            if ($position !== false) {
                array_unshift($values, $this->getDefaultPageLength());
                array_unshift($labels, $labels[$position]);
            } else {
                // if it's not in array we add it as the first element
                array_unshift($values, $this->getDefaultPageLength());
                array_unshift($labels, $this->getDefaultPageLength());
            }
            //now make it unique.
            $values = array_values(array_unique($values));
            $labels = array_values(array_unique($labels));
        }

        $this->setOperationSetting('pageLengthMenu', [$values, $labels]);
    }

    /**
     * Specify array of available page lengths on the list view.
     *
     * @param array|int $menu
     *
     * https://backpackforlaravel.com/docs/4.1/crud-cheat-sheet#page-length
     */
    public function setPageLengthMenu($menu)
    {
        if (is_array($menu)) {
            // start checking $menu integrity
            if (count($menu) !== count($menu, COUNT_RECURSIVE)) {
                // developer defined as setPageLengthMenu([[50, 100, 300]]) or setPageLengthMenu([[50, 100, 300],['f','h','t']])
                // we will apply the same labels as the values to the menu if developer didn't
                if (in_array(0, $menu[0])) {
                    abort(500, 'You should not use 0 as a key in paginator. If you are looking for "ALL" option, use -1 instead.');
                }

                if (! isset($menu[1]) || ! is_array($menu[1])) {
                    $menu[1] = $menu[0];
                }
            } else {
                // developer defined setPageLengthMenu([10 => 'f', 100 => 'h', 300 => 't']) OR setPageLengthMenu([50, 100, 300])
                $menu = $this->buildPageLengthMenuFromArray($menu);
            }
        } else {
            // developer added only a single value setPageLengthMenu(10)
            if ($menu === 0) {
                abort(500, 'You should not use 0 as a key in paginator. If you are looking for "ALL" option, use -1 instead.');
            }

            $menu = [[$menu], [$menu]];
        }

        $this->setOperationSetting('pageLengthMenu', $menu);
    }

    /**
     * Builds the menu from the given array. It works out with two different types of arrays:
     *  [1, 2, 3] AND [1 => 'one', 2 => 'two', 3 => 'three'].
     *
     * @param array $menu
     * @return array
     */
    private function buildPageLengthMenuFromArray($menu)
    {
        // check if the values of the array are strings, in case developer defined:
        // setPageLengthMenu([0 => 'f', 100 => 'h', 300 => 't'])
        if (count(array_filter(array_values($menu), 'is_string')) > 0) {
            $values = array_keys($menu);
            $labels = array_values($menu);

            if (in_array(0, $values)) {
                abort(500, 'You should not use 0 as a key in paginator. If you are looking for "ALL" option, use -1 instead.');
            }

            return [$values, $labels];
        } else {
            // developer defined length as setPageLengthMenu([50, 100, 300])
            // we will use the same values as labels
            if (in_array(0, $menu)) {
                abort(500, 'You should not use 0 as a key in paginator. If you are looking for "ALL" option, use -1 instead.');
            }

            return [$menu, $menu];
        }
    }

    /**
     * Get page length menu for the list view.
     *
     * @return array
     */
    public function getPageLengthMenu()
    {
        // if we have a 2D array, update all the values in the right hand array to their translated values
        if (isset($this->getOperationSetting('pageLengthMenu')[1]) && is_array($this->getOperationSetting('pageLengthMenu')[1])) {
            $aux = $this->getOperationSetting('pageLengthMenu');
            foreach ($this->getOperationSetting('pageLengthMenu')[1] as $key => $val) {
                $aux[1][$key] = trans($val);
            }
            $this->setOperationSetting('pageLengthMenu', $aux);
        }
        $this->addCustomPageLengthToPageLengthMenu();

        return $this->getOperationSetting('pageLengthMenu');
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
        $this->setOperationSetting('exportButtons', true);
    }

    /**
     * Check if export buttons are enabled for the table view.
     *
     * @return bool
     */
    public function exportButtons()
    {
        return $this->getOperationSetting('exportButtons') ?? false;
    }
}
