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


// ========================================
// GET USER INFORMATION
// ========================================

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        email,
        age,
        address,
        city,
        state,
        role,
        status,
        created_at
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

$stmt->close();


if (!$user) {

    session_destroy();

    header("Location: login.php");
    exit;
}


// ========================================
// TOTAL TRIPS
// ========================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM trips
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$totalTrips =
    $stmt->get_result()
         ->fetch_assoc()['total'];

$stmt->close();


// ========================================
// COMPLETED TRIPS
// ========================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM trips
    WHERE user_id = ?
      AND status = 'completed'
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$completedTrips =
    $stmt->get_result()
         ->fetch_assoc()['total'];

$stmt->close();


// ========================================
// FAVORITE PLACES
// ========================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM favorites
    WHERE user_id = ?
      AND city_id IS NOT NULL
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$totalFavorites =
    $stmt->get_result()
         ->fetch_assoc()['total'];

$stmt->close();


// ========================================
// TOTAL EXPENSES
// ========================================

$stmt = $conn->prepare("
    SELECT
        COALESCE(SUM(e.amount), 0) AS total_expense
    FROM expenses e

    INNER JOIN trips t
        ON e.trip_id = t.id

    WHERE t.user_id = ?
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$totalExpense =
    $stmt->get_result()
         ->fetch_assoc()['total_expense'];

$stmt->close();


// ========================================
// TOTAL BUDGET
// ========================================

$stmt = $conn->prepare("
    SELECT
        COALESCE(SUM(budget), 0) AS total_budget
    FROM trips
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$totalBudget =
    $stmt->get_result()
         ->fetch_assoc()['total_budget'];

$stmt->close();


$remainingBudget =
    (float)$totalBudget -
    (float)$totalExpense;


// ========================================
// USER INITIALS
// ========================================

$words = preg_split(
    '/\s+/',
    trim($user['name'])
);

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
    My Profile | GlobeTrotter
</title>

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
   PROFILE HERO
============================== */

.profile-hero {

    width: min(1200px, 92%);

    margin: 40px auto 0;

    min-height: 280px;

    border-radius: 25px;

    background:

        linear-gradient(
            90deg,
            rgba(30,30,30,.85),
            rgba(30,30,30,.45)
        ),

        url(
        'assets/img/beautiful-landscape-view-covered-with-greenery-mountains-background.jpg'
        )
        center / cover;

    display: flex;

    align-items: center;

    padding: 50px;

    color: white;

    box-shadow:
        0 15px 35px
        rgba(0,0,0,.12);
}


.profile-avatar-large {

    width: 115px;

    height: 115px;

    min-width: 115px;

    border-radius: 50%;

    background: white;

    color: #333;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 38px;

    font-weight: 700;

    margin-right: 28px;

    border:
        5px solid
        rgba(255,255,255,.4);
}


.profile-hero h1 {

    margin: 0 0 7px;

    font-size: 36px;
}


.profile-hero p {

    margin: 0;

    color: #ddd;
}


/* ==============================
   MAIN
============================== */

.profile-container {

    width: min(1100px, 90%);

    margin: 35px auto 70px;
}


/* ==============================
   STATISTICS
============================== */

.profile-stats {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 18px;

    margin-bottom: 30px;
}


.stat-card {

    background: white;

    padding: 24px;

    border-radius: 15px;

    box-shadow:
        0 6px 20px
        rgba(0,0,0,.07);

    transition: .3s;
}


.stat-card strong {

    display: block;

    font-size: 28px;

    margin-bottom: 5px;

    color: #333;
}


.stat-card span {

    color: #888;

    font-size: 13px;
}


.clickable-stat {

    text-decoration: none;

    color: inherit;

    cursor: pointer;
}


.clickable-stat:hover {

    transform: translateY(-5px);

    box-shadow:
        0 10px 26px
        rgba(0,0,0,.12);
}


/* ==============================
   BUDGET SUMMARY
============================== */

.budget-summary {

    background: #333;

    color: white;

    border-radius: 18px;

    padding: 28px;

    margin-bottom: 30px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;
}


.budget-summary h2 {

    margin: 5px 0;

    font-size: 25px;
}


.budget-summary p {

    margin: 0;

    color: #ccc;
}


.budget-values {

    display: flex;

    gap: 30px;

    text-align: right;
}


.budget-item small {

    display: block;

    color: #bbb;

    margin-bottom: 5px;
}


.budget-item strong {

    font-size: 20px;
}


.budget-btn {

    display: inline-block;

    margin-top: 15px;

    background: white;

    color: #333;

    text-decoration: none;

    padding: 10px 16px;

    border-radius: 8px;

    font-weight: 700;

    font-size: 13px;
}


/* ==============================
   PROFILE CARD
============================== */

.profile-card {

    background: white;

    padding: 32px;

    border-radius: 18px;

    box-shadow:
        0 6px 20px
        rgba(0,0,0,.07);
}


.card-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    border-bottom:
        1px solid #eee;

    padding-bottom: 20px;

    margin-bottom: 25px;
}


.card-header h2 {

    margin: 4px 0 0;
}


.edit-btn {

    background: #444;

    color: white;

    text-decoration: none;

    padding: 10px 18px;

    border-radius: 8px;

    font-size: 14px;

    font-weight: 600;

    transition: .3s;
}


.edit-btn:hover {

    background: #222;

    transform: translateY(-2px);
}


/* ==============================
   INFORMATION
============================== */

.info-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 20px;
}


