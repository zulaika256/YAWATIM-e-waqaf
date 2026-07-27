<?php
// includes/footer.php - Unified HTML Footer
?>
            </main> <!-- Closing content-body -->
        </div> <!-- Closing main-wrapper -->
    </div> <!-- Closing app-container -->

    <!-- Load PDF export libraries and custom JS Application -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="assets/js/app.js"></script>
<?php
    require_once __DIR__ . '/notification.php';
    $flash = get_flash();
    if ($flash):
?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast(<?php echo json_encode($flash['message']); ?>, <?php echo json_encode($flash['type']); ?>);
        });
    </script>
<?php endif; ?>
</body>
</html>
