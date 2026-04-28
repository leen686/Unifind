<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$reportID = intval($_GET['id'] ?? 0);

if ($reportID <= 0) {
    header("Location: my-reports.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM reports WHERE reportID = ? AND userID = ?");
$stmt->bind_param("ii", $reportID, $userID);
$stmt->execute();

header("Location: my-reports.php");
exit();
?>