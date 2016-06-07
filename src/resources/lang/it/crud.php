<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dick Crud Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the CRUD interface.
    | You are free to change them to anything
    | you want to customize your views to better match your application.
    |
    */

    // Create form
    'add'                 => 'Aggiungi',
    'back_to_all'         => 'Torna a tutti i ',
    'cancel'              => 'Annulla',
    'add_a_new'           => 'Aggiungi un nuovo ',

        // Create form - advanced options
        'after_saving'            => 'Dopo il salvataggio',
        'go_to_the_table_view'    => 'vai alla lista di tutti gli elementi',
        'let_me_add_another_item' => 'aggiungi un altro elemento',
        'edit_the_new_item'       => 'modifica il nuovo elemento',

    // Edit form
    'edit'                 => 'Modifica',
    'save'                 => 'Salva',

    // CRUD table view
    'all'                  => 'Tutti i ',
    'in_the_database'      => 'nel database',
    'list'                 => 'Lista',
    'actions'              => 'Azioni',
    'preview'              => 'Anteprima',
    'delete'               => 'Elimina',

        // Confirmation messages and bubbles
        'delete_confirm'                              => 'Sei sicuro di eliminare questo elemento?',
        'delete_confirmation_title'                   => 'Elemento eliminato',
        'delete_confirmation_message'                 => "L'elemento è stato eliminato con successo.",
        'delete_confirmation_not_title'               => 'NON eliminato',
        'delete_confirmation_not_message'             => "C'è stato un errore. L'elemento potrebbe non essere stato eliminato.",
        'delete_confirmation_not_deleted_title'       => 'Non eliminato',
        'delete_confirmation_not_deleted_message'     => "Non è successo niente. L'elemento è al sicuro.",

        // DataTables translation
        'emptyTable'     => 'Nessun record da visualizzare',
        'info'           => 'Visualizzando da _START_ a _END_ record di _TOTAL_',
        'infoEmpty'      => 'Visualizzando da 0 a 0 record di 0',
        'infoFiltered'   => '(filtrati da _MAX_ record totali)',
        'infoPostFix'    => '',
        'thousands'      => ',',
        'lengthMenu'     => '_MENU_ record per pagina',
        'loadingRecords' => 'Caricamento...',
        'processing'     => 'Elaborazione...',
        'search'         => 'Cerca: ',
        'zeroRecords'    => 'Nessun record corrispondente',
        'paginate'       => [
            'first'    => 'Primo',
            'last'     => 'Ultimo',
            'next'     => 'Prossimo',
            'previous' => 'Precedente',
        ],
        'aria' => [
            'sortAscending'  => ': attiva per ordinare la colonna ascendentemente',
            'sortDescending' => ': attiva per ordinare la colonna discendentemente',
        ],

    // global crud - errors
    'unauthorized_access' => 'Accesso non autorizzato - non hai i permessi necessari per vedere questa pagina.',

    // global crud - success / error notification bubbles
    'insert_success' => "L'elemento è stato aggiunto correttamente.",
    'update_success' => "L'elemento è stato aggiornato correttamente.",

    // CRUD reorder view
    'reorder'                      => 'Riordina',
    'reorder_text'                 => 'Seleziona e trascina per riordinare.',
    'reorder_success_title'        => 'Fatto',
    'reorder_success_message'      => 'Il tuo ordinamento è stato salvato.',
    'reorder_error_title'          => 'Errore',
    'reorder_error_message'        => 'Il tuo ordinamento non è stato salvato.',

    'rules_text'    => "<strong>Avviso: </strong> Non tradurre le parole che iniziano con due punti (es: ':number_of_items'). Queste verranno sostituite automaticamente con un valore adeguato. Se tradotte, smetteranno di funzionare.",

];
