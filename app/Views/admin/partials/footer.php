        </div> <!-- End admin-content-body -->
    </div> <!-- End admin-main -->
</div> <!-- End admin-wrapper -->

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Mobile Sidebar Toggle
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const adminSidebar = document.getElementById('adminSidebar');
    if (sidebarToggleBtn && adminSidebar) {
        sidebarToggleBtn.addEventListener('click', function() {
            adminSidebar.classList.toggle('show-mobile');
        });
    }
</script>
</body>
</html>