.info-item {

    background: #fafafa;

    padding: 18px;

    border-radius: 10px;

    border: 1px solid #eee;
}


.info-item label {

    display: block;

    color: #999;

    font-size: 12px;

    text-transform: uppercase;

    margin-bottom: 7px;

    font-weight: 700;
}


.info-item span {

    color: #333;

    font-size: 15px;

    font-weight: 600;
}


.full {

    grid-column: 1 / -1;
}


/* ==============================
   QUICK LINKS
============================== */

.quick-links {

    margin-top: 30px;

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 16px;
}


.quick-link {

    padding: 20px;

    border-radius: 14px;

    background: #fafafa;

    border: 1px solid #eee;

    text-decoration: none;

    color: #333;

    transition: .3s;
}


.quick-link:hover {

    background: #f1f1f1;

    transform: translateY(-3px);
}


.quick-link strong {

    display: block;

    margin-bottom: 5px;
}


.quick-link span {

    font-size: 13px;

    color: #888;
}


/* ==============================
   ACCOUNT
============================== */

.account {

    margin-top: 30px;

    padding-top: 25px;

    border-top: 1px solid #eee;

    display: flex;

    justify-content: space-between;

    align-items: center;
}


.account small {

    display: block;

    color: #999;

    margin-top: 5px;
}


.logout {

    text-decoration: none;

    background: #eee;

    color: #444;

    padding: 10px 18px;

    border-radius: 8px;

    font-weight: 600;
}


.logout:hover {

    background: #ddd;
}


/* ==============================
   RESPONSIVE
============================== */

@media(max-width: 950px) {

    .profile-stats {

        grid-template-columns:
            repeat(2, 1fr);
    }


    .quick-links {

        grid-template-columns:
            repeat(2, 1fr);
    }


    .budget-summary {

        flex-direction: column;

        align-items: flex-start;
    }


    .budget-values {

        text-align: left;
    }

}


@media(max-width: 650px) {

    .profile-hero {

        flex-direction: column;

        text-align: center;

        padding: 35px 20px;
    }


    .profile-avatar-large {

        margin:
            0 0 20px;
    }


    .profile-stats,
    .quick-links {

        grid-template-columns: 1fr;
    }


    .info-grid {

        grid-template-columns: 1fr;
    }


    .full {

        grid-column: auto;
    }


    .card-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;
    }


    .account {

        flex-direction: column;

        align-items: flex-start;

        gap: 20px;
    }


    .budget-values {

        flex-direction: column;

        gap: 15px;
    }

}

</style>

</head>


<body>


<!-- ==============================
     NAVIGATION
============================== -->

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
        href="homepage.php"
        class="btn light"
    >

        ← Back Home

    </a>

</div>


</header>



<!-- ==============================
     PROFILE HERO
============================== -->

<section class="profile-hero">


<div class="profile-avatar-large">

    <?= htmlspecialchars(
        $initials
    ) ?>

</div>


<div>

    <span class="eyebrow">

        TRAVELER PROFILE

    </span>


    <h1>

        <?= htmlspecialchars(
            $user['name']
        ) ?>

    </h1>


    <p>

        ✉
        <?= htmlspecialchars(
            $user['email']
        ) ?>

        <?php if (!empty($user['city'])): ?>

            &nbsp; • &nbsp;

            📍
            <?= htmlspecialchars(
                $user['city']
            ) ?>

        <?php endif; ?>

    </p>

</div>


</section>



<!-- ==============================
     PROFILE CONTENT
============================== -->

<main class="profile-container">


<!-- ==============================
     STAT CARDS
============================== -->

<section class="profile-stats">


<a
    href="my-trips.php"
    class="stat-card clickable-stat"
>

    <strong>
        <?= (int)$totalTrips ?>
    </strong>

    <span>
        Trips Planned
    </span>

