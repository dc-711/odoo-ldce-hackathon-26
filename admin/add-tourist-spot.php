<?php

session_start();
include("db.php");


// =========================================
// LOGIN CHECK
// =========================================

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {

    header("Location: login.php");
    exit;
}


$user_id = (int)$_SESSION['user_id'];


// Only allow POST

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: cities.php");
    exit;
}


$activity_id =
    (int)($_POST['activity_id'] ?? 0);

$trip_id =
    (int)($_POST['trip_id'] ?? 0);

$stop_id =
    (int)($_POST['stop_id'] ?? 0);


if (
    $activity_id <= 0 ||
    $trip_id <= 0 ||
    $stop_id <= 0
) {
    die("Invalid request.");
}


// =========================================
// VERIFY USER OWNS TRIP
// =========================================

$stmt = $conn->prepare("
    SELECT id
    FROM trips
    WHERE id = ?
      AND user_id = ?
    LIMIT 1
");

$stmt->bind_param(
    "ii",
    $trip_id,
    $user_id
);

$stmt->execute();

if ($stmt->get_result()->num_rows !== 1) {

    $stmt->close();

    die("You cannot modify this trip.");
}

$stmt->close();


// =========================================
// VERIFY STOP BELONGS TO TRIP
// =========================================

$stmt = $conn->prepare("
    SELECT id
    FROM stops
    WHERE id = ?
      AND trip_id = ?
    LIMIT 1
");

$stmt->bind_param(
    "ii",
    $stop_id,
    $trip_id
);

$stmt->execute();

if ($stmt->get_result()->num_rows !== 1) {

    $stmt->close();

    die("Invalid trip stop.");
}

$stmt->close();


// =========================================
// GET ORIGINAL ACTIVITY
// =========================================

$stmt = $conn->prepare("
    SELECT
        name,
        activity_type,
        estimated_cost,
        start_time,
        notes,
        location,
        duration_minutes,
        rating,
        image

    FROM activities

    WHERE id = ?

    LIMIT 1
");

$stmt->bind_param(
    "i",
    $activity_id
);

$stmt->execute();

$activity = $stmt
    ->get_result()
    ->fetch_assoc();

$stmt->close();


if (!$activity) {
    die("Tourist spot not found.");
}


// =========================================
// OPTIONAL DUPLICATE CHECK
// =========================================

$stmt = $conn->prepare("
    SELECT id
    FROM activities
    WHERE stop_id = ?
      AND name = ?
    LIMIT 1
");

$stmt->bind_param(
    "is",
    $stop_id,
    $activity['name']
);

$stmt->execute();

$alreadyExists =
    $stmt->get_result()->num_rows > 0;

$stmt->close();


if ($alreadyExists) {

    header(
        "Location: trip-details.php?id="
        . $trip_id
        . "&already_added=1"
    );

    exit;
}


// =========================================
// INSERT ACTIVITY INTO SELECTED STOP
// =========================================

$stmt = $conn->prepare("
    INSERT INTO activities
    (
        stop_id,
        name,
        activity_type,
        estimated_cost,
        start_time,
        notes,
        location,
        duration_minutes,
        rating,
        image
    )
    VALUES
    (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )
");


$activity_name =
    $activity['name'];

$activity_type =
    $activity['activity_type'];

$estimated_cost =
    (float)$activity['estimated_cost'];

$start_time =
    $activity['start_time'];

$notes =
    $activity['notes'];

$location =
    $activity['location'];

$duration_minutes =
    (int)$activity['duration_minutes'];

$rating =
    (float)$activity['rating'];

$image =
    $activity['image'];


$stmt->bind_param(
    "issdsssids",

    $stop_id,
    $activity_name,
    $activity_type,
    $estimated_cost,
    $start_time,
    $notes,
    $location,
    $duration_minutes,
    $rating,
    $image
);


if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: trip-details.php?id="
        . $trip_id
        . "&added=1"
    );

    exit;
}


$error = $stmt->error;

$stmt->close();


die(
    "Unable to add tourist spot: "
    . htmlspecialchars($error)
);

?>