<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backpack Crud Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the CRUD interface.
    | You are free to change them to anything
    | you want to customize your views to better match your application.
    |
    */

    // Forms
    'save_action_save_and_new'         => 'Guardar e adicionar item',
    'save_action_save_and_edit'        => 'Guardar e editar este item',
    'save_action_save_and_back'        => 'Guardar e voltar',
    'save_action_save_and_preview'     => 'Guardar e visualizar',
    'save_action_changed_notification' => 'O comportamento padr�o depois de gravar foi alterado.',

    // Create form
    'add'                 => 'Adicionar',
    'back_to_all'         => 'Voltar � lista de ',
    'cancel'              => 'Cancelar',
    'add_a_new'           => 'Adicionar ',

    // Edit form
    'edit'                 => 'Editar',
    'save'                 => 'Gravar',

    // Translatable models
    'edit_translations' => 'Tradu��o',
    'language'          => 'Idioma',

    // CRUD table view
    'all'                       => 'Todos ',
    'in_the_database'           => 'na base de dados',
    'list'                      => 'Lista',
    'reset'                     => 'Repor',
    'actions'                   => 'Ac��es',
    'preview'                   => 'Visualizar',
    'delete'                    => 'Apagar',
    'admin'                     => 'Administrar',
    'details_row'               => 'Isto � a linha de detalhes. Modifique conforme quiser.',
    'details_row_loading_error' => 'Houve um erro ao carregar os detalhes. Por favor tente novamente.',
    'clone'                     => 'Clonar',
    'clone_success'             => '<strong>Item clonado</strong><br>Um novo item foi adicionado com as mesmas informa��es que este.',
    'clone_failure'             => '<strong>A clonagem falhou</strong><br>O novo item n�o pode ser criado. Por favor tente novamente.',

    // Confirmation messages and bubbles
    'delete_confirm'                              => 'Tem a certeza que quer apagar este item?',
    'delete_confirmation_title'                   => 'Item apagado',
    'delete_confirmation_message'                 => 'O item foi apagado com sucesso.',
    'delete_confirmation_not_title'               => 'N�o apagado',
    'delete_confirmation_not_message'             => 'Ocorreu um erro. O item pode n�o ter sido apagado.',
    'delete_confirmation_not_deleted_title'       => 'N�o apagado',
    'delete_confirmation_not_deleted_message'     => 'O item n�o foi apagado.',

    // Bulk actions
    'bulk_no_entries_selected_title'   => 'Nenhum item seleccionado',
    'bulk_no_entries_selected_message' => 'Por favor seleccione um ou mais itens para realizar uma ac��o em massa.',

    // Bulk delete
    'bulk_delete_are_you_sure'   => 'Tem a certeza que quer apagar estes :number itens?',
    'bulk_delete_sucess_title'   => 'Itens apagados',
    'bulk_delete_sucess_message' => ' itens foram apagados',
    'bulk_delete_error_title'    => 'Ocorreu um erro ao apagar o item',
    'bulk_delete_error_message'  => 'Um ou mais itens n�o puderam ser apagados',

    // Bulk clone
    'bulk_clone_are_you_sure'   => 'Tem a certeza que quer clonar estes :number itens?',
    'bulk_clone_sucess_title'   => 'Itens clonados',
    'bulk_clone_sucess_message' => ' itens foram clonados.',
    'bulk_clone_error_title'    => 'Ocorreu um erro ao clonar o item',
    'bulk_clone_error_message'  => 'Um ou mais itens n�o puderam ser criados. Por favor tente novamente.',

    // Ajax errors
    'ajax_error_title' => 'Erro',
    'ajax_error_text'  => 'Erro ao carregar a p�gina. Por favor actualize a p�gina.',

    // DataTables translation
    'emptyTable'     => 'Sem dados dispon�veis na tabela',
    'info'           => 'A mostrar _START_ a _END_ de _TOTAL_ itens',
    'infoEmpty'      => 'Sem itens',
    'infoFiltered'   => '(filtrado de um total de _MAX_ itens)',
    'infoPostFix'    => '.',
    'thousands'      => ',',
    'lengthMenu'     => '_MENU_ itens por p�gina',
    'loadingRecords' => 'A carregar...',
    'processing'     => 'A processar...',
    'search'         => 'Procurar',
    'zeroRecords'    => 'Nenhum item encontrado',
    'paginate'       => [
        'first'    => 'Primeiro',
        'last'     => '�ltimo',
        'next'     => 'Seguinte',
        'previous' => 'Anterior',
    ],
    'aria' => [
        'sortAscending'  => ': activar para colocar por ordem ascendente',
        'sortDescending' => ': activar para colocar por ordem descendente',
    ],
    'export' => [
        'export'            => 'Exportar',
        'copy'              => 'Copiar',
        'excel'             => 'Excel',
        'csv'               => 'CSV',
        'pdf'               => 'PDF',
        'print'             => 'Imprimir',
        'column_visibility' => 'Colunas vis�veis',
    ],

    // global crud - errors
    'unauthorized_access' => 'Acesso n�o autorizado - n�o tem as permiss�es necess�rias para ver esta p�gina.',
    'please_fix'          => 'Por favor corrija os seguintes erros:',

    // global crud - success / error notification bubbles
    'insert_success' => 'O item foi adicionado com sucesso.',
    'update_success' => 'O item foi modificado com sucesso.',

    // CRUD reorder view
    'reorder'                      => 'Reordenar',
    'reorder_text'                 => 'Use \'arrastar e soltar\' para ordenar.',
    'reorder_success_title'        => 'Feito',
    'reorder_success_message'      => 'A ordena��o foi gravada.',
    'reorder_error_title'          => 'Erro',
    'reorder_error_message'        => 'A ordena��o n�o foi gravada.',

    // CRUD yes/no
    'yes' => 'Sim',
    'no'  => 'N�o',

    // CRUD filters navbar view
    'filters'        => 'Filtros',
    'toggle_filters' => 'Alternar filtros',
    'remove_filters' => 'Remover filtros',
    'apply' => 'Aplicar',

    //filters language strings
    'today' => 'Hoje',
    'yesterday' => 'Ontem',
    'last_7_days' => '�ltimos 7 dias',
    'last_30_days' => '�ltimos 30 dias',
    'this_month' => 'Este m�s',
    'last_month' => 'M�s passado',
    'custom_range' => 'Intervalo personalizado',
    'weekLabel' => 'S',

    // Fields
    'browse_uploads'            => 'Procurar uploads',
    'select_all'                => 'Seleccionar todos',
    'select_files'              => 'Seleccionar ficheiros',
    'select_file'               => 'Seleccionar ficheiro',
    'clear'                     => 'Limpar',
    'page_link'                 => 'Link da p�gina',
    'page_link_placeholder'     => 'http://example.com/a-sua-pagina',
    'internal_link'             => 'Link interno',
    'internal_link_placeholder' => 'Slug interno. Ex: \'admin/page\' (sem aspas) para \':url\'',
    'external_link'             => 'Link externo',
    'choose_file'               => 'Escolher ficheiro',
    'new_item'                  => 'Novo item',
    'select_entry'              => 'Selecionar um item',
    'select_entries'            => 'Selecionar itens',

    //Table field
    'table_cant_add'    => 'N�o foi poss�vel adicionar novo :entity',
    'table_max_reached' => 'Limite de :max itens atingido',

    // File manager
    'file_manager' => 'Gestor de ficheiros',

    // InlineCreateOperation
    'related_entry_created_success' => 'O item relacionado foi criado e selecionado.',
    'related_entry_created_error' => 'N�o foi poss�vel adicionar o item relacionado.',
];
