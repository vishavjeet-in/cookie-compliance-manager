jQuery(document).ready(function($) {
  if ($('#wccm-logs-datatable').length) {
    $('#wccm-logs-datatable').DataTable({
      dom: 'Bfrtip',
      buttons: [
        'copy', 'csv', 'excel', 'pdf', 'print'
      ],
      order: [[0, 'desc']],
      pageLength: 20,
      responsive: true
    });
  }
});
