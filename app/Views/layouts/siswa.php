<?php
/**
 * Siswa Layout - Using Existing Templates
 * This layout includes header and footer from members/siswa/templates
 */

// Include header
echo view('members/siswa/templates/header', get_defined_vars());
?>

<!-- Render Styles Section -->
<?= $this->renderSection('styles') ?>

<!-- Main Content -->
<?= $this->renderSection('content') ?>

<?php
// Include footer
echo view('members/siswa/templates/footer', get_defined_vars());
?>

<!-- Render Scripts Section -->
<?= $this->renderSection('scripts') ?>