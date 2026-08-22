<?php
session_start();
include("db.php");
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) { header("Location: login.php"); exit; }

$name=$_SESSION['name']??'Traveler';
$selectedCity=trim($_GET['city']??'');
$selectedType=trim($_GET['type']??'');
$maxCost=($_GET['max_cost']??'')!==''?(float)$_GET['max_cost']:null;
$minRating=($_GET['rating']??'')!==''?(float)$_GET['rating']:null;

$cityNames=[];$r=$conn->query("SELECT DISTINCT city FROM stops WHERE city<>'' ORDER BY city");while($row=$r->fetch_assoc())$cityNames[]=$row['city'];
$types=[];$r=$conn->query("SELECT DISTINCT activity_type FROM activities WHERE activity_type IS NOT NULL AND activity_type<>'' ORDER BY activity_type");while($row=$r->fetch_assoc())$types[]=$row['activity_type'];

$sql="
 SELECT a.id,a.stop_id,a.name,a.activity_type,a.estimated_cost,a.start_time,a.notes,a.location,a.duration_minutes,a.rating,a.image,
        s.city,s.country,s.trip_id,t.name trip_name,t.user_id
 FROM activities a
 INNER JOIN stops s ON s.id=a.stop_id
 INNER JOIN trips t ON t.id=s.trip_id
 WHERE 1=1";
$params=[];$bindTypes='';
if($selectedCity!==''){$sql.=" AND s.city=?";$params[]=$selectedCity;$bindTypes.='s';}
if($selectedType!==''){$sql.=" AND a.activity_type=?";$params[]=$selectedType;$bindTypes.='s';}
if($maxCost!==null){$sql.=" AND a.estimated_cost<=?";$params[]=$maxCost;$bindTypes.='d';}
if($minRating!==null){$sql.=" AND a.rating>=?";$params[]=$minRating;$bindTypes.='d';}
$sql.=" ORDER BY a.rating DESC,a.name";
$stmt=$conn->prepare($sql);if($params)$stmt->bind_param($bindTypes,...$params);$stmt->execute();$r=$stmt->get_result();$activities=[];while($row=$r->fetch_assoc())$activities[]=$row;$stmt->close();

