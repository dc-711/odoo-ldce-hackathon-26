<?php
include("db.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $age = ($_POST['age'] ?? '') !== '' ? (int)$_POST['age'] : null;
    $address = trim($_POST['address'] ?? "");
    $city = trim($_POST['city'] ?? "");
    $state = trim($_POST['state'] ?? "");

    if (strlen($name) < 2) {
        $error = "Please enter your full name.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($age !== null && ($age < 1 || $age > 120)) {
        $error = "Please enter a valid age.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $check->bind_param("s", $email);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if ($exists) {
            $error = "An account with this email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                INSERT INTO users (name, email, password_hash, age, address, city, state)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssisss", $name, $email, $hash, $age, $address, $city, $state);

            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit;
            }
            $error = "Signup failed. Please try again.";
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GlobeTrotter Signup</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:"Segoe UI",Arial,sans-serif;min-height:100vh;display:flex;justify-content:center;align-items:center;background:url('assets/img/beautiful-landscape-view-covered-with-greenery-mountains-background.jpg') center/cover fixed no-repeat;padding:30px 15px}
.overlay{position:fixed;inset:0;background:rgba(25,25,25,.58)}
.card{position:relative;z-index:2;width:500px;background:rgba(255,255,255,.97);padding:34px;border-radius:18px;box-shadow:0 12px 35px rgba(0,0,0,.32);text-align:center}
.brand{display:inline-flex;align-items:center;gap:10px;text-decoration:none;color:#333;font-size:25px;font-weight:700;margin-bottom:12px}.brand>span:last-child>span{color:#777}
.mark{width:42px;height:42px;border-radius:10px;background:#444;color:#fff;display:flex;align-items:center;justify-content:center;transform:rotate(-10deg)}
h2{margin:5px 0 7px}.sub{font-size:14px;color:#777;margin-bottom:22px}
label{display:block;text-align:left;font-size:14px;font-weight:600;color:#444;margin-bottom:6px}
input,textarea{width:100%;padding:11px 12px;border:1px solid #ccc;border-radius:8px;margin-bottom:14px;font:inherit;background:#fafafa}
input:focus,textarea:focus{outline:none;border-color:#666;box-shadow:0 0 7px rgba(80,80,80,.2);background:#fff}
textarea{min-height:70px;resize:vertical}.row{display:flex;gap:14px}.field{flex:1}
button{width:100%;border:0;background:#555;color:#fff;padding:13px;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer}button:hover{background:#333}
.error{background:#fdecea;color:#d93025;padding:10px;border-radius:7px;margin-bottom:15px;font-size:14px}.links{font-size:14px;color:#666}.links a{color:#444;font-weight:600;text-decoration:none}
@media(max-width:520px){.card{width:100%}.row{flex-direction:column;gap:0}}
</style>
</head>
<body>
<div class="overlay"></div>
<div class="card">
<a class="brand" href="homepage.php"><span class="mark">✈</span><span>Globe<span>Trotter</span></span></a>
<h2>Create Account</h2>
<p class="sub">Start planning your next adventure 🌍</p>
<?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<form method="POST">
<label>Full Name</label>
<input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>

<label>Email Address</label>
<input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

<div class="row">
<div class="field"><label>Age</label><input type="number" name="age" min="1" max="120" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>"></div>
<div class="field"><label>City</label><input type="text" name="city" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>"></div>
</div>

<label>State</label>
<input type="text" name="state" value="<?= htmlspecialchars($_POST['state'] ?? '') ?>">

<label>Address</label>
<textarea name="address"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>

<label>Password</label>
<input type="password" name="password" minlength="6" required>

<button type="submit">Create Account</button>
</form>

<p class="links">Already have an account? <a href="login.php">Login</a></p>
</div>
</body>
</html>
