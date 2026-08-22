<?php
session_start();
include("db.php");

// Protect page
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$error = "";
$success = "";


// ========================================
// GET CURRENT USER DATA
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
        status
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
// UPDATE PROFILE
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? '');
    $age = ($_POST['age'] ?? '') !== ''
        ? (int) $_POST['age']
        : null;

    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');


    if (strlen($name) < 2) {

        $error = "Please enter a valid name.";

    } elseif ($age !== null && ($age < 1 || $age > 120)) {

        $error = "Please enter a valid age.";

    } else {

        $stmt = $conn->prepare("
            UPDATE users
            SET
                name = ?,
                age = ?,
                address = ?,
                city = ?,
                state = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "sisssi",
            $name,
            $age,
            $address,
            $city,
            $state,
            $user_id
        );

        if ($stmt->execute()) {

            // Update session name too
            $_SESSION['name'] = $name;

            $success = "Profile updated successfully.";

            // Update local array so page immediately shows new values
            $user['name'] = $name;
            $user['age'] = $age;
            $user['address'] = $address;
            $user['city'] = $city;
            $user['state'] = $state;

        } else {

            $error = "Unable to update profile. Please try again.";
        }

        $stmt->close();
    }
}


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
    Edit Profile | GlobeTrotter
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
   PAGE
============================== */

.edit-page {

    width: min(950px, 92%);

    margin: 45px auto 70px;
}


/* ==============================
   HERO
============================== */

.edit-hero {

    min-height: 230px;

    border-radius: 24px;

    padding: 45px;

    display: flex;

    align-items: center;

    gap: 25px;

    color: white;

    background:

        linear-gradient(
            90deg,
            rgba(30,30,30,.88),
            rgba(30,30,30,.45)
        ),

        url(
        'assets/img/beautiful-landscape-view-covered-with-greenery-mountains-background.jpg'
        )
        center / cover;

    margin-bottom: 30px;
}


.edit-avatar {

    width: 95px;

    height: 95px;

    min-width: 95px;

    border-radius: 50%;

    background: white;

    color: #333;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 32px;

    font-weight: 700;

    border: 4px solid rgba(255,255,255,.4);
}


.edit-hero h1 {

    margin: 5px 0 7px;

    font-size: 34px;
}


.edit-hero p {

    margin: 0;

    color: #ddd;
}


/* ==============================
   FORM CARD
============================== */

.edit-card {

    background: white;

    border-radius: 18px;

    padding: 32px;

    box-shadow:
        0 7px 25px rgba(0,0,0,.07);
}


.card-header {

    margin-bottom: 25px;

    padding-bottom: 20px;

    border-bottom: 1px solid #eee;
}


.card-header h2 {

    margin: 5px 0;
}


.card-header p {

    margin: 0;

    color: #888;

    font-size: 14px;
}


/* ==============================
   FORM
============================== */

.form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 18px;
}


.form-group {

    margin-bottom: 5px;
}


.form-group.full {

    grid-column: 1 / -1;
}


.form-group label {

    display: block;

    margin-bottom: 7px;

    font-size: 14px;

    font-weight: 700;

    color: #444;
}


.form-group input,
.form-group textarea {

    width: 100%;

    padding: 12px 13px;

    border: 1px solid #ddd;

    border-radius: 9px;

    background: #fafafa;

    font-family: inherit;

    font-size: 14px;

    transition: .25s;
}


.form-group input:focus,
.form-group textarea:focus {

    outline: none;

    border-color: #555;

    background: white;

    box-shadow:
        0 0 6px rgba(0,0,0,.12);
}


.form-group textarea {

    min-height: 100px;

    resize: vertical;
}


/* Email disabled */

.readonly-field {

    background: #eeeeee !important;

    cursor: not-allowed;
}


/* ==============================
   MESSAGE
============================== */

.error {

    background: #fdecea;

    color: #d93025;

    padding: 12px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-size: 14px;
}


.success {

    background: #edf7ed;

    color: #2e7d32;

    padding: 12px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-size: 14px;
}


/* ==============================
   BUTTONS
============================== */

.form-actions {

    margin-top: 28px;

    padding-top: 22px;

    border-top: 1px solid #eee;

    display: flex;

    justify-content: flex-end;

    gap: 12px;
}


