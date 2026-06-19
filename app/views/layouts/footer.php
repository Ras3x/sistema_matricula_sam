    </div> <!-- Cierra .wrapper -->

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap 5 Bundle con Popper JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS CDN -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Script Personalizado del Sistema SAM -->
    <script src="assets/js/main.js"></script>
    
    <!-- Inicialización en caliente para DataTables (si existe la clase .datatable) -->
    <script>
        $(document).ready(function() {
            if ($('.datatable').length > 0) {
                $('.datatable').DataTable({
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                    },
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50]
                });
            }
        });
    </script>
</body>
</html>
