<?php
/**
 * Global Header Partial
 * Barangay Health Outreach Management System
 */

if (!isset($pageTitle)) {
    $pageTitle = 'Barangay Health Outreach System';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="assets/js/script.js" defer></script>
</head>
<body>
