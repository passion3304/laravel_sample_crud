<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backpack\CRUD preferences
    |--------------------------------------------------------------------------
    */
    'default_save_action' => 'save_and_back', //save_and_back, save_and_edit, save_and_new

    /*
    |------------
    | CREATE
    |------------
    */

    /*
    |------------
    | READ
    |------------
    */

    // LIST VIEW (table view)

        // How many items should be shown by default by the Datatable?
        // This value can be overwritten on a specific CRUD by calling
        // $this->crud->setDefaultPageLength(50);
        'default_page_length' => 25,

    // PREVIEW

    /*
    |------------
    | UPDATE
    |------------
    */

    /*
    |------------
    | DELETE
    |------------
    */

    /*
    |------------
    | REORDER
    |------------
    */

    /*
    |------------
    | DETAILS ROW
    |------------
    */

];
