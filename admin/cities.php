<?php
session_start();
include("db.php");

// Protect page
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Traveler';


// =====================================================
// FILTER VALUES
// =====================================================

$selectedState = trim($_GET['state'] ?? '');
$selectedCity = (int)($_GET['city'] ?? 0);


// =====================================================
// GET STATES
// =====================================================

$states = [];

$result = $conn->query("
    SELECT DISTINCT region
    FROM cities
    WHERE region IS NOT NULL
      AND region != ''
    ORDER BY region ASC
");

while ($row = $result->fetch_assoc()) {
    $states[] = $row['region'];
}


// =====================================================
// GET CITIES
// =====================================================

$cities = [];

if ($selectedState !== '') {

    $stmt = $conn->prepare("
        SELECT
            id,
            city_name,
            country,
            region,
            description,
            image,
            cost_index,
            popularity,
            latitude,
            longitude
        FROM cities
        WHERE region = ?
        ORDER BY popularity DESC, city_name ASC
    ");

    $stmt->bind_param("s", $selectedState);

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $cities[] = $row;
    }

    $stmt->close();

} else {

    $result = $conn->query("
        SELECT
            id,
            city_name,
            country,
            region,
            description,
            image,
            cost_index,
            popularity,
            latitude,
            longitude
        FROM cities
        ORDER BY popularity DESC, city_name ASC
    ");

    while ($row = $result->fetch_assoc()) {
        $cities[] = $row;
    }
}


// =====================================================
// SELECTED CITY DETAILS
// =====================================================

$cityDetails = null;
$places = [];

if ($selectedCity > 0) {

    $stmt = $conn->prepare("
        SELECT
            id,
            city_name,
            country,
            region,
            description,
            image,
            cost_index,
            popularity,
            latitude,
            longitude
        FROM cities
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $selectedCity);

    $stmt->execute();

    $result = $stmt->get_result();

    $cityDetails = $result->fetch_assoc();

    $stmt->close();


    // =================================================
    // TOURIST PLACES / ACTIVITIES
    //
    // Current DB structure:
    //
    // activities.stop_id
    //        ↓
    // stops.id
    //
    // stops contains city name
    // =================================================

    if ($cityDetails) {

        $stmt = $conn->prepare("
            SELECT DISTINCT

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

            WHERE s.city = ?

            ORDER BY
                a.rating DESC,
                a.name ASC
        ");

        $stmt->bind_param(
            "s",
            $cityDetails['city_name']
        );

        $stmt->execute();

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $places[] = $row;
        }

        $stmt->close();
    }
}


// =====================================================
// CHECK USER FAVORITES
// =====================================================

$favoriteCityIds = [];

$stmt = $conn->prepare("
    SELECT city_id
    FROM favorites
    WHERE user_id = ?
      AND city_id IS NOT NULL
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $favoriteCityIds[] = (int)$row['city_id'];
}

$stmt->close();


// =====================================================
// USER INITIALS
// =====================================================

$words = preg_split('/\s+/', trim($name));

$initials = "";

foreach ($words as $word) {

    if (!empty($word)) {

        $initials .= strtoupper(
            substr($word, 0, 1)
        );
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

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Explore Cities | GlobeTrotter
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

    color: #333;
}


/* =====================================================
   PAGE
===================================================== */

.cities-page {

    padding-top: 25px;
}


/* =====================================================
   HERO
===================================================== */

.city-hero {

    width: min(1200px, 92%);

    margin: 0 auto 45px;

    min-height: 320px;

    border-radius: 28px;

    overflow: hidden;

    display: flex;

    align-items: center;

    padding: 55px;

    color: white;

    background:

        linear-gradient(
            90deg,
            rgba(25,25,25,.84),
            rgba(25,25,25,.35)
        ),

        url(
        'assets/img/beautiful-landscape-view-covered-with-greenery-mountains-background.jpg'
        )
        center / cover;

    box-shadow:
        0 16px 40px
        rgba(0,0,0,.12);
}


.city-hero-content {

    max-width: 650px;
}


.city-hero h1 {

    margin: 12px 0;

    font-size: 48px;

    line-height: 1.1;
}


.city-hero h1 em {

    font-style: normal;

    color: #ddd;
}


.city-hero p {

    color: #e7e7e7;

    line-height: 1.7;

    max-width: 620px;
}


/* =====================================================
   FILTER
===================================================== */

.filter-card {

    width: min(1100px, 90%);

    margin: -75px auto 50px;

    background: white;

    border-radius: 18px;

    padding: 25px;

    position: relative;

    z-index: 10;

    box-shadow:
        0 10px 30px
        rgba(0,0,0,.10);
}


.filter-card h3 {

    margin: 0 0 20px;
}


.filter-grid {

    display: grid;

    grid-template-columns:
        1fr 1fr auto;

    gap: 15px;

    align-items: end;
}


.filter-group label {

    display: block;

    margin-bottom: 7px;

    font-size: 13px;

    font-weight: 700;

    color: #555;
}


.filter-group select {

    width: 100%;

    padding: 12px;

    border: 1px solid #ddd;

    border-radius: 9px;

    background: #fafafa;

    font-size: 14px;
}


.filter-search {

    border: none;

    background: #333;

    color: white;

    padding: 13px 22px;

    border-radius: 9px;

    font-weight: 700;

    cursor: pointer;
}


.filter-search:hover {

    background: #555;
}


/* =====================================================
   CONTENT
===================================================== */

.cities-container {

    width: min(1200px, 92%);

    margin: auto;
}


/* =====================================================
   SELECTED CITY
===================================================== */

.selected-city {

    background: white;

    border-radius: 20px;

    overflow: hidden;

    margin-bottom: 55px;

    box-shadow:
        0 8px 25px
        rgba(0,0,0,.08);
}


.selected-header {

    padding: 32px;

    background: #333;

    color: white;
}


.selected-header h2 {

    margin: 7px 0;

    font-size: 34px;
}


.selected-header p {

    margin: 0;

    color: #ddd;
}


.selected-description {

    margin-top: 14px !important;

    max-width: 850px;

    line-height: 1.6;
}


/* =====================================================
   TOURIST PLACES
===================================================== */

.places-wrapper {

    padding: 28px 32px 32px;
}


.places-wrapper > h2 {

    margin-bottom: 5px;
}


.places-subtitle {

    margin: 0 0 20px;

    color: #888;
}


.place-card {

    display: grid;

    grid-template-columns:
        210px 1fr;

    gap: 24px;

    padding: 22px 0;

    border-bottom: 1px solid #eee;
}


.place-card:last-child {

    border-bottom: none;
}


.place-image {

    height: 155px;

    border-radius: 14px;

    background-size: cover;

    background-position: center;

    background-color: #ddd;
}


.place-content h3 {

    margin: 0 0 7px;

    font-size: 21px;
}


.place-location {

    margin-bottom: 10px;

    color: #777;

    font-size: 14px;
}


.place-description {

    color: #666;

    font-size: 14px;

    line-height: 1.6;

    margin-bottom: 14px;
}


.place-meta {

    display: flex;

    flex-wrap: wrap;

    gap: 17px;

    color: #555;

    font-size: 13px;

    margin-bottom: 15px;
}


.spot-btn {

    display: inline-block;

    padding: 10px 16px;

    background: #333;

    color: white;

    text-decoration: none;

    border-radius: 8px;

    font-size: 13px;

    font-weight: 700;
}


.spot-btn:hover {

    background: #555;
}


/* =====================================================
   CITY GRID
===================================================== */

.city-grid-custom {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 24px;
}


.city-card {

    background: white;

    border-radius: 18px;

    overflow: hidden;

    box-shadow:
        0 7px 22px
        rgba(0,0,0,.07);

    transition: .3s;
}


.city-card:hover {

    transform: translateY(-6px);

    box-shadow:
        0 12px 30px
        rgba(0,0,0,.12);
}


.city-image {

    height: 220px;

    background-size: cover;

    background-position: center;

    position: relative;
}


.city-default {

    background:
        linear-gradient(
            135deg,
            #444,
            #999
        );
}


.popularity {

    position: absolute;

    top: 14px;

    left: 14px;

    background:
        rgba(255,255,255,.95);

    color: #333;

    padding: 7px 11px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 700;
}


.favorite {

    position: absolute;

    top: 14px;

    right: 14px;

    width: 39px;

    height: 39px;

    border-radius: 50%;

    background: white;

    display: flex;

    align-items: center;

    justify-content: center;

    color: #333;

    text-decoration: none;

    font-size: 19px;

    box-shadow:
        0 3px 9px
        rgba(0,0,0,.16);
}


.favorite.active {

    color: #c62828;
}


.city-body {

    padding: 20px;
}


.city-body h3 {

    margin: 0 0 7px;

    font-size: 22px;
}


.city-location {

    margin: 0 0 12px;

    color: #777;

    font-size: 14px;
}


.city-description {

    color: #666;

    font-size: 14px;

    line-height: 1.6;

    min-height: 67px;
}


.city-meta {

    display: flex;

    justify-content: space-between;

    border-top: 1px solid #eee;

    margin-top: 15px;

    padding-top: 14px;

    color: #666;

    font-size: 13px;
}


.city-btn {

    display: block;

    margin-top: 17px;

    background: #333;

    color: white;

    padding: 11px;

    border-radius: 9px;

    text-align: center;

    text-decoration: none;

    font-weight: 700;

    font-size: 14px;
}


.city-btn:hover {

    background: #555;
}


/* =====================================================
   EMPTY
===================================================== */

.empty {

    background: white;

    padding: 50px;

    text-align: center;

    color: #777;

    border-radius: 18px;
}


/* =====================================================
   PROFILE LINK
===================================================== */

.profile-link {

    text-decoration: none;

    color: inherit;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width: 900px) {

    .city-grid-custom {

        grid-template-columns:
            repeat(2, 1fr);
    }


    .filter-grid {

        grid-template-columns:
            1fr 1fr;
    }


    .filter-search {

        grid-column: 1 / -1;
    }

}


@media(max-width: 650px) {

    .city-hero {

        padding: 35px 25px;
    }


    .city-hero h1 {

        font-size: 36px;
    }


    .city-grid-custom,
    .filter-grid {

        grid-template-columns: 1fr;
    }


    .place-card {

        grid-template-columns: 1fr;
    }


    .place-image {

        height: 220px;
    }

}

</style>

</head>


<body>


<!-- =====================================================
     HEADER
===================================================== -->

<header class="topbar">


<a
    class="brand"
    href="homepage.php"
>

    <span class="mark">
        ✈
    </span>

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

    <a
        class="active"
        href="cities.php"
    >
        Explore
    </a>

    <a href="activities.php">
        Activities
    </a>

</nav>


<div class="actions">

<a
    href="profile.php"
    class="profile-link"
>

<div class="profile">


<div class="avatar">

<?= htmlspecialchars(
    $initials
) ?>

</div>


<div>

<b>

<?= htmlspecialchars(
    $name
) ?>

</b>

<small>
    My Profile
</small>

</div>


</div>

</a>

</div>


</header>



<main class="cities-page">


<!-- =====================================================
     HERO
===================================================== -->

<section class="city-hero">


<div class="city-hero-content">


<span class="eyebrow">

    DISCOVER YOUR NEXT DESTINATION

</span>


<h1>

    Explore cities and
    <br>

    <em>
        unforgettable places.
    </em>

</h1>


<p>

    Find destinations by state and city,
    discover popular tourist attractions,
    and add interesting places to your trip.

</p>


</div>


</section>



<!-- =====================================================
     FILTER
===================================================== -->

<section class="filter-card">


<h3>
    Find a destination
</h3>


<form
    method="GET"
    action="cities.php"
>


<div class="filter-grid">


<!-- STATE -->

<div class="filter-group">


<label>
    State
</label>


<select
    name="state"
    onchange="this.form.submit()"
>


<option value="">
    All States
</option>


<?php foreach ($states as $state): ?>


<option

value="<?= htmlspecialchars($state) ?>"

<?= $selectedState === $state
    ? 'selected'
    : ''
?>

>


<?= htmlspecialchars(
    $state
) ?>


</option>


<?php endforeach; ?>


</select>


</div>



<!-- CITY -->

<div class="filter-group">


<label>
    City
</label>


<select name="city">


<option value="0">
    Select City
</option>


<?php foreach ($cities as $city): ?>


<option

value="<?= (int)$city['id'] ?>"

<?= $selectedCity === (int)$city['id']
    ? 'selected'
    : ''
?>

>


<?= htmlspecialchars(
    $city['city_name']
) ?>


</option>


<?php endforeach; ?>


</select>


</div>



<button
    type="submit"
    class="filter-search"
>

    Search Destination

</button>


</div>


</form>


</section>



<div class="cities-container">


<!-- =====================================================
     SELECTED CITY + TOURIST PLACES
===================================================== -->

<?php if ($cityDetails): ?>


<section class="selected-city">


<div class="selected-header">


<span class="label">
    CITY GUIDE
</span>


<h2>

<?= htmlspecialchars(
    $cityDetails['city_name']
) ?>

</h2>


<p>

📍

<?= htmlspecialchars(
    $cityDetails['region']
) ?>,

<?= htmlspecialchars(
    $cityDetails['country']
) ?>

</p>


<?php if (
    !empty(
        $cityDetails['description']
    )
): ?>


<p class="selected-description">

<?= htmlspecialchars(
    $cityDetails['description']
) ?>

</p>


<?php endif; ?>


</div>



<div class="places-wrapper">


<h2>
    Popular & Tourist Places
</h2>


<p class="places-subtitle">

Discover interesting places in

<?= htmlspecialchars(
    $cityDetails['city_name']
) ?>.

</p>



<?php if (!empty($places)): ?>


<?php foreach ($places as $p): ?>


<article class="place-card">


<!-- IMAGE -->

<div

class="place-image"

<?php if (
    !empty($p['image'])
): ?>

style="
background-image:
url('<?= htmlspecialchars(
    $p['image']
) ?>');
"

<?php endif; ?>

></div>



<div class="place-content">


<h3>

<?= htmlspecialchars(
    $p['name']
) ?>

</h3>



<div class="place-location">

📍

<?= htmlspecialchars(
    !empty($p['location'])
        ? $p['location']
        : $p['city']
) ?>

</div>



<div class="place-description">

<?= htmlspecialchars(
    !empty($p['notes'])
        ? $p['notes']
        : 'Popular tourist place to explore during your trip.'
) ?>

</div>



<div class="place-meta">


<span>

⭐
<?= htmlspecialchars(
    $p['rating']
) ?>

</span>


<span>

🏷

<?= htmlspecialchars(
    !empty($p['activity_type'])
        ? $p['activity_type']
        : 'Tourist Spot'
) ?>

</span>


<span>

💰 ₹<?= number_format(
    (float)$p['estimated_cost'],
    0
) ?>

</span>


<span>

⏱
<?= (int)$p['duration_minutes'] ?>
min

</span>


</div>



<a
    class="spot-btn"

    href="tourist-spot.php?id=<?= (int)$p['id'] ?>"
>

    Explore Tourist Spot →

</a>


</div>


</article>


<?php endforeach; ?>


<?php else: ?>


<div class="empty">


<h3>
    No tourist places found
</h3>


<p>

No activities are currently linked
to stops for this city.

</p>


</div>


<?php endif; ?>


</div>


</section>


<?php endif; ?>



<!-- =====================================================
     CITY LIST
===================================================== -->

<section>


<div class="heading">


<div>

<span class="label">
    GET INSPIRED
</span>

<h2>
    Explore cities
</h2>

</div>


</div>



<?php if (!empty($cities)): ?>


<div class="city-grid-custom">


<?php foreach ($cities as $city): ?>


<?php

$isFavorite =
    in_array(
        (int)$city['id'],
        $favoriteCityIds,
        true
    );

?>


<article class="city-card">


<!-- IMAGE -->

<div

class="
city-image
<?= empty($city['image'])
    ? 'city-default'
    : ''
?>
"

<?php if (
    !empty($city['image'])
): ?>

style="
background-image:
url('<?= htmlspecialchars(
    $city['image']
) ?>');
"

<?php endif; ?>

>


<span class="popularity">

⭐

<?= htmlspecialchars(
    $city['popularity']
) ?>

</span>



<a
    href="toggle-favorite.php?city_id=<?= (int)$city['id'] ?>"

    class="
    favorite
    <?= $isFavorite
        ? 'active'
        : ''
    ?>
    "

    title="
    <?= $isFavorite
        ? 'Remove from favorites'
        : 'Add to favorites'
    ?>
    "
>


<?= $isFavorite
    ? '♥'
    : '♡'
?>


</a>


</div>



<div class="city-body">


<h3>

<?= htmlspecialchars(
    $city['city_name']
) ?>

</h3>



<p class="city-location">

📍

<?= htmlspecialchars(
    $city['region']
) ?>,

<?= htmlspecialchars(
    $city['country']
) ?>

</p>



<p class="city-description">

<?= htmlspecialchars(

    !empty(
        $city['description']
    )

    ? $city['description']

    : 'Discover this destination and explore its popular attractions.'

) ?>

</p>



<div class="city-meta">


<span>

💰 Cost:

<?= htmlspecialchars(
    $city['cost_index']
) ?>

</span>


<span>

⭐

<?= htmlspecialchars(
    $city['popularity']
) ?>

</span>


</div>



<a

class="city-btn"

href="cities.php?state=<?= urlencode(
    $city['region']
) ?>&city=<?= (int)$city['id'] ?>"

>

Explore Tourist Places →

</a>


</div>


</article>


<?php endforeach; ?>


</div>


<?php else: ?>


<div class="empty">


<h3>
    No cities found
</h3>


<p>
    Select another state.
</p>


</div>


<?php endif; ?>


</section>



<!-- =====================================================
     BANNER
===================================================== -->

<section
    class="banner"
    style="margin-top:60px;"
>


<div>


<span class="label">
    START EXPLORING
</span>


<h2>
    Found somewhere you love?
</h2>


<p>

Save the city to your favorites
or add its tourist places to your trip.

</p>


</div>


<a
    class="btn light"
    href="create-trip.php"
>

    Plan a Trip →

</a>


</section>


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


<script
    src="assets/js/home.js"
></script>


</body>

</html>