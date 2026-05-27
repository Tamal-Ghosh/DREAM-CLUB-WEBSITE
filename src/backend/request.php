<?php
declare(strict_types=1);

session_start();

$redirectBase = '../frontend';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$redirectBase}/request.html");
    exit;
}

$patientName = trim($_POST['patientName'] ?? '');
$bloodGroup = trim($_POST['bloodGroup'] ?? '');
$hospital = trim($_POST['hospital'] ?? '');
$location = trim($_POST['location'] ?? '');
$contactNumber = trim($_POST['contactNumber'] ?? '');
$unitsNeeded = trim($_POST['unitsNeeded'] ?? '');
$urgencyLevel = trim($_POST['urgencyLevel'] ?? '');
$details = trim($_POST['details'] ?? '');

if ($patientName === '' || $bloodGroup === '' || $hospital === '' || $contactNumber === '' || $unitsNeeded === '' || $urgencyLevel === '') {
    header("Location: {$redirectBase}/request.html?error=missing");
    exit;
}

try {
    require __DIR__ . '/db.php';
} catch (Throwable $error) {
    header("Location: {$redirectBase}/request.html?error=db");
    exit;
}

$createdByUserId = isset($_SESSION['userId']) ? (int) $_SESSION['userId'] : null;

$insertSql = 'INSERT INTO dbo.requests (patient_name, blood_group, hospital, location, contact_number, units_needed, urgency_level, details, created_by_user_id) OUTPUT INSERTED.id VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
$insertParams = [
    $patientName,
    $bloodGroup,
    $hospital,
    $location,
    $contactNumber,
    (int) $unitsNeeded,
    $urgencyLevel,
    $details,
    $createdByUserId
];

$insertStmt = sqlsrv_prepare($connection, $insertSql, $insertParams);

if (!$insertStmt || !sqlsrv_execute($insertStmt)) {
    header("Location: {$redirectBase}/request.html?error=db");
    exit;
}

$inserted = sqlsrv_fetch_array($insertStmt, SQLSRV_FETCH_ASSOC);
$requestId = (int) ($inserted['id'] ?? 0);

if ($requestId > 0) {
    $updateSql = "UPDATE dbo.requests SET request_code = CONCAT('PR-', RIGHT('0000' + CAST(id AS VARCHAR(10)), 4)) WHERE id = ?";
    $updateStmt = sqlsrv_prepare($connection, $updateSql, [$requestId]);
    if ($updateStmt) {
        sqlsrv_execute($updateStmt);
    }
}

$role = $_SESSION['role'] ?? 'public';
$target = $role === 'patient'
    ? "{$redirectBase}/patient_dashboard.html"
    : "{$redirectBase}/request.html";

header("Location: {$target}?success=1");
exit;
