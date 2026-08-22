<?php
session_start();
include("db.php");
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) { header("Location: login.php"); exit; }

$user_id=(int)$_SESSION['user_id'];
$name=$_SESSION['name']??'Traveler';

function fetchTrips($conn,$user_id,$statuses){
    $list=[];
    $sql="
      SELECT t.id,t.name,t.description,t.start_date,t.end_date,t.destination,t.budget,t.currency,t.status,t.cover_photo,
             COUNT(s.id) total_cities
      FROM trips t LEFT JOIN stops s ON s.trip_id=t.id
      WHERE t.user_id=? AND t.status IN ($statuses)
      GROUP BY t.id ORDER BY t.start_date ASC";
    $stmt=$conn->prepare($sql); $stmt->bind_param("i",$user_id); $stmt->execute();
    $r=$stmt->get_result(); while($row=$r->fetch_assoc())$list[]=$row; $stmt->close(); return $list;
}
$upcomingTrips=fetchTrips($conn,$user_id,"'draft','planned'");
$historyTrips=fetchTrips($conn,$user_id,"'completed','cancelled'");

$favorites=[];
$stmt=$conn->prepare("
 SELECT f.id favorite_id,c.id city_id,c.city_name,c.country,c.region,c.description,c.image,c.cost_index,c.popularity
 FROM favorites f INNER JOIN cities c ON c.id=f.city_id
 WHERE f.user_id=? AND f.city_id IS NOT NULL
 ORDER BY f.created_at DESC");
$stmt->bind_param("i",$user_id);$stmt->execute();$r=$stmt->get_result();while($row=$r->fetch_assoc())$favorites[]=$row;$stmt->close();

$parts=preg_split('/\s+/',trim($name));$initials='';foreach($parts as $p){if($p!=='')$initials.=strtoupper(substr($p,0,1));if(strlen($initials)>=2)break;}
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>GlobeTrotter | My Trips</title><link rel="stylesheet" href="assets/css/home.css">
<style>
.page{padding-top:25px}.hero2{width:min(1200px,92%);margin:0 auto 45px;min-height:300px;border-radius:28px;padding:55px;display:flex;align-items:center;color:#fff;background:linear-gradient(90deg,rgba(25,25,25,.84),rgba(25,25,25,.4)),url('assets/img/beautiful-landscape-view-covered-with-greenery-mountains-background.jpg') center/cover;box-shadow:0 16px 40px rgba(0,0,0,.12)}
.hero2 h1{font-size:48px;margin:10px 0}.hero2 p{max-width:620px;color:#e5e5e5;line-height:1.7}.hero2 .btn{margin-top:10px}
.summary{width:min(1100px,90%);margin:-70px auto 45px;position:relative;z-index:4;display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.sum{background:#fff;border-radius:16px;padding:22px;box-shadow:0 8px 25px rgba(0,0,0,.09)}.sum b{font-size:27px;display:block}.sum span{color:#888;font-size:13px}
.wrap{width:min(1200px,92%);margin:auto}.tabs{display:flex;gap:8px;background:#fff;padding:7px;border-radius:13px;width:max-content;margin-bottom:30px}.tab{border:0;background:transparent;padding:11px 18px;border-radius:9px;font-weight:700;color:#777;cursor:pointer}.tab.active{background:#333;color:#fff}.panel{display:none}.panel.active{display:block}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}.card{background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 7px 22px rgba(0,0,0,.07)}.img{height:210px;background:center/cover;position:relative}.fallback{background:linear-gradient(135deg,#444,#999)}.badge{position:absolute;top:14px;left:14px;background:#fff;padding:7px 11px;border-radius:20px;font-size:12px;font-weight:700}.body{padding:20px}.body h3{margin:7px 0}.muted{color:#777;font-size:14px;line-height:1.6}.meta{border-top:1px solid #eee;padding-top:14px;margin-top:14px;display:flex;justify-content:space-between;color:#666;font-size:13px}.view{display:block;text-align:center;margin-top:16px;background:#333;color:#fff;text-decoration:none;padding:11px;border-radius:9px;font-weight:700}
.favgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:22px}.empty{background:#fff;padding:50px;border-radius:18px;text-align:center;color:#777}.section-title{margin:0 0 20px}.section-title h2{margin:5px 0}
@media(max-width:900px){.grid{grid-template-columns:repeat(2,1fr)}.favgrid{grid-template-columns:repeat(2,1fr)}}@media(max-width:650px){.hero2{padding:35px 24px}.hero2 h1{font-size:36px}.summary,.grid,.favgrid{grid-template-columns:1fr}.tabs{max-width:100%;overflow:auto}}
</style></head><body>
<header class="topbar">

    <a class="brand" href="homepage.php">
        <span class="mark">✈</span>
        <span>Globe<span>Trotter</span></span>
    </a>

    <nav>
        <a href="homepage.php">Home</a>
        <a class="active" href="my-trips.php">My Trips</a>
        <a href="cities.php">Explore</a>
        <a href="activities.php">Activities</a>
    </nav>

    <div class="actions">

        <a href="profile.php" class="profile-link">

            <div class="profile">

                <div class="avatar">
                    <?= htmlspecialchars($initials) ?>
                </div>

                <div>
                    <b><?= htmlspecialchars($name) ?></b>
                    <small>My Profile</small>
                </div>

            </div>

        </a>

    </div>

</header><main class="page">
<section class="hero2"><div><span class="eyebrow">YOUR TRAVEL COLLECTION</span><h1>Every journey,<br>all in one place.</h1><p>Manage upcoming adventures, revisit old journeys and keep your favorite destinations ready for the next plan.</p><a class="btn light" href="create-trip.php">＋ Plan New Trip</a></div></section>
<section class="summary"><div class="sum"><b><?=count($upcomingTrips)?></b><span>Upcoming Trips</span></div><div class="sum"><b><?=count($historyTrips)?></b><span>Trip History</span></div><div class="sum"><b><?=count($favorites)?></b><span>Favorite Places</span></div></section>
<div class="wrap">
<div class="tabs"><button class="tab active" onclick="showTab('upcoming',this)">Upcoming Trips</button><button class="tab" onclick="showTab('history',this)">History</button><button class="tab" onclick="showTab('favorites',this)">♡ Favorite Places</button></div>

<section id="upcoming" class="panel active"><div class="section-title"><span class="label">YOUR TRAVEL PLANS</span><h2>Upcoming trips</h2></div>
<?php if($upcomingTrips): ?><div class="grid"><?php foreach($upcomingTrips as $t): ?><article class="card"><div class="img <?=empty($t['cover_photo'])?'fallback':''?>" <?php if($t['cover_photo']):?>style="background-image:url('<?=htmlspecialchars($t['cover_photo'])?>')"<?php endif;?>><span class="badge"><?=htmlspecialchars($t['status'])?></span></div><div class="body"><small><?=date('d M Y',strtotime($t['start_date']))?> – <?=date('d M Y',strtotime($t['end_date']))?></small><h3><?=htmlspecialchars($t['name'])?></h3><p class="muted"><?=htmlspecialchars($t['description'] ?: $t['destination'] ?: 'Your upcoming travel adventure.')?></p><div class="meta"><span>📍 <?=$t['total_cities']?> stops</span><span>💰 <?=htmlspecialchars($t['currency'])?> <?=number_format((float)$t['budget'],0)?></span></div><a class="view" href="trip-details.php?id=<?=$t['id']?>">View Itinerary →</a></div></article><?php endforeach;?></div><?php else:?><div class="empty"><h3>No upcoming trips</h3><p>Start planning your next adventure.</p><a class="btn primary" href="create-trip.php">Create Trip</a></div><?php endif;?></section>

<section id="history" class="panel"><div class="section-title"><span class="label">PAST JOURNEYS</span><h2>Trip history</h2></div>
<?php if($historyTrips): ?><div class="grid"><?php foreach($historyTrips as $t): ?><article class="card"><div class="img <?=empty($t['cover_photo'])?'fallback':''?>" <?php if($t['cover_photo']):?>style="background-image:url('<?=htmlspecialchars($t['cover_photo'])?>')"<?php endif;?>><span class="badge"><?=htmlspecialchars($t['status'])?></span></div><div class="body"><small><?=date('d M Y',strtotime($t['start_date']))?> – <?=date('d M Y',strtotime($t['end_date']))?></small><h3><?=htmlspecialchars($t['name'])?></h3><p class="muted"><?=htmlspecialchars($t['description'] ?: 'Previous journey.')?></p><div class="meta"><span>📍 <?=$t['total_cities']?> stops</span><span>💰 <?=htmlspecialchars($t['currency'])?> <?=number_format((float)$t['budget'],0)?></span></div><a class="view" href="trip-details.php?id=<?=$t['id']?>">View Trip →</a></div></article><?php endforeach;?></div><?php else:?><div class="empty"><h3>No trip history yet</h3><p>Completed or cancelled trips will appear here.</p></div><?php endif;?></section>

<section id="favorites" class="panel"><div class="section-title"><span class="label">SAVED DESTINATIONS</span><h2>Favorite places</h2></div>
<?php if($favorites): ?><div class="favgrid"><?php foreach($favorites as $c): ?><article class="card"><div class="img <?=empty($c['image'])?'fallback':''?>" <?php if($c['image']):?>style="background-image:url('<?=htmlspecialchars($c['image'])?>')"<?php endif;?>><a class="badge" href="toggle-favorite.php?city_id=<?=$c['city_id']?>">♥ Remove</a></div><div class="body"><h3><?=htmlspecialchars($c['city_name'])?></h3><p class="muted">📍 <?=htmlspecialchars($c['region'])?>, <?=htmlspecialchars($c['country'])?></p><p class="muted"><?=htmlspecialchars($c['description'] ?: 'Saved destination.')?></p><div class="meta"><span>Cost <?=htmlspecialchars($c['cost_index'])?></span><span>⭐ <?=htmlspecialchars($c['popularity'])?></span></div><a class="view" href="cities.php?state=<?=urlencode($c['region'])?>&city=<?=$c['city_id']?>">Explore City →</a></div></article><?php endforeach;?></div><?php else:?><div class="empty"><h3>No favorite places yet</h3><p>Save cities from Explore.</p><a class="btn primary" href="cities.php">Explore Destinations</a></div><?php endif;?></section>

<section class="banner" style="margin-top:60px"><div><span class="label">READY FOR MORE?</span><h2>Your next adventure is waiting.</h2><p>Discover destinations and build a new itinerary.</p></div><a class="btn light" href="create-trip.php">Start Planning →</a></section>
</div></main>
<footer><b>✈ GlobeTrotter</b><span>Empowering personalized travel planning.</span><span>© <?=date('Y')?> GlobeTrotter</span></footer>
<script>function showTab(id,btn){document.querySelectorAll('.panel').forEach(x=>x.classList.remove('active'));document.querySelectorAll('.tab').forEach(x=>x.classList.remove('active'));document.getElementById(id).classList.add('active');btn.classList.add('active')}</script>
</body></html>
