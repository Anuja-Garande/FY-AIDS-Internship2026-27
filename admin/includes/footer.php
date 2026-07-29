        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('margin-0');
        }
        function toggleStatus(table, id, field, btn) {
            fetch('<?= BASE_URL ?>/admin/ajax/toggle_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `table=${table}&id=${id}&field=${field}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({icon:'success',title:'Updated',text:data.message,timer:1500,showConfirmButton:false});
                    if (btn) {
                        btn.classList.toggle('btn-success');
                        btn.classList.toggle('btn-danger');
                        btn.textContent = data.new_value == 1 ? 'Active' : 'Inactive';
                    }
                } else {
                    Swal.fire({icon:'error',title:'Error',text:data.message});
                }
            })
            .catch(() => Swal.fire({icon:'error',title:'Error',text:'Something went wrong'}));
        }
        function deleteItem(url, id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e94560',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(url, {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'id='+id})
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({icon:'success',title:'Deleted',text:data.message,timer:1500,showConfirmButton:false}).then(() => location.reload());
                        } else {
                            Swal.fire({icon:'error',title:'Error',text:data.message});
                        }
                    })
                    .catch(() => Swal.fire({icon:'error',title:'Error',text:'Something went wrong'}));
                }
            });
        }
    </script>
        <?php $flash_success = getFlash('success'); if ($flash_success): ?>
        <script>Swal.fire({icon:'success',title:'Success',text:'<?= addslashes($flash_success) ?>',timer:3000,showConfirmButton:false});</script>
        <?php endif; ?>
        <?php $flash_error = getFlash('error'); if ($flash_error): ?>
        <script>Swal.fire({icon:'error',title:'Error',text:'<?= addslashes($flash_error) ?>',timer:3000,showConfirmButton:false});</script>
        <?php endif; ?>
    </script>
    <script src="<?= BASE_URL ?>/admin/assets/js/admin.js"></script>
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
