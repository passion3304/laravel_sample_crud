<?php

namespace Backpack\CRUD\app\Http\Controllers\CrudFeatures;

trait AjaxTable
{
    /**
     * Respond with the JSON of one or more rows, depending on the POST parameters.
     * @return JSON Array of cells in HTML form.
     */
    private $totalRows = 0;
    private $filteredRows = 0;

    /**
     * The search function that is called by the data table.
     *
     * @return array
     */
    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        $this->totalRows = $this->filteredRows = $this->crud->getEntries()->count();

        // overwrite any order set in the setup() method with the datatables order
        if ($this->request->has('order')) {
            $column_number      = $this->request->input('order')[0]['column'];
            $column_direction   = $this->request->input('order')[0]['dir'];
            $column             = $this->crud->findColumnById($column_number);

            $this->crud->orderBy($column['name'], $column_direction);
        }

        // limit the number of results to the one specified by the datatable
        if ($this->request->has('length')) {
            $this->crud->limit($this->request->input('length'));
        }

        $entries = $this->crud->getEntriesWithConditions(
            $this->request->input('start'),
            $this->request->has('search')?$this->request->input('search')['value']:null
        );

        // if a search term was present, recalculate the number of filtered rows
        if ($this->request->has('search')) {
            $this->filteredRows = $entries->count();
        }

        return $this->prepareDataForDatatables($entries);
    }

    /**
     * Formats the row of the table from the entry.
     *
     * @param $entry The instance of an Eloquent model.
     * @return array
     */
    private function formatRow($entry)
    {
        $row_items = $this->crud->getRowViews($entry, $this->crud);

        // add the buttons as the last column
        if ($this->crud->buttons->where('stack', 'line')->count()) {
            $row_items[] = \View::make('crud::inc.button_stack', ['stack' => 'line'])
                                ->with('crud', $this->crud)
                                ->with('entry', $entry)
                                ->render();
        }

        // add the details_row buttons as the first column
        if ($this->crud->details_row) {
            array_unshift($row_items, \View::make('crud::columns.details_row_button')
                                           ->with('crud', $this->crud)
                                           ->with('entry', $entry)
                                           ->render());
        }

        return $row_items;
    }

    /**
     * Created the array to be fed to the data table.
     *
     * @param $entries Eloquent results.
     * @return array
     */
    private function prepareDataForDatatables($entries)
    {
        $rows = [];

        foreach ($entries as $row) {
            $rows[] = $this->formatRow($row);
        }

        return [
            'draw'            => (isset($this->request['draw']) ? (int) $this->request['draw'] : 0),
            'recordsTotal'    => $this->totalRows,
            'recordsFiltered' => $this->filteredRows,
            'data'            => $rows,
        ];
    }
}
