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

function fetch_matched_donors(PDO $pdo, string $bloodGroup): array
{
    $stmt = $pdo->prepare(
        'SELECT id, name, phone, blood_group
         FROM users
         WHERE role = ? AND status = ? AND availability_status = ? AND blood_group = ?
         ORDER BY created_at DESC, id DESC'
    );
    $stmt->execute(['donor', 'Active', 'Available', $bloodGroup]);
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

    if ($action === 'matched-donors') {
        require_role(['admin', 'volunteer']);

        $requestId = (int)($_GET['request_id'] ?? 0);
        if ($requestId <= 0) {
            json_response(['success' => false, 'message' => 'Missing request id'], 422);
        }

        $stmt = $pdo->prepare('SELECT id, blood_group, patient_name, contact_number, hospital, location FROM requests WHERE id = ? LIMIT 1');
        $stmt->execute([$requestId]);
        $request = $stmt->fetch();
        if (!$request) {
            json_response(['success' => false, 'message' => 'Request not found'], 404);
        }

        json_response([
            'success' => true,
            'request' => $request,
            'donors' => fetch_matched_donors($pdo, (string)$request['blood_group'])
        ]);
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

if ($action === 'update') {
    require_role(['patient', 'volunteer']);

    $requestId = (int)($_POST['request_id'] ?? 0);
    $patientName = trim($_POST['patientName'] ?? '');
    $contactNumber = trim($_POST['contactNumber'] ?? '');
    $bloodGroup = trim($_POST['bloodGroup'] ?? '');
    $unitsRequired = (int)($_POST['unitsNeeded'] ?? 1);
    $hospital = trim($_POST['hospital'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $urgency = trim($_POST['urgencyLevel'] ?? 'Normal');
    $details = trim($_POST['details'] ?? '');

    if ($requestId <= 0) {
        json_response(['success' => false, 'message' => 'Missing request id'], 422);
    }

    if ($patientName === '' || $contactNumber === '' || $bloodGroup === '' || $hospital === '') {
        json_response(['success' => false, 'message' => 'Missing required fields'], 422);
    }

    $stmt = $pdo->prepare('SELECT id, user_id, status FROM requests WHERE id = ? LIMIT 1');
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();

    if (!$request) {
        json_response(['success' => false, 'message' => 'Request not found'], 404);
    }

    if ((int)$request['user_id'] !== (int)$_SESSION['user_id']) {
        json_response(['success' => false, 'message' => 'You can only edit your own request'], 403);
    }

    if (($request['status'] ?? 'Pending') !== 'Pending') {
        json_response(['success' => false, 'message' => 'Only pending requests can be edited'], 409);
    }

    $stmt = $pdo->prepare(
        'UPDATE requests
         SET patient_name = ?, contact_number = ?, blood_group = ?, units_required = ?, hospital = ?, location = ?, urgency = ?, details = ?
         WHERE id = ?'
    );
    $stmt->execute([
        $patientName,
        $contactNumber,
        $bloodGroup,
        max(1, $unitsRequired),
        $hospital,
        $location,
        in_array($urgency, ['Normal', 'Urgent', 'Critical'], true) ? $urgency : 'Normal',
        $details,
        $requestId
    ]);

    json_response(['success' => true, 'message' => 'Request updated']);
}

if ($action === 'assign') {
    require_role(['admin', 'volunteer']);

    $requestId = (int)($_POST['request_id'] ?? 0);
    $donorId = (int)($_POST['donor_id'] ?? 0);

    // Debug logging: record assign attempts (temporary)
    $dbgFile = __DIR__ . '/assign_debug.log';
    $dbgEntry = sprintf("[%s] assign attempt: request_id=%d donor_id=%d user_id=%s\n", date('c'), $requestId, $donorId, $_SESSION['user_id'] ?? 'anon');
    @file_put_contents($dbgFile, $dbgEntry, FILE_APPEND);

    if ($requestId <= 0 || $donorId <= 0) {
        @file_put_contents($dbgFile, "missing assignment data\n", FILE_APPEND);
        json_response(['success' => false, 'message' => 'Missing assignment data'], 422);
    }

    $stmt = $pdo->prepare('SELECT id, status FROM requests WHERE id = ? LIMIT 1');
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    if (!$request) {
        @file_put_contents($dbgFile, "request not found\n", FILE_APPEND);
        json_response(['success' => false, 'message' => 'Request not found'], 404);
    }

    $stmt = $pdo->prepare(
        'SELECT id, name, phone, blood_group, role, status, availability_status
         FROM users
         WHERE id = ? LIMIT 1'
    );
    $stmt->execute([$donorId]);
    $donor = $stmt->fetch();
    if (!$donor || $donor['role'] !== 'donor') {
        @file_put_contents($dbgFile, "donor not found or wrong role\n", FILE_APPEND);
        json_response(['success' => false, 'message' => 'Donor not found'], 404);
    }

    if ($donor['status'] !== 'Active' || $donor['availability_status'] !== 'Available') {
        @file_put_contents($dbgFile, "donor not available: status={$donor['status']} avail={$donor['availability_status']}\n", FILE_APPEND);
        json_response(['success' => false, 'message' => 'Donor is not available'], 409);
    }

    // If the request has a blood_group set, enforce a match. If request blood_group is empty, allow assignment.
    if (!empty($request['blood_group']) && (string)$donor['blood_group'] !== (string)$request['blood_group']) {
        @file_put_contents($dbgFile, "blood group mismatch: donor={$donor['blood_group']} request={$request['blood_group']}\n", FILE_APPEND);
        json_response(['success' => false, 'message' => 'Donor blood group does not match the request'], 409);
    }

    $stmt = $pdo->prepare('UPDATE requests SET donor_id = ?, donor_name = ?, donor_phone = ?, status = ? WHERE id = ?');
    $stmt->execute([
        $donorId,
        $donor['name'],
        $donor['phone'] ?? '',
        'Donor Review',
        $requestId
    ]);

    @file_put_contents($dbgFile, "assigned ok: donor={$donorId} to request={$requestId}\n", FILE_APPEND);

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
    require_role(['admin', 'volunteer']);

    $requestId = (int)($_POST['request_id'] ?? 0);
    if ($requestId <= 0) {
        json_response(['success' => false, 'message' => 'Missing request id'], 422);
    }

    $stmt = $pdo->prepare('SELECT id, status FROM requests WHERE id = ? LIMIT 1');
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    if (!$request) {
        json_response(['success' => false, 'message' => 'Request not found'], 404);
    }

    if (($request['status'] ?? 'Pending') !== 'Donor Assigned') {
        json_response(['success' => false, 'message' => 'Request must be donor assigned first'], 409);
    }

    $stmt = $pdo->prepare('UPDATE requests SET status = ? WHERE id = ?');
    $stmt->execute(['Completed', $requestId]);

    json_response(['success' => true, 'message' => 'Request completed']);
}

if ($action === 'failed') {
    require_role(['admin', 'volunteer']);

    $requestId = (int)($_POST['request_id'] ?? 0);
    if ($requestId <= 0) {
        json_response(['success' => false, 'message' => 'Missing request id'], 422);
    }

    $stmt = $pdo->prepare('SELECT id, status FROM requests WHERE id = ? LIMIT 1');
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    if (!$request) {
        json_response(['success' => false, 'message' => 'Request not found'], 404);
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