.cancel-btn,
.save-btn {

    text-decoration: none;

    padding: 11px 20px;

    border-radius: 9px;

    font-size: 14px;

    font-weight: 700;
}


.cancel-btn {

    background: #eeeeee;

    color: #444;
}


.cancel-btn:hover {

    background: #dddddd;
}


.save-btn {

    border: none;

    background: #444;

    color: white;

    cursor: pointer;
}


.save-btn:hover {

    background: #222;
}


/* ==============================
   MOBILE
============================== */

@media(max-width: 700px) {

    .edit-hero {

        flex-direction: column;

        text-align: center;

        padding: 35px 20px;
    }


    .form-grid {

        grid-template-columns: 1fr;
    }


    .form-group.full {

        grid-column: auto;
    }


    .form-actions {

        flex-direction: column;
    }


    .cancel-btn,
    .save-btn {

        width: 100%;

        text-align: center;
    }

}

</style>

</head>


<body>


<!-- ==============================
     HEADER
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
        href="profile.php"
        class="btn light"
    >
        ← Back to Profile
    </a>

</div>


</header>



<main class="edit-page">


<!-- ==============================
     HERO
============================== -->

<section class="edit-hero">


<div class="edit-avatar">

    <?= htmlspecialchars(
        $initials
    ) ?>

</div>


<div>

    <span class="eyebrow">

        ACCOUNT SETTINGS

    </span>


    <h1>
        Edit Profile
    </h1>


    <p>

        Update your personal information
        and travel profile.

    </p>

</div>


</section>



<!-- ==============================
     FORM CARD
============================== -->

<section class="edit-card">


<div class="card-header">

    <span class="label">

        PERSONAL INFORMATION

    </span>


    <h2>
        Your Details
    </h2>


    <p>

        Keep your profile information
        accurate and up to date.

    </p>

</div>



<?php if (!empty($error)): ?>

<div class="error">

    <?= htmlspecialchars(
        $error
    ) ?>

</div>

<?php endif; ?>



<?php if (!empty($success)): ?>

<div class="success">

    <?= htmlspecialchars(
        $success
    ) ?>

</div>

<?php endif; ?>



<form method="POST">


<div class="form-grid">


<!-- NAME -->

<div class="form-group">

    <label for="name">

        Full Name

    </label>


    <input
        type="text"
        id="name"
        name="name"
        value="<?= htmlspecialchars(
            $user['name']
        ) ?>"
        required
    >

</div>



<!-- EMAIL -->

<div class="form-group">

    <label>

        Email Address

    </label>


    <input
        type="email"
        value="<?= htmlspecialchars(
            $user['email']
        ) ?>"
        class="readonly-field"
        readonly
    >

</div>



<!-- AGE -->

<div class="form-group">

    <label for="age">

        Age

    </label>


    <input
        type="number"
        id="age"
        name="age"
        min="1"
        max="120"
        value="<?= htmlspecialchars(
            $user['age'] ?? ''
        ) ?>"
    >

</div>



<!-- CITY -->

<div class="form-group">

    <label for="city">

        City

    </label>


    <input
        type="text"
        id="city"
        name="city"
        value="<?= htmlspecialchars(
            $user['city'] ?? ''
        ) ?>"
        placeholder="Ahmedabad"
    >

</div>



<!-- STATE -->

<div class="form-group full">

    <label for="state">

        State

    </label>


    <input
        type="text"
        id="state"
        name="state"
        value="<?= htmlspecialchars(
            $user['state'] ?? ''
        ) ?>"
        placeholder="Gujarat"
    >

</div>



<!-- ADDRESS -->

<div class="form-group full">

    <label for="address">

        Address

    </label>


    <textarea
        id="address"
        name="address"
        placeholder="Enter your address"
    ><?= htmlspecialchars(
        $user['address'] ?? ''
    ) ?></textarea>

</div>


</div>



<!-- BUTTONS -->

<div class="form-actions">


<a
    href="profile.php"
    class="cancel-btn"
>

    Cancel

</a>


<button
    type="submit"
    class="save-btn"
>

    Save Changes

</button>


</div>


</form>


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