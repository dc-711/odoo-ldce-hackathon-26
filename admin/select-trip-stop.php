<?php

session_start();
include("db.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$trip_id = (int)($_GET['trip_id'] ?? 0);
$activity_id = (int)($_GET['activity_id'] ?? 0);


if ($trip_id <= 0 || $activity_id <= 0) {
    header("Location: cities.php");
    exit;
}


// =========================================
// VERIFY TRIP
// =========================================

$stmt = $conn->prepare("
    SELECT
        id,
        name
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

$trip = $stmt
    ->get_result()
    ->fetch_assoc();

$stmt->close();


if (!$trip) {
    die("Trip not found or access denied.");
}


// =========================================
// VERIFY ACTIVITY
// =========================================

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        location
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
// GET TRIP STOPS
// =========================================

$stops = [];

$stmt = $conn->prepare("
    SELECT
        id,
        city,
        country,
        arrival_date,
        departure_date,
        position

    FROM stops

    WHERE trip_id = ?

    ORDER BY
        position ASC,
        id ASC
");

$stmt->bind_param(
    "i",
    $trip_id
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $stops[] = $row;
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>
    Select Trip Stop | GlobeTrotter
</title>

<link
    rel="stylesheet"
    href="assets/css/home.css"
>

<style>

body {
    margin: 0;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    background: #f5f5f5;
}

.container {
    width: min(750px, 92%);
    margin: 60px auto;
}

.card {
    background: white;

    border-radius: 18px;

    padding: 32px;

    box-shadow:
        0 7px 25px
        rgba(0,0,0,.08);
}

.card h1 {
    margin-top: 6px;
}

.stop {
    display: block;

    border: 1px solid #ddd;

    border-radius: 12px;

    padding: 17px;

    margin-bottom: 13px;

    cursor: pointer;

    transition: .2s;
}

.stop:hover {
    border-color: #555;
    background: #fafafa;
}

.stop input {
    margin-right: 10px;
}

.stop small {
    display: block;

    margin-left: 28px;

    margin-top: 5px;

    color: #888;
}

.add-btn {
    width: 100%;

    border: none;

    margin-top: 15px;

    background: #333;

    color: white;

    padding: 13px;

    border-radius: 9px;

    font-weight: 700;

    cursor: pointer;
}

.add-btn:hover {
    background: #555;
}

.empty {
    background: #f7f7f7;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
}

</style>

</head>


<body>


<div class="container">


<div class="card">


<span class="label">
    ADD TO ITINERARY
</span>


<h1>
    Select Trip Stop
</h1>


<p>

Add

<strong>
    <?= htmlspecialchars($activity['name']) ?>
</strong>

to

<strong>
    <?= htmlspecialchars($trip['name']) ?>
</strong>

</p>



<?php if (!empty($stops)): ?>


<form
    method="POST"
    action="add-tourist-spot.php"
>


<input
    type="hidden"
    name="activity_id"
    value="<?= $activity_id ?>"
>


<input
    type="hidden"
    name="trip_id"
    value="<?= $trip_id ?>"
>


<?php foreach ($stops as $stop): ?>


<label class="stop">


<input
    type="radio"
    name="stop_id"
    value="<?= (int)$stop['id'] ?>"
    required
>


<strong>

📍 <?= htmlspecialchars($stop['city']) ?>

</strong>


<small>

<?= htmlspecialchars($stop['country'] ?? '') ?>


<?php if (!empty($stop['arrival_date'])): ?>

&nbsp; • &nbsp;

<?= date(
    "d M",
    strtotime($stop['arrival_date'])
) ?>

<?php endif; ?>


<?php if (!empty($stop['departure_date'])): ?>

-

<?= date(
    "d M",
    strtotime($stop['departure_date'])
) ?>

<?php endif; ?>


</small>


</label>


<?php endforeach; ?>


<button
    type="submit"
    class="add-btn"
>
    ＋ Add Tourist Spot
</button>


</form>


<?php else: ?>


<div class="empty">

<h3>
    No stops in this trip
</h3>

<p>
    Add a city to your trip first.
</p>

<a
    class="btn primary"
    href="trip-details.php?id=<?= $trip_id ?>"
>
    Add Trip Stop
</a>

</div>


<?php endif; ?>


</div>

</div>


</body>
</html>