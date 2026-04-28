<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$reportID = intval($_GET['id'] ?? 0);
$action = $_GET['action'] ?? 'save';
$return = $_GET['return'] ?? 'reports';

if ($reportID <= 0) {
    header("Location: reports.php");
    exit();
}

if ($action === 'unsave') {
    $stmt = $conn->prepare("DELETE FROM saved_reports WHERE userID = ? AND reportID = ?");
    $stmt->bind_param("ii", $userID, $reportID);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("INSERT IGNORE INTO saved_reports (userID, reportID) VALUES (?, ?)");
    $stmt->bind_param("ii", $userID, $reportID);
    $stmt->execute();
}

if ($return === 'saved') {
    header("Location: saved.php");
} elseif ($return === 'details') {
    header("Location: report-details.php?id=" . $reportID);
} else {
    header("Location: reports.php");
}
exit();
?>