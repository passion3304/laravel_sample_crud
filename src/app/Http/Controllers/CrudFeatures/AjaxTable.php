<?php

namespace Backpack\CRUD\app\Http\Controllers\CrudFeatures;

trait AjaxTable
{
    /**
     * Respond with the JSON of one or more rows, depending on the POST parameters.
     * @return JSON Array of cells in HTML form.
     */
    public $data;

    private $input;
    private $totalRows = 0;
    private $filteredRows = 0;
    private $versionTransformer;

    /**
     * The search function. This function it's called by the data table
     *
     * @return array
     */
    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        $this->input = Input::all();
        $this->totalRows = $this->filteredRows = $this->crud->getEntries()->count();

        $data = $this->crud->getEntriesWithConditions(
            $this->input['length'],
            $this->input['start'],
            $this->addOrderBy()[0],
            $this->addOrderBy()[1],
            $this->addFilters()
        );

        if ($this->addFilters() !== null) {
            $this->filteredRows = $data->count();
        }

        return $this->make($data);
    }

    /**
     * Formats the row of the table from the entry(instance of a model)
     *
     * @param $entry
     * @return array
     */
    private function format($entry)
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
     * Created the array to be fed to the data table
     * @param $data
     * @return array
     */
    private function make($data)
    {
        $rows = [];
        foreach ($data as $row) {
            $rows[] = $this->format($row);
        }

        return [
            'draw'            => (isset($this->input['draw']) ? (int)$this->input['draw'] : 0),
            'recordsTotal'    => $this->totalRows,
            'recordsFiltered' => $this->filteredRows,
            'data'            => $rows,
        ];

    }

    /**
     * Checks of teh user has anything written in the search bar and if so a string with the filtered is returned.
     * In case no filter is detected null is returned
     *
     * @return null | string $filter
     */
    private function addFilters()
    {
        if (isset($this->input['search']) && isset($this->input['search']['value'])) {
            $filter = $this->input['search']['value'];
            if ($filter !== '') {
                return $filter;
            }
        }
        return null;
    }


    /**
     * Checks if the user tried to order a column and if so an array with the
     * column to be order and the direction are returned
     *
     * @return array [column, direction]
     */
    public function addOrderBy()
    {
        if (isset($this->input['order']) && isset($this->input['order'][0])) {
            $orderBy = $this->input['order'][0]['column'];
            if (isset($this->crud->columns[(int)$orderBy]['name'])) {
                return [$this->crud->columns[(int)$orderBy]['name'], $this->input['order'][0]['dir']];
            }
        }
        return [null, null];
    }
}
