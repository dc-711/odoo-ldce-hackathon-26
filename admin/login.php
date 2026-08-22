<?php
session_start();
include("db.php");

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: homepage.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("
            SELECT id, name, email, password_hash, role, status
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['status'] === 'blocked') {
                $error = "Your account has been blocked.";
            } elseif (password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                header("Location: homepage.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GlobeTrotter Login</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:"Segoe UI",Arial,sans-serif;min-height:100vh;display:flex;justify-content:center;align-items:center;background:url('assets/img/beautiful-landscape-view-covered-with-greenery-mountains-background.jpg') center/cover fixed no-repeat;padding:20px}
.overlay{position:fixed;inset:0;background:rgba(25,25,25,.58)}
.card{position:relative;z-index:2;width:390px;background:rgba(255,255,255,.97);padding:38px 35px;border-radius:18px;box-shadow:0 12px 35px rgba(0,0,0,.32);text-align:center}
.brand{display:inline-flex;align-items:center;gap:10px;text-decoration:none;color:#333;font-size:25px;font-weight:700;margin-bottom:16px}
.brand>span:last-child>span{color:#777}
.mark{width:42px;height:42px;border-radius:10px;background:#444;color:#fff;display:flex;align-items:center;justify-content:center;transform:rotate(-10deg)}
h2{margin:4px 0 8px;color:#333}.sub{color:#777;font-size:14px;margin-bottom:24px}
label{display:block;text-align:left;margin:0 0 7px;font-size:14px;font-weight:600;color:#444}
input{width:100%;padding:12px;border:1px solid #ccc;border-radius:8px;margin-bottom:16px;font-size:14px;background:#fafafa}
input:focus{outline:none;border-color:#666;box-shadow:0 0 7px rgba(80,80,80,.2);background:#fff}
button{width:100%;border:0;background:#555;color:#fff;padding:13px;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer}
button:hover{background:#333}.error{background:#fdecea;color:#d93025;padding:10px;border-radius:7px;margin-bottom:16px;font-size:14px}
.links{font-size:14px;color:#666;margin-top:16px}.links a{color:#444;font-weight:600;text-decoration:none}.links a:hover{text-decoration:underline}
.footer{margin-top:20px;padding-top:14px;border-top:1px solid #eee;color:#999;font-size:12px}
</style>
</head>
<body>
<div class="overlay"></div>
<div class="card">
    <a class="brand" href="homepage.php"><span class="mark">✈</span><span>Globe<span>Trotter</span></span></a>
    <h2>Welcome Back</h2>
    <p class="sub">Login to continue your journey ✈️</p>

    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST">
        <label>Email Address</label>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <p class="links">Don't have an account? <a href="signup.php">Sign Up</a></p>
    <div class="footer">© <?= date('Y') ?> GlobeTrotter • Explore. Plan. Travel.</div>
</div>
</body>
</html>
