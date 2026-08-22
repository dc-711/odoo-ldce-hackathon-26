<?php

session_start();
include("db.php");


// ========================================
// CHECK LOGIN
// ========================================

if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true
) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$name = $_SESSION['name'] ?? 'Traveler';


// ========================================
// CREATE USER INITIALS
// ========================================

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


// ========================================
// GET USER TRIPS + EXPENSE TOTAL
// ========================================

$trips = [];

$stmt = $conn->prepare("
    SELECT
        t.id,
        t.name,
        t.start_date,
        t.end_date,
        t.budget,
        t.currency,
        t.status,

        COALESCE(SUM(e.amount), 0) AS total_expense

    FROM trips t

    LEFT JOIN expenses e
        ON t.id = e.trip_id

    WHERE t.user_id = ?

    GROUP BY
        t.id,
        t.name,
        t.start_date,
        t.end_date,
        t.budget,
        t.currency,
        t.status

    ORDER BY t.start_date DESC
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $trips[] = $row;
}

$stmt->close();


// ========================================
// TOTAL BUDGET + TOTAL EXPENSE
// ========================================

$totalBudget = 0;
$totalExpense = 0;

foreach ($trips as $trip) {

    $totalBudget += (float)$trip['budget'];
    $totalExpense += (float)$trip['total_expense'];
}

$totalRemaining = $totalBudget - $totalExpense;

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Expenses | GlobeTrotter</title>

<link
    rel="stylesheet"
    href="assets/css/home.css"
>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    background: #f5f5f5;

    color: #333;
}


/* ==============================
   MAIN
============================== */

.expense-page {

    width: min(1200px, 92%);

    margin: 45px auto 70px;
}


/* ==============================
   HEADER
============================== */

.page-heading {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 30px;
}


.page-heading h1 {

    margin: 5px 0;

    font-size: 34px;
}


.page-heading p {

    margin: 0;

    color: #888;
}


.back-btn {

    text-decoration: none;

    background: #eeeeee;

    color: #444;

    padding: 11px 18px;

    border-radius: 9px;

    font-weight: 600;

    font-size: 14px;
}


.back-btn:hover {

    background: #ddd;
}


/* ==============================
   SUMMARY CARDS
============================== */

.summary-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;

    margin-bottom: 35px;
}


.summary-card {

    background: white;

    border-radius: 15px;

    padding: 25px;

    box-shadow:
        0 6px 20px
        rgba(0,0,0,.07);
}


.summary-card span {

    display: block;

    color: #888;

    font-size: 13px;

    margin-bottom: 8px;

    text-transform: uppercase;

    font-weight: 700;
}


.summary-card strong {

    font-size: 29px;

    color: #333;
}


.remaining-negative {

    color: #c62828 !important;
}


/* ==============================
   TRIP SECTION
============================== */

.section-title {

    margin-bottom: 20px;
}


.section-title h2 {

    margin: 0 0 5px;

    font-size: 25px;
}


.section-title p {

    margin: 0;

    color: #888;

    font-size: 14px;
}


/* ==============================
   TRIP CARDS
============================== */

.trip-expense-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 22px;
}


.expense-trip-card {

    background: white;

    border-radius: 16px;

    padding: 25px;

    box-shadow:
        0 6px 20px
        rgba(0,0,0,.07);

    transition: .3s;
}


.expense-trip-card:hover {

    transform: translateY(-4px);

    box-shadow:
        0 10px 25px
        rgba(0,0,0,.11);
}


.trip-top {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    margin-bottom: 18px;
}


.trip-top h3 {

    margin: 0 0 6px;

    font-size: 21px;
}


.trip-date {

    color: #888;

    font-size: 13px;
}


.status {

    background: #eeeeee;

    color: #555;

    padding: 6px 11px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 700;

    text-transform: capitalize;
}


/* ==============================
   COST INFO
============================== */

.cost-row {

    display: flex;

    justify-content: space-between;

    margin: 12px 0;

    color: #666;

    font-size: 14px;
}


.cost-row strong {

    color: #333;
}


.divider {

    height: 1px;

    background: #eee;

    margin: 18px 0;
}


/* ==============================
   PROGRESS BAR
============================== */

.progress {

    width: 100%;

    height: 9px;

    background: #eeeeee;

    border-radius: 20px;

    overflow: hidden;

    margin-top: 10px;
}


.progress-bar {

    height: 100%;

    background: #555;

    border-radius: 20px;
}


/* ==============================
   BUTTON
============================== */

.details-btn {

    display: inline-block;

    margin-top: 20px;

    text-decoration: none;

    background: #444;

    color: white;

    padding: 10px 17px;

    border-radius: 8px;

    font-size: 14px;

    font-weight: 600;
}


