<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function json_response($payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function require_role(array $roles): void
{
    if (!isLoggedIn() || !in_array($_SESSION['role'] ?? '', $roles, true)) {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
}

function fetch_requests_for_user(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare(
        'SELECT id, user_id, patient_name, contact_number, blood_group, units_required, hospital, location, urgency, details, status, donor_id, donor_name, donor_phone, created_at, updated_at
         FROM requests
         WHERE user_id = ?
         ORDER BY created_at DESC, id DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function fetch_assigned_requests(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare(
        'SELECT id, user_id, patient_name, contact_number, blood_group, units_required, hospital, location, urgency, details, status, donor_id, donor_name, donor_phone, created_at, updated_at
         FROM requests
         WHERE donor_id = ?
         ORDER BY created_at DESC, id DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function fetch_management_requests(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT id, user_id, patient_name, contact_number, blood_group, units_required, hospital, location, urgency, details, status, donor_id, donor_name, donor_phone, created_at, updated_at
         FROM requests
         ORDER BY created_at DESC, id DESC'
    );
    return $stmt->fetchAll();
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$action = $action ?: ($method === 'POST' ? 'create' : '');
$pdo = getPDO();

if ($method === 'GET') {
    if ($action === 'patient-history') {
        require_role(['patient', 'volunteer']);
        json_response(['success' => true, 'requests' => fetch_requests_for_user($pdo, (int)$_SESSION['user_id'])]);
    }

    if ($action === 'assigned-requests') {
        require_role(['donor', 'volunteer']);
        json_response(['success' => true, 'requests' => fetch_assigned_requests($pdo, (int)$_SESSION['user_id'])]);
    }

    if ($action === 'management-requests') {
        require_role(['admin', 'volunteer']);
        json_response(['success' => true, 'requests' => fetch_management_requests($pdo)]);
    }

    json_response(['success' => false, 'message' => 'Unknown action'], 400);
}

if ($method !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

if ($action === 'create') {
    require_role(['patient', 'volunteer']);

    $patientName = trim($_POST['patientName'] ?? '');
    $contactNumber = trim($_POST['contactNumber'] ?? '');
    $bloodGroup = trim($_POST['bloodGroup'] ?? '');
    $unitsRequired = (int)($_POST['unitsNeeded'] ?? 1);
    $hospital = trim($_POST['hospital'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $urgency = trim($_POST['urgencyLevel'] ?? 'Normal');
    $details = trim($_POST['details'] ?? '');

    if ($patientName === '' || $contactNumber === '' || $bloodGroup === '' || $hospital === '') {
        json_response(['success' => false, 'message' => 'Missing required fields'], 422);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO requests (user_id, patient_name, contact_number, blood_group, units_required, hospital, location, urgency, details, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        (int)$_SESSION['user_id'],
        $patientName,
        $contactNumber,
        $bloodGroup,
        max(1, $unitsRequired),
        $hospital,
        $location,
        in_array($urgency, ['Normal', 'Urgent', 'Critical'], true) ? $urgency : 'Normal',
        $details,
        'Pending'
    ]);

    json_response(['success' => true, 'message' => 'Request created']);
}

if ($action === 'assign') {
    require_role(['admin', 'volunteer']);

    $requestId = (int)($_POST['request_id'] ?? 0);
    $donorId = (int)($_POST['donor_id'] ?? 0);
    $donorName = trim($_POST['donor_name'] ?? '');
    $donorPhone = trim($_POST['donor_phone'] ?? '');

    if ($requestId <= 0 || $donorId <= 0 || $donorName === '') {
        json_response(['success' => false, 'message' => 'Missing assignment data'], 422);
    }

    $stmt = $pdo->prepare('SELECT id, status FROM requests WHERE id = ? LIMIT 1');
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    if (!$request) {
        json_response(['success' => false, 'message' => 'Request not found'], 404);
    }

    $stmt = $pdo->prepare('UPDATE requests SET donor_id = ?, donor_name = ?, donor_phone = ?, status = ? WHERE id = ?');
    $stmt->execute([
        $donorId,
        $donorName,
        $donorPhone,
        'Donor Review',
        $requestId
    ]);

    json_response(['success' => true, 'message' => 'Request assigned']);
}

if ($action === 'accept') {
    require_role(['donor', 'volunteer']);

    $requestId = (int)($_POST['request_id'] ?? 0);
    if ($requestId <= 0) {
        json_response(['success' => false, 'message' => 'Missing request id'], 422);
    }

    $stmt = $pdo->prepare('SELECT id, donor_id, status FROM requests WHERE id = ? LIMIT 1');
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();

    if (!$request) {
        json_response(['success' => false, 'message' => 'Request not found'], 404);
    }

    $currentUserId = (int)$_SESSION['user_id'];
    $currentRole = $_SESSION['role'] ?? '';
    $isAssignedToCurrentUser = (int)$request['donor_id'] === $currentUserId;

    if ($currentRole !== 'donor' || !$isAssignedToCurrentUser) {
        json_response(['success' => false, 'message' => 'This request is not assigned to you'], 403);
    }

    $stmt = $pdo->prepare('UPDATE requests SET status = ? WHERE id = ?');
    $stmt->execute(['Donor Assigned', $requestId]);

    json_response(['success' => true, 'message' => 'Request accepted']);
}

if ($action === 'complete') {
    require_role(['donor', 'volunteer']);

    $requestId = (int)($_POST['request_id'] ?? 0);
    if ($requestId <= 0) {
        json_response(['success' => false, 'message' => 'Missing request id'], 422);
    }

    $stmt = $pdo->prepare('SELECT id, status FROM requests WHERE id = ? LIMIT 1');
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    if (!$request || $request['status'] !== 'Donor Assigned') {
        json_response(['success' => false, 'message' => 'Request must be donor assigned first'], 409);
    }

    $stmt = $pdo->prepare('UPDATE requests SET status = ? WHERE id = ?');
    $stmt->execute(['Completed', $requestId]);

    json_response(['success' => true, 'message' => 'Request completed']);
}

if ($action === 'failed') {
    require_role(['donor', 'volunteer']);

    $requestId = (int)($_POST['request_id'] ?? 0);
    if ($requestId <= 0) {
        json_response(['success' => false, 'message' => 'Missing request id'], 422);
    }

    $stmt = $pdo->prepare('SELECT id, status FROM requests WHERE id = ? LIMIT 1');
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    if (!$request || $request['status'] !== 'Donor Assigned') {
        json_response(['success' => false, 'message' => 'Request must be donor assigned first'], 409);
    }

    $stmt = $pdo->prepare('UPDATE requests SET status = ? WHERE id = ?');
    $stmt->execute(['Failed', $requestId]);

    json_response(['success' => true, 'message' => 'Request marked failed']);
}

if ($action === 'reject') {
    require_role(['donor']);

    $requestId = (int)($_POST['request_id'] ?? 0);
    if ($requestId <= 0) {
        json_response(['success' => false, 'message' => 'Missing request id'], 422);
    }

    $stmt = $pdo->prepare('SELECT id, donor_id, status FROM requests WHERE id = ? LIMIT 1');
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    if (!$request || (int)$request['donor_id'] !== (int)$_SESSION['user_id']) {
        json_response(['success' => false, 'message' => 'This request is not assigned to you'], 403);
    }

    $stmt = $pdo->prepare('UPDATE requests SET donor_id = NULL, donor_name = NULL, donor_phone = NULL, status = ? WHERE id = ?');
    $stmt->execute(['Pending', $requestId]);

    json_response(['success' => true, 'message' => 'Request rejected']);
}

json_response(['success' => false, 'message' => 'Unknown action'], 400);