$parts=preg_split('/\s+/',trim($name));$initials='';foreach($parts as $p){if($p!=='')$initials.=strtoupper(substr($p,0,1));if(strlen($initials)>=2)break;}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>GlobeTrotter | Activities</title><link rel="stylesheet" href="assets/css/home.css">
<style>
.page{padding-top:25px}.hero2{width:min(1200px,92%);margin:0 auto 45px;min-height:320px;border-radius:28px;padding:55px;color:#fff;display:flex;align-items:center;background:linear-gradient(90deg,rgba(25,25,25,.84),rgba(25,25,25,.38)),url('assets/img/beautiful-landscape-view-covered-with-greenery-mountains-background.jpg') center/cover}.hero2 h1{font-size:48px;margin:10px 0}.hero2 p{max-width:620px;line-height:1.7;color:#e7e7e7}
.filter{width:min(1100px,90%);margin:-70px auto 50px;background:#fff;border-radius:18px;padding:24px;position:relative;z-index:5;box-shadow:0 10px 30px rgba(0,0,0,.1)}.fg{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.field label{display:block;font-size:13px;font-weight:700;margin-bottom:7px}.field select,.field input{width:100%;padding:12px;border:1px solid #ddd;border-radius:9px}.actions2{margin-top:14px;display:flex;gap:10px}.search,.reset{padding:12px 20px;border-radius:9px;font-weight:700;text-decoration:none}.search{border:0;background:#333;color:#fff}.reset{background:#eee;color:#444}
.wrap{width:min(1200px,92%);margin:auto}.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}.card{background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 7px 22px rgba(0,0,0,.07)}.img{height:220px;background:center/cover;position:relative}.fallback{background:linear-gradient(135deg,#444,#999)}.badge{position:absolute;top:14px;background:#fff;border-radius:20px;padding:7px 11px;font-size:12px;font-weight:700}.badge.left{left:14px}.badge.right{right:14px}.body{padding:20px}.body h3{margin:5px 0}.muted{color:#777;font-size:14px;line-height:1.6}.meta{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-top:15px}.meta div{background:#f7f7f7;border-radius:9px;padding:10px;font-size:13px}.empty{background:#fff;border-radius:18px;padding:50px;text-align:center;color:#777}
@media(max-width:950px){.fg{grid-template-columns:repeat(2,1fr)}.grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:650px){.hero2{padding:35px 24px}.hero2 h1{font-size:36px}.fg,.grid{grid-template-columns:1fr}}
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
<section class="hero2"><div><span class="eyebrow">DISCOVER EXPERIENCES</span><h1>Find activities for<br>every adventure.</h1><p>Browse activities already attached to trip stops in your current database, and filter by city, type, cost or rating.</p></div></section>
<section class="filter"><form method="GET"><div class="fg"><div class="field"><label>City</label><select name="city"><option value="">All Cities</option><?php foreach($cityNames as $c):?><option value="<?=htmlspecialchars($c)?>" <?=$selectedCity===$c?'selected':''?>><?=htmlspecialchars($c)?></option><?php endforeach;?></select></div><div class="field"><label>Activity Type</label><select name="type"><option value="">All Types</option><?php foreach($types as $t):?><option value="<?=htmlspecialchars($t)?>" <?=$selectedType===$t?'selected':''?>><?=htmlspecialchars($t)?></option><?php endforeach;?></select></div><div class="field"><label>Maximum Cost</label><input type="number" name="max_cost" min="0" value="<?=htmlspecialchars($_GET['max_cost']??'')?>"></div><div class="field"><label>Minimum Rating</label><select name="rating"><option value="">Any Rating</option><option value="4" <?=$minRating==4?'selected':''?>>4.0+</option><option value="4.5" <?=$minRating==4.5?'selected':''?>>4.5+</option></select></div></div><div class="actions2"><button class="search">Search Activities</button><a class="reset" href="activities.php">Reset</a></div></form></section>
<div class="wrap"><div class="heading"><div><span class="label">THINGS TO DO</span><h2>Explore activities</h2></div><span><?=count($activities)?> found</span></div>
<?php if($activities):?><div class="grid"><?php foreach($activities as $a):?><article class="card"><div class="img <?=empty($a['image'])?'fallback':''?>" <?php if($a['image']):?>style="background-image:url('<?=htmlspecialchars($a['image'])?>')"<?php endif;?>><span class="badge left"><?=htmlspecialchars($a['activity_type'] ?: 'Activity')?></span><span class="badge right">⭐ <?=htmlspecialchars($a['rating'])?></span></div><div class="body"><small><?=htmlspecialchars($a['city'])?> • <?=htmlspecialchars($a['trip_name'])?></small><h3><?=htmlspecialchars($a['name'])?></h3><p class="muted">📍 <?=htmlspecialchars($a['location'] ?: $a['city'])?></p><p class="muted"><?=htmlspecialchars($a['notes'] ?: 'Activity added to a GlobeTrotter itinerary.')?></p><div class="meta"><div>💰 Cost<br><b>₹<?=number_format((float)$a['estimated_cost'],0)?></b></div><div>⏱ Duration<br><b><?= (int)$a['duration_minutes']?> min</b></div></div></div></article><?php endforeach;?></div><?php else:?><div class="empty"><h3>No activities found</h3><p>Add activities to a trip stop or change the filters.</p></div><?php endif;?>
<section class="banner" style="margin-top:60px"><div><span class="label">BUILD YOUR ITINERARY</span><h2>Ready to plan?</h2><p>Create a trip, add stops and then add activities to those stops.</p></div><a class="btn light" href="create-trip.php">Plan Your Trip →</a></section></div>
</main><footer><b>✈ GlobeTrotter</b><span>Empowering personalized travel planning.</span><span>© <?=date('Y')?> GlobeTrotter</span></footer></body></html>