</a>



<a
    href="my-trips.php"
    class="stat-card clickable-stat"
>

    <strong>
        <?= (int)$completedTrips ?>
    </strong>

    <span>
        Completed Trips
    </span>

</a>



<a
    href="my-trips.php"
    class="stat-card clickable-stat"
>

    <strong>
        <?= (int)$totalFavorites ?>
    </strong>

    <span>
        Favorite Places
    </span>

</a>



<a
    href="expenses.php"
    class="stat-card clickable-stat"
>

    <strong>

        ₹<?= number_format(
            (float)$totalExpense,
            0
        ) ?>

    </strong>

    <span>
        Total Expenses
    </span>

</a>


</section>



<!-- ==============================
     BUDGET
============================== -->

<section class="budget-summary">


<div>

    <span class="label">
        TRAVEL FINANCES
    </span>

    <h2>
        Your Travel Budget
    </h2>

    <p>
        Track your overall trip budget
        and spending.
    </p>


    <a
        href="expenses.php"
        class="budget-btn"
    >

        View Expenses →

    </a>

</div>



<div class="budget-values">


<div class="budget-item">

    <small>
        Total Budget
    </small>

    <strong>

        ₹<?= number_format(
            (float)$totalBudget,
            0
        ) ?>

    </strong>

</div>



<div class="budget-item">

    <small>
        Total Spent
    </small>

    <strong>

        ₹<?= number_format(
            (float)$totalExpense,
            0
        ) ?>

    </strong>

</div>



<div class="budget-item">

    <small>
        Remaining
    </small>

    <strong>

        ₹<?= number_format(
            $remainingBudget,
            0
        ) ?>

    </strong>

</div>


</div>


</section>



<!-- ==============================
     PERSONAL INFORMATION
============================== -->

<section class="profile-card">


<div class="card-header">


<div>

    <span class="label">

        PERSONAL DETAILS

    </span>


    <h2>

        Profile Information

    </h2>

</div>


<a
    href="edit-profile.php"
    class="edit-btn"
>

    ✎ Edit Profile

</a>


</div>



<div class="info-grid">


<div class="info-item">

    <label>
        Full Name
    </label>

    <span>

        <?= htmlspecialchars(
            $user['name']
        ) ?>

    </span>

</div>



<div class="info-item">

    <label>
        Email
    </label>

    <span>

        <?= htmlspecialchars(
            $user['email']
        ) ?>

    </span>

</div>



<div class="info-item">

    <label>
        Age
    </label>

    <span>

        <?= !empty($user['age'])
            ? htmlspecialchars(
                $user['age']
            )
            : "Not provided"
        ?>

    </span>

</div>



<div class="info-item">

    <label>
        Role
    </label>

    <span>

        <?= ucfirst(
            htmlspecialchars(
                $user['role']
            )
        ) ?>

    </span>

</div>



<div class="info-item">

    <label>
        City
    </label>

    <span>

        <?= !empty($user['city'])
            ? htmlspecialchars(
                $user['city']
            )
            : "Not provided"
        ?>

    </span>

</div>



<div class="info-item">

    <label>
        State
    </label>

    <span>

        <?= !empty($user['state'])
            ? htmlspecialchars(
                $user['state']
            )
            : "Not provided"
        ?>

    </span>

</div>



<div class="info-item full">

    <label>
        Address
    </label>

    <span>

        <?= !empty($user['address'])
            ? htmlspecialchars(
                $user['address']
            )
            : "Not provided"
        ?>

    </span>

</div>


</div>



<!-- ==============================
     QUICK LINKS
============================== -->

<div class="quick-links">


<a
    href="my-trips.php"
    class="quick-link"
>

    <strong>
        ✈ My Trips
    </strong>

    <span>
        View upcoming and previous journeys.
    </span>

</a>



<a
    href="cities.php"
    class="quick-link"
>

    <strong>
        ♡ Favorite Places
    </strong>

    <span>
        Explore and save travel destinations.
    </span>

</a>



<a
    href="expenses.php"
    class="quick-link"
>

    <strong>
        ₹ Expenses
    </strong>

    <span>
        View trip budgets and spending.
    </span>

</a>


</div>



<!-- ==============================
     ACCOUNT
============================== -->

<div class="account">


<div>

    <strong>

        Account Status:

        <?= ucfirst(
            htmlspecialchars(
                $user['status']
            )
        ) ?>

    </strong>


    <small>

        Member since

        <?= date(
            "d M Y",
            strtotime(
                $user['created_at']
            )
        ) ?>

    </small>

</div>


<a
    href="logout.php"
    class="logout"
>

    Logout

</a>


</div>


</section>


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