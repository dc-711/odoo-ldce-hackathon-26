<?php
session_start();
include("db.php");
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) { header("Location: login.php"); exit; }

$user_id=(int)$_SESSION['user_id'];
$name=$_SESSION['name']??'Traveler';
$error='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $tripName=trim($_POST['name']??'');
    $start=$_POST['start_date']??'';
    $end=$_POST['end_date']??'';
    $destination=trim($_POST['destination']??'');
    $description=trim($_POST['description']??'');
    $budget=(float)($_POST['budget']??0);
    $currency=trim($_POST['currency']??'INR');
    $visibility=in_array($_POST['visibility']??'private',['private','public'],true)?$_POST['visibility']:'private';

    if($tripName===''||$start===''||$end==='') $error='Trip name and dates are required.';
    elseif($end<$start) $error='End date cannot be before start date.';
    else{
        $stmt=$conn->prepare("INSERT INTO trips (user_id,name,start_date,end_date,destination,description,budget,currency,status,visibility) VALUES (?,?,?,?,?,?,?,?, 'planned', ?)");
        $stmt->bind_param("isssssdss",$user_id,$tripName,$start,$end,$destination,$description,$budget,$currency,$visibility);
        if($stmt->execute()){ $id=$stmt->insert_id; $stmt->close(); header("Location: trip-details.php?id=".$id); exit; }
        $error='Could not create trip.';
        $stmt->close();
    }
}
$parts=preg_split('/\s+/',trim($name));$initials='';foreach($parts as $p){if($p!=='')$initials.=strtoupper(substr($p,0,1));if(strlen($initials)>=2)break;}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create Trip | GlobeTrotter</title><link rel="stylesheet" href="assets/css/home.css">
<style>.wrap{width:min(850px,92%);margin:50px auto}.box{background:#fff;border-radius:20px;padding:34px;box-shadow:0 8px 28px rgba(0,0,0,.08)}.box h1{margin-top:0}.row{display:grid;grid-template-columns:1fr 1fr;gap:15px}.field{margin-bottom:16px}.field label{display:block;font-weight:700;font-size:13px;margin-bottom:7px}.field input,.field textarea,.field select{width:100%;padding:12px;border:1px solid #ddd;border-radius:9px;font:inherit}.field textarea{min-height:100px}.error{background:#fdecea;color:#d93025;padding:10px;border-radius:8px;margin-bottom:15px}@media(max-width:650px){.row{grid-template-columns:1fr}}</style></head><body>
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

</header><main class="wrap"><div class="box"><span class="label">START A JOURNEY</span><h1>Plan a new trip</h1><p>Create the trip first. You can add stops, activities and expenses on the trip details page.</p><?php if($error):?><div class="error"><?=htmlspecialchars($error)?></div><?php endif;?><form method="POST"><div class="field"><label>Trip Name</label><input name="name" required></div><div class="row"><div class="field"><label>Start Date</label><input type="date" name="start_date" required></div><div class="field"><label>End Date</label><input type="date" name="end_date" required></div></div><div class="field"><label>Main Destination</label><input name="destination" placeholder="Jaipur, Rajasthan"></div><div class="field"><label>Description</label><textarea name="description"></textarea></div><div class="row"><div class="field"><label>Budget</label><input type="number" step="0.01" min="0" name="budget" value="0"></div><div class="field"><label>Currency</label><input name="currency" value="INR"></div></div><div class="field"><label>Visibility</label><select name="visibility"><option value="private">Private</option><option value="public">Public</option></select></div><button class="btn primary" type="submit">Create Trip</button></form></div></main></body></html>
