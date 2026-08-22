<?php
session_start();
include("db.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepared statement prevents SQL injection
    $stmt = $conn->prepare("SELECT user_id, name, email, password, status 
                            FROM users 
                            WHERE email = ?");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        // Check if account is blocked
        if ($user['status'] == 'blocked') {

            $error = "Your account has been blocked.";

        } elseif (password_verify($password, $user['password'])) {

            // Login successful
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];

            header("Location: index.php");
            exit;

        } else {

            $error = "Invalid email or password!";

        }

    } else {

        $error = "Invalid email or password!";

    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>GlobeTrotter Login</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
    margin: 0;
    font-family: "Segoe UI", Arial, sans-serif;

    height: 100vh;

    display: flex;
    justify-content: center;
    align-items: center;

    background-image: url('assets/img/beautiful-landscape-view-covered-with-greenery-mountains-background.jpg');
    background-repeat: no-repeat;
    background-position: center;
    background-size: cover;
    background-attachment: fixed;

    overflow: hidden;
}


      

        /* Login card */

        .login-box {

            position: relative;

            background: #ffffff;

            padding: 40px 35px;

            width: 390px;

            border-radius: 16px;

            box-shadow:
                0px 10px 30px rgba(0, 0, 0, 0.35);

            text-align: center;

            z-index: 2;

            animation: fadeIn 0.8s ease-in-out;
        }


        @keyframes fadeIn {

            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }


        /* Logo */

        .logo {

            width: 150px;

            height: auto;

            margin-bottom: 15px;
        }


        /* Title */

        .login-box h2 {

            margin: 5px 0 8px;

            color: #333333;

            font-size: 27px;
        }


        /* Welcome message */

        .welcome-text {

            color: #777777;

            margin-bottom: 25px;

            font-size: 14px;
        }


        /* Error message */

        .error {

            background: #fdecea;

            color: #d93025;

            padding: 11px;

            margin-bottom: 18px;

            border-radius: 7px;

            font-size: 14px;

            animation: shake 0.3s;
        }


        @keyframes shake {

            0% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            50% {
                transform: translateX(5px);
            }

            75% {
                transform: translateX(-5px);
            }

            100% {
                transform: translateX(0);
            }
        }


        /* Labels */

        label {

            display: block;

            text-align: left;

            margin-bottom: 7px;

            font-weight: 600;

            font-size: 14px;

            color: #444444;
        }


        /* Inputs */

        input[type="email"],
        input[type="password"] {

            width: 100%;

            padding: 12px;

            border: 1px solid #cccccc;

            border-radius: 8px;

            margin-bottom: 17px;

            font-size: 14px;

            background: #fafafa;

            transition: 0.3s;
        }


        input:focus {

            border-color: #666666;

            background: #ffffff;

            box-shadow:
                0 0 7px rgba(80, 80, 80, 0.25);

            outline: none;
        }


        /* Login button */

        button {

            width: 100%;

            background: #555555;

            color: #ffffff;

            border: none;

            padding: 13px;

            border-radius: 8px;

            font-size: 16px;

            font-weight: 600;

            cursor: pointer;

            transition: all 0.3s ease;
        }


        button:hover {

            background: #333333;

            transform: translateY(-2px);

            box-shadow:
                0px 5px 12px rgba(0, 0, 0, 0.25);
        }


        /* Links */

        a {

            color: #555555;

            text-decoration: none;

            font-weight: 600;
        }


        a:hover {

            color: #222222;

            text-decoration: underline;
        }


        .forgot {

            margin-top: 15px;

            margin-bottom: 10px;

            font-size: 14px;
        }


        .signup {

            font-size: 14px;

            color: #666666;
        }


        /* Footer */

        .footer {

            margin-top: 22px;

            padding-top: 15px;

            border-top: 1px solid #eeeeee;

            font-size: 12px;

            color: #999999;
        }


        /* Mobile */

        @media (max-width: 480px) {

            .login-box {

                width: 90%;

                padding: 30px 25px;
            }

        }
        /* GlobeTrotter Logo */

.brand {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;

    margin-bottom: 25px;

    font-size: 25px;
    font-weight: 700;

    color: #333333;
    text-decoration: none;
}

.brand:hover {
    text-decoration: none;
    color: #333333;
}

/* Airplane icon */

.mark {
    width: 42px;
    height: 42px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #444444;
    color: white;

    border-radius: 10px;

    font-size: 21px;

    transform: rotate(-10deg);
}

/* Trotter text */

.brand > span:last-child > span {
    color: #777777;
}
    </style>

</head>


<body>


<div class="overlay"></div>


<div class="login-box">


    <!-- Change this to your GlobeTrotter logo -->
<a class="brand" href="index.php">
    <span class="mark">✈</span>
    <span>Globe<span>Trotter</span></span>
</a>


    <h2>Welcome Back</h2>

    <p class="welcome-text">
        Login to continue your journey ✈️
    </p>


    <!-- Error message -->

    <?php if (!empty($error)): ?>

        <div class="error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <form method="POST" autocomplete="off">


        <label for="email">
            Email Address
        </label>

        <input
            type="email"
            id="email"
            name="email"
            placeholder="Enter your email"
            required
        >


        <label for="password">
            Password
        </label>

        <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter your password"
            required
        >


        <button type="submit">
            Login
        </button>


        <p class="forgot">

            <a href="forgot_password.php">
                Forgot Password?
            </a>

        </p>


        <p class="signup">

            Don't have an account?

            <a href="signup.php">
                Sign Up
            </a>

        </p>


    </form>


    <div class="footer">

        © 2026 GlobeTrotter • Explore. Plan. Travel.

    </div>


</div>


</body>

</html>
