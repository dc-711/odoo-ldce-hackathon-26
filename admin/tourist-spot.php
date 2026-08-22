<?php

session_start();
include("db.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Traveler';

$activity_id = (int)($_GET['id'] ?? 0);

if ($activity_id <= 0) {
    header("Location: cities.php");
    exit;
}


// =========================================
// GET TOURIST SPOT
// =========================================

$stmt = $conn->prepare("
    SELECT
        a.id,
        a.name,
        a.activity_type,
        a.estimated_cost,
        a.start_time,
        a.notes,
        a.location,
        a.duration_minutes,
        a.rating,
        a.image,

        s.city,
        s.country

    FROM activities a

    INNER JOIN stops s
        ON a.stop_id = s.id

    WHERE a.id = ?

    LIMIT 1
");

$stmt->bind_param("i", $activity_id);

$stmt->execute();

$result = $stmt->get_result();

$activity = $result->fetch_assoc();

$stmt->close();


if (!$activity) {
    die("Tourist spot not found.");
}


// =========================================
// GET USER UPCOMING TRIPS
// =========================================

$trips = [];

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        start_date,
        end_date,
        destination

    FROM trips

    WHERE user_id = ?
      AND status IN ('draft', 'planned')

    ORDER BY start_date ASC
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $trips[] = $row;
}

$stmt->close();


// =========================================
// USER INITIALS
// =========================================

$words = preg_split('/\s+/', trim($name));

$initials = "";

foreach ($words as $word) {

    if (!empty($word)) {
        $initials .= strtoupper(substr($word, 0, 1));
    }

    if (strlen($initials) >= 2) {
        break;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
    <?= htmlspecialchars($activity['name']) ?> | GlobeTrotter
</title>

<link rel="stylesheet" href="assets/css/home.css">

<style>

body {
    margin: 0;
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f5f5f5;
    color: #333;
}

.spot-page {
    width: min(1100px, 92%);
    margin: 45px auto 70px;
}

.spot-image {
    height: 420px;
    border-radius: 24px;
    background-size: cover;
    background-position: center;
    position: relative;
    overflow: hidden;
    background-color: #777;
}

.overlay {
    position: absolute;
    inset: 0;

    background:
        linear-gradient(
            to top,
            rgba(0,0,0,.78),
            rgba(0,0,0,.08)
        );

    display: flex;
    align-items: flex-end;
    padding: 38px;
    color: white;
}

.overlay h1 {
    font-size: 40px;
    margin: 7px 0;
}

.overlay p {
    margin: 0;
    color: #eee;
}

.layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
    margin-top: 28px;
}

.card {
    background: white;
    border-radius: 18px;
    padding: 28px;

    box-shadow:
        0 7px 22px
        rgba(0,0,0,.07);
}

.card h2 {
    margin-top: 5px;
}

.description {
    line-height: 1.7;
    color: #666;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 13px;
    margin-top: 22px;
}

.info {
    background: #f7f7f7;
    border-radius: 10px;
    padding: 15px;
}

.info small {
    display: block;
    color: #888;
    margin-bottom: 5px;
}

.add-card {
    height: fit-content;
}

.add-card label {
    display: block;
    margin: 18px 0 7px;
    font-weight: 600;
}

.add-card select {
    width: 100%;
    padding: 12px;

    border: 1px solid #ddd;
    border-radius: 9px;
}

.add-btn {
    width: 100%;

    border: none;

    background: #333;
    color: white;

    padding: 13px;

    border-radius: 9px;

    margin-top: 18px;

    cursor: pointer;

    font-weight: 700;
}

.add-btn:hover {
    background: #555;
}

.back {
    display: block;
    text-align: center;
    margin-top: 14px;

    text-decoration: none;
    color: #666;
}

.profile-link {
    text-decoration: none;
    color: inherit;
}

@media(max-width: 800px) {

    .layout {
        grid-template-columns: 1fr;
    }

    .spot-image {
        height: 310px;
    }

    .overlay h1 {
        font-size: 30px;
    }

}

</style>

</head>

<body>


<header class="topbar">

<a class="brand" href="homepage.php">

    <span class="mark">✈</span>

    <span>
        Globe<span>Trotter</span>
    </span>

</a>


<nav>

    <a href="homepage.php">
        Home
    </a>

    <a href="my-trips.php">
        My Trips
    </a>

    <a class="active" href="cities.php">
        Explore
    </a>

    <a href="activities.php">
        Activities
    </a>

</nav>


<div class="actions">

    <a href="profile.php" class="profile-link">

        <div class="profile">

            <div class="avatar">
                <?= htmlspecialchars($initials) ?>
            </div>

            <div>

                <b>
                    <?= htmlspecialchars($name) ?>
                </b>

                <small>
                    My Profile
                </small>

            </div>

        </div>

    </a>

</div>

</header>



<main class="spot-page">


<section
    class="spot-image"

    <?php if (!empty($activity['image'])): ?>

    style="
        background-image:
        url('<?= htmlspecialchars($activity['image']) ?>')
    "

    <?php endif; ?>
>


<div class="overlay">

<div>

<span class="label">

    <?= htmlspecialchars(
        $activity['activity_type'] ?: 'Tourist Spot'
    ) ?>

</span>


<h1>

    <?= htmlspecialchars($activity['name']) ?>

</h1>


<p>

📍

<?= htmlspecialchars(
    $activity['location'] ?: $activity['city']
) ?>,

<?= htmlspecialchars($activity['country']) ?>

</p>

</div>

</div>

</section>



<div class="layout">


<section class="card">

<span class="label">
    ABOUT THIS PLACE
</span>

<h2>
    Tourist Spot Details
</h2>


<p class="description">

<?= nl2br(
    htmlspecialchars(
        $activity['notes']
        ?: 'Discover this tourist attraction and add it to your itinerary.'
    )
) ?>

</p>


<div class="info-grid">


<div class="info">

<small>
Rating
</small>

<strong>

⭐ <?= htmlspecialchars($activity['rating']) ?>

</strong>

</div>


<div class="info">

<small>
Estimated Cost
</small>

<strong>

₹<?= number_format(
    (float)$activity['estimated_cost'],
    0
) ?>

</strong>

</div>


<div class="info">

<small>
Duration
</small>

<strong>

<?= (int)$activity['duration_minutes'] ?>
minutes

</strong>

</div>


<div class="info">

<small>
Type
</small>

<strong>

<?= htmlspecialchars(
    $activity['activity_type']
    ?: 'Tourist Spot'
) ?>

</strong>

</div>


<div class="info">

<small>
City
</small>

<strong>

<?= htmlspecialchars($activity['city']) ?>

</strong>

</div>


<div class="info">

<small>
Country
</small>

<strong>

<?= htmlspecialchars($activity['country']) ?>

</strong>

</div>


</div>

</section>



<aside class="card add-card">

<span class="label">
    YOUR ITINERARY
</span>

<h2>
    Add to My Trip
</h2>

<p class="description">
    Choose one of your upcoming trips.
</p>


<?php if (!empty($trips)): ?>


<form
    method="GET"
    action="select-trip-stop.php"
>

<input
    type="hidden"
    name="activity_id"
    value="<?= $activity_id ?>"
>


<label>
    Choose Trip
</label>


<select
    name="trip_id"
    required
>

<option value="">
    Select Trip
</option>


<?php foreach ($trips as $trip): ?>

<option value="<?= (int)$trip['id'] ?>">

<?= htmlspecialchars($trip['name']) ?>

-
<?= date(
    "d M",
    strtotime($trip['start_date'])
) ?>

to

<?= date(
    "d M Y",
    strtotime($trip['end_date'])
) ?>

</option>

<?php endforeach; ?>


</select>


<button
    type="submit"
    class="add-btn"
>

＋ Add to My Trip

</button>

</form>


<?php else: ?>


<p class="description">
    You don't have any planned trips.
</p>

<a
    href="create-trip.php"
    class="add-btn"
    style="display:block;text-align:center;text-decoration:none;"
>
    + Create Trip
</a>


<?php endif; ?>


<a
    href="cities.php"
    class="back"
>
    ← Back to Explore
</a>


</aside>


</div>

</main>


<footer>

<b>
    ✈ GlobeTrotter
</b>

<span>
    Empowering personalized travel planning.
</span>

<span>
    © <?= date('Y') ?> GlobeTrotter
</span>

</footer>


</body>
</html>
