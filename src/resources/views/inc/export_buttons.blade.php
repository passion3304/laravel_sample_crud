@if ($crud->exportButtons())
  <script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js" type="text/javascript"></script>
  <script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js" type="text/javascript"></script>
  <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.bootstrap.min.js" type="text/javascript"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js" type="text/javascript"></script>
  <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js" type="text/javascript"></script>
  <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js" type="text/javascript"></script>
  <script src="//cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js" type="text/javascript"></script>
  <script src="//cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js" type="text/javascript"></script>
  <script src="//cdn.datatables.net/buttons/1.2.2/js/buttons.colVis.min.js" type="text/javascript"></script>
  <script>
    crud.dataTableConfiguration.buttons = crud.createDataTablesButtonArray([
      'copyHtml5',
      'excelHtml5',
      'csvHtml5',
      'pdfHtml5',
      'print',
      'colvis'
    ]);

    // move the datatable buttons in the top-right corner and make them smaller
    function moveExportButtonsToTopRight() {
      crud.table.buttons().each(function(button) {
        if (button.node.className.indexOf('buttons-columnVisibility') == -1)
        {
          button.node.className = button.node.className + " btn-sm";
        }
      })
      $(".dt-buttons").appendTo($('#datatable_button_stack' )).css('display', 'block');
    }

    crud.addFunctionToDataTablesDrawEventQueue('moveExportButtonsToTopRight');
  </script>
@endif