.details-btn:hover {

    background: #222;
}


/* ==============================
   EMPTY
============================== */

.empty {

    background: white;

    padding: 60px;

    text-align: center;

    border-radius: 15px;

    color: #888;

    border: 1px dashed #ccc;
}


.empty h3 {

    color: #444;
}


/* ==============================
   MOBILE
============================== */

@media(max-width: 750px) {

    .summary-grid,
    .trip-expense-grid {

        grid-template-columns: 1fr;
    }


    .page-heading {

        flex-direction: column;

        align-items: flex-start;

        gap: 20px;
    }

}

</style>

</head>


<body>


<!-- =================================
     NAVBAR
================================= -->

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

    <a href="cities.php">
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



<main class="expense-page">


<!-- =================================
     PAGE TITLE
================================= -->

<div class="page-heading">


<div>

<span class="label">
    TRAVEL FINANCES
</span>

<h1>
    Trip Expenses
</h1>

<p>
    Track your travel budget and spending
    across all your trips.
</p>

</div>


<a
    href="profile.php"
    class="back-btn"
>

    ← Back to Profile

</a>


</div>



<!-- =================================
     TOTAL SUMMARY
================================= -->

<section class="summary-grid">


<div class="summary-card">

<span>
    Total Trip Budget
</span>

<strong>

₹<?= number_format(
    $totalBudget,
    2
) ?>

</strong>

</div>



<div class="summary-card">

<span>
    Total Expenses
</span>

<strong>

₹<?= number_format(
    $totalExpense,
    2
) ?>

</strong>

</div>



<div class="summary-card">

<span>
    Remaining Budget
</span>

<strong
<?php if ($totalRemaining < 0): ?>
class="remaining-negative"
<?php endif; ?>
>

₹<?= number_format(
    $totalRemaining,
    2
) ?>

</strong>

</div>


</section>



<!-- =================================
     TRIPS
================================= -->

<div class="section-title">

<h2>
    Expenses by Trip
</h2>

<p>
    View budget usage for each of your trips.
</p>

</div>



<?php if (!empty($trips)): ?>


<div class="trip-expense-grid">


<?php foreach ($trips as $trip): ?>


<?php

$budget =
    (float)$trip['budget'];

$spent =
    (float)$trip['total_expense'];

$remaining =
    $budget - $spent;


// Progress percentage

if ($budget > 0) {

    $percentage =
        ($spent / $budget) * 100;

} else {

    $percentage = 0;
}


// Prevent progress bar > 100%

$barPercentage =
    min($percentage, 100);

?>


<article class="expense-trip-card">


<div class="trip-top">


<div>

<h3>

<?= htmlspecialchars(
    $trip['name']
) ?>

</h3>


<div class="trip-date">

<?php if (!empty($trip['start_date'])): ?>

<?= date(
    "d M Y",
    strtotime($trip['start_date'])
) ?>

<?php endif; ?>


<?php if (!empty($trip['end_date'])): ?>

-

<?= date(
    "d M Y",
    strtotime($trip['end_date'])
) ?>

<?php endif; ?>

</div>

</div>


<span class="status">

<?= htmlspecialchars(
    $trip['status']
) ?>

</span>


</div>



<div class="divider"></div>



<div class="cost-row">

<span>
    Trip Budget
</span>

<strong>

₹<?= number_format(
    $budget,
    2
) ?>

</strong>

</div>



<div class="cost-row">

<span>
    Total Spent
</span>

<strong>

₹<?= number_format(
    $spent,
    2
) ?>

</strong>

</div>



<div class="cost-row">

<span>
    Remaining
</span>

<strong>

₹<?= number_format(
    $remaining,
    2
) ?>

</strong>

</div>



<div class="progress">

<div
    class="progress-bar"
    style="
        width:
        <?= $barPercentage ?>%;
    "
></div>

</div>


<div class="cost-row">

<span>
    Budget Used
</span>

<strong>

<?= number_format(
    $percentage,
    1
) ?>%

</strong>

</div>



<?php if ($remaining < 0): ?>

<p
style="
color:#c62828;
font-size:13px;
font-weight:600;
"
>

⚠ Over budget by

₹<?= number_format(
    abs($remaining),
    2
) ?>

</p>

<?php endif; ?>



<a
href="expense-details.php?trip_id=<?= (int)$trip['id'] ?>"
class="details-btn"
>

View Expense Details →

</a>


</article>


<?php endforeach; ?>


</div>


<?php else: ?>


<div class="empty">

<h3>
    No trips found
</h3>

<p>
    Create a trip first to start
    tracking your travel expenses.
</p>

<a
href="create-trip.php"
class="details-btn"
>

+ Plan New Trip

</a>

</div>


<?php endif; ?>


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