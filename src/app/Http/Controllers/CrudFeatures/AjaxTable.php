<?php

namespace Backpack\CRUD\app\Http\Controllers\CrudFeatures;

trait AjaxTable
{
    /**
     * Respond with the JSON of one or more rows, depending on the POST parameters.
     * @return JSON Array of cells in HTML form.
     */
    private $input;
    private $totalRows = 0;
    private $filteredRows = 0;
    private $versionTransformer;

    /**
     * The search function that is called by the data table.
     *
     * @return array
     */
    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        $this->input = $_REQUEST;
        $this->totalRows = $this->filteredRows = $this->crud->getEntries()->count();

        $entries = $this->crud->getEntriesWithConditions(
            $this->input['length'],
            $this->input['start'],
            $this->addAjaxOrderBy()[0],
            $this->addAjaxOrderBy()[1],
            $this->input['search']['value']?$this->input['search']['value']:null
        );

        if ($this->input['search']['value']) {
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
            'draw'            => (isset($this->input['draw']) ? (int) $this->input['draw'] : 0),
            'recordsTotal'    => $this->totalRows,
            'recordsFiltered' => $this->filteredRows,
            'data'            => $rows,
        ];
    }

    /**
     * Checks if the user tried to order a column. If so, an array with the
     * column to be order and the direction are returned.
     *
     * @return array [column, direction]
     */
    public function addAjaxOrderBy()
    {
        if (isset($this->input['order']) && isset($this->input['order'][0])) {
            $orderBy = $this->input['order'][0]['column'];
            if (isset($this->crud->columns[(int) $orderBy]['name'])) {
                return [$this->crud->columns[(int) $orderBy]['name'], $this->input['order'][0]['dir']];
            }
        }

        return [null, null];
    }
}
