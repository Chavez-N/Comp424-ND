<?php
// Ensure no output is sent before headers
ob_start();

// Verify file parameter exists
if (!isset($_GET['file'])) {
    header('HTTP/1.1 400 Bad Request');
    die('File parameter missing');
}

// Sanitize the filename
$filename = basename($_GET['file']);
$filepath = __DIR__ . '/' . $filename;

// Verify file exists and is readable
if (!file_exists($filepath) || !is_readable($filepath)) {
    header('HTTP/1.1 404 Not Found');
    die('File not found or not accessible');
}

// Clear any existing output
ob_end_clean();

// Set appropriate headers for file download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));

// Send file
readfile($filepath);
exit;
?>
