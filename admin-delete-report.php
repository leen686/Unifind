<?php
session_start();
include 'db.php';

if (!isset($_SESSION['adminID']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$reportID = intval($_GET['id'] ?? 0);

if ($reportID > 0) {
    $stmt = $conn->prepare("DELETE FROM reports WHERE reportID = ?");
    $stmt->bind_param("i", $reportID);
    $stmt->execute();
}

header("Location: admin-reports.php");
exit();
?>