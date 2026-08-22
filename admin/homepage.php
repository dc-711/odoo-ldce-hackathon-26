<?php
session_start();

$upcomingTrips = [
    ["name"=>"European Adventure","dates"=>"10 Sep – 20 Sep 2026","cities"=>3,"budget"=>"₹50,000","image"=>"https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=900&q=80"],
    ["name"=>"Goa Escape","dates"=>"05 Oct – 10 Oct 2026","cities"=>2,"budget"=>"₹25,000","image"=>"https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=900&q=80"]
];
$destinations = [
    ["name"=>"Paris","country"=>"France","cost"=>"High","image"=>"https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=700&q=80"],
    ["name"=>"Goa","country"=>"India","cost"=>"Medium","image"=>"https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=700&q=80"],
    ["name"=>"Dubai","country"=>"UAE","cost"=>"High","image"=>"https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=700&q=80"],
    ["name"=>"Tokyo","country"=>"Japan","cost"=>"High","image"=>"https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?auto=format&fit=crop&w=700&q=80"]
];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>GlobeTrotter | Home</title>
<link rel="stylesheet" href="assets/css/home.css">
</head>
<body>
<header class="topbar">
<a class="brand" href="homepage.php"><span class="mark">✈</span><span>Globe<span>Trotter</span></span></a>
<nav><a class="active" href="homepage.php">Home</a><a href="my-trips.php">My Trips</a><a href="cities.php">Explore</a><a href="activities.php">Activities</a></nav>
<div class="actions">

   
    <a href="profile.php" class="profile-link">

        <div class="profile">


            <div>
                <b>Traveler</b>
                <small>My Profile</small>
            </div>

        </div>

    </a>

</div>
</header>

<main>
<section class="hero">
<div>
<span class="eyebrow">YOUR JOURNEY, YOUR WAY</span>
<h1>Plan your next<br><em>great adventure.</em></h1>
<p>Create personalized multi-city trips, discover amazing destinations, organize activities, and keep your entire journey within budget.</p>
<div class="hero-actions"><a class="btn primary" href="create-trip.php">＋ Plan New Trip</a><a class="btn light" href="cities.php">Explore Destinations</a></div>
<div class="stats"><div><b>12</b><small>Trips planned</small></div><div><b>28</b><small>Cities explored</small></div><div><b>₹1.8L</b><small>Travel budget</small></div></div>
</div>
<div class="hero-photo"><div class="float top">📍 <span><small>Next destination</small><b>Paris, France</b></span></div><div class="float bottom">✓ <span><b>Trip ready!</b><small>8 activities planned</small></span></div></div>
</section>

<section class="section">
<div class="heading"><div><span class="label">YOUR TRAVEL PLANS</span><h2>Upcoming trips</h2></div><a href="my-trips.php">View all trips →</a></div>
<div class="trip-grid">
<?php foreach($upcomingTrips as $trip): ?>
<article class="trip-card"><div class="trip-img" style="background-image:url('<?=htmlspecialchars($trip['image'])?>')"><span>Upcoming</span><button>•••</button></div><div class="trip-body"><small><?=htmlspecialchars($trip['dates'])?></small><h3><?=htmlspecialchars($trip['name'])?></h3><p>📍 <?=$trip['cities']?> cities &nbsp; • &nbsp; 💰 <?=htmlspecialchars($trip['budget'])?></p><a href="trip-details.php">View itinerary →</a></div></article>
<?php endforeach; ?>
<a class="new-trip" href="create-trip.php"><strong>＋</strong><h3>Plan a new trip</h3><p>Start building your next adventure</p></a>
</div>
</section>

<section class="section explore">
<div class="heading"><div><span class="label">GET INSPIRED</span><h2>Popular destinations</h2></div><a href="cities.php">Explore all →</a></div>
<div class="dest-grid">
<?php foreach($destinations as $d): ?>
<article class="dest"><div class="dest-img" style="background-image:url('<?=htmlspecialchars($d['image'])?>')"><button class="heart">♡</button></div><div class="dest-body"><div><h3><?=htmlspecialchars($d['name'])?></h3><p><?=htmlspecialchars($d['country'])?></p></div><span><?=$d['cost']?></span></div></article>
<?php endforeach; ?>
</div>
</section>

<section class="banner"><div><span class="label">PLAN SMARTER</span><h2>Your whole trip in one place.</h2><p>Build day-by-day itineraries, track expenses and visualize your journey.</p></div><a class="btn light" href="create-trip.php">Start Planning →</a></section>
</main>
<footer><b>✈ GlobeTrotter</b><span>Empowering personalized travel planning.</span><span>© <?=date('Y')?> GlobeTrotter</span></footer>
<script src="assets/js/home.js"></script>
</body></html>
