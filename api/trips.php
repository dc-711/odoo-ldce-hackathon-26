<?php
declare(strict_types=1);

header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $statement = $pdo->prepare(
        'SELECT id, name, start_date, end_date, destination, description, created_at
         FROM trips WHERE user_id = :user_id ORDER BY start_date DESC'
    );
    $statement->execute(['user_id' => 1]);
    echo json_encode($statement->fetchAll());
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$name = trim((string)($input['name'] ?? ''));
$startDate = (string)($input['start_date'] ?? '');
$endDate = (string)($input['end_date'] ?? '');
$destination = trim((string)($input['destination'] ?? ''));

if ($name === '' || $startDate === '' || $endDate === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Trip name and dates are required']);
    exit;
}

$statement = $pdo->prepare(
    'INSERT INTO trips (user_id, name, start_date, end_date, destination)
     VALUES (:user_id, :name, :start_date, :end_date, :destination)'
);
$statement->execute([
    'user_id' => 1,
    'name' => $name,
    'start_date' => $startDate,
    'end_date' => $endDate,
    'destination' => $destination ?: null
]);

http_response_code(201);
echo json_encode(['id' => (int)$pdo->lastInsertId(), 'message' => 'Trip created']);
