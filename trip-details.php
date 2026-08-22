<?php
session_start();
include("db.php");
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) { header("Location: login.php"); exit; }

$user_id=(int)$_SESSION['user_id'];
$trip_id=(int)($_GET['id']??0);
$name=$_SESSION['name']??'Traveler';
$error='';

$stmt=$conn->prepare("SELECT * FROM trips WHERE id=? AND (user_id=? OR id IN (SELECT trip_id FROM trip_collaborators WHERE user_id=? AND status='accepted'))");
$stmt->bind_param("iii",$trip_id,$user_id,$user_id);$stmt->execute();$trip=$stmt->get_result()->fetch_assoc();$stmt->close();
if(!$trip){http_response_code(404);die("Trip not found or access denied.");}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action']??'';
    if($action==='add_stop'){
        $city=trim($_POST['city']??'');$country=trim($_POST['country']??'');$arrival=$_POST['arrival_date']?:null;$departure=$_POST['departure_date']?:null;$position=(int)($_POST['position']??0);
        if($city!==''){
            $stmt=$conn->prepare("INSERT INTO stops (trip_id,city,country,arrival_date,departure_date,position) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("issssi",$trip_id,$city,$country,$arrival,$departure,$position);$stmt->execute();$stmt->close();
            header("Location: trip-details.php?id=".$trip_id);exit;
        }
    }
    if($action==='add_expense'){
        $category=$_POST['category']??'other';$label=trim($_POST['label']??'');$amount=(float)($_POST['amount']??0);
        if(in_array($category,['transport','stay','activities','meals','other'],true)&&$label!==''){
            $stmt=$conn->prepare("INSERT INTO expenses (trip_id,category,label,amount) VALUES (?,?,?,?)");
            $stmt->bind_param("issd",$trip_id,$category,$label,$amount);$stmt->execute();$stmt->close();
            header("Location: trip-details.php?id=".$trip_id);exit;
        }
    }
}

$stops=[];$stmt=$conn->prepare("SELECT * FROM stops WHERE trip_id=? ORDER BY position,id");$stmt->bind_param("i",$trip_id);$stmt->execute();$r=$stmt->get_result();while($row=$r->fetch_assoc())$stops[]=$row;$stmt->close();
$expenses=[];$total=0;$stmt=$conn->prepare("SELECT * FROM expenses WHERE trip_id=? ORDER BY id DESC");$stmt->bind_param("i",$trip_id);$stmt->execute();$r=$stmt->get_result();while($row=$r->fetch_assoc()){$expenses[]=$row;$total+=(float)$row['amount'];}$stmt->close();
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=htmlspecialchars($trip['name'])?> | GlobeTrotter</title><link rel="stylesheet" href="assets/css/home.css">
<style>.wrap{width:min(1100px,92%);margin:45px auto}.box{background:#fff;padding:28px;border-radius:18px;margin-bottom:24px;box-shadow:0 7px 22px rgba(0,0,0,.07)}.grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}.field{margin-bottom:12px}.field input,.field select{width:100%;padding:10px;border:1px solid #ddd;border-radius:8px}.item{padding:14px 0;border-bottom:1px solid #eee}.muted{color:#777}@media(max-width:700px){.grid{grid-template-columns:1fr}}</style></head><body>
<header class="topbar"><a class="brand" href="homepage.php"><span class="mark">✈</span><span>Globe<span>Trotter</span></span></a><nav><a href="homepage.php">Home</a><a href="my-trips.php">My Trips</a><a href="cities.php">Explore</a><a href="activities.php">Activities</a></nav></header>
<main class="wrap"><div class="box"><span class="label">TRIP DETAILS</span><h1><?=htmlspecialchars($trip['name'])?></h1><p class="muted"><?=htmlspecialchars($trip['destination'] ?: '')?> • <?=date('d M Y',strtotime($trip['start_date']))?> – <?=date('d M Y',strtotime($trip['end_date']))?></p><p><?=htmlspecialchars($trip['description'] ?: '')?></p><p><b>Budget:</b> <?=htmlspecialchars($trip['currency'])?> <?=number_format((float)$trip['budget'],2)?> &nbsp; <b>Expenses:</b> <?=htmlspecialchars($trip['currency'])?> <?=number_format($total,2)?></p></div>
<div class="grid"><section class="box"><h2>Stops</h2><?php foreach($stops as $s):?><div class="item"><b><?=htmlspecialchars($s['city'])?></b>, <?=htmlspecialchars($s['country'])?><br><span class="muted"><?=htmlspecialchars($s['arrival_date']??'')?> – <?=htmlspecialchars($s['departure_date']??'')?></span></div><?php endforeach;?><h3>Add Stop</h3><form method="POST"><input type="hidden" name="action" value="add_stop"><div class="field"><input name="city" placeholder="City" required></div><div class="field"><input name="country" placeholder="Country"></div><div class="field"><input type="date" name="arrival_date"></div><div class="field"><input type="date" name="departure_date"></div><div class="field"><input type="number" name="position" value="<?=count($stops)+1?>" min="0"></div><button class="btn primary">Add Stop</button></form></section>
<section class="box"><h2>Expenses</h2><?php foreach($expenses as $e):?><div class="item"><b><?=htmlspecialchars($e['label'])?></b><br><span class="muted"><?=htmlspecialchars($e['category'])?> • ₹<?=number_format((float)$e['amount'],2)?></span></div><?php endforeach;?><h3>Add Expense</h3><form method="POST"><input type="hidden" name="action" value="add_expense"><div class="field"><select name="category"><option>transport</option><option>stay</option><option>activities</option><option>meals</option><option>other</option></select></div><div class="field"><input name="label" placeholder="Expense name" required></div><div class="field"><input type="number" step="0.01" min="0" name="amount" placeholder="Amount" required></div><button class="btn primary">Add Expense</button></form></section></div></main></body></html>
