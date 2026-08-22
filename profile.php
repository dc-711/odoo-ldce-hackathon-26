<?php

header("Content-Type: application/json");

include "db.php";

/* =========================================
   USER ID
========================================= */

$user_id = 1;


/* =========================================
   SQL QUERY
========================================= */

$sql = "SELECT 
            user_id,
            name,
            email,
            phone,
            dob,
            gender,
            address,
            country,
            city,
            created_at
        FROM `user`
        WHERE user_id = ?";


/* =========================================
   PREPARE QUERY
========================================= */

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    echo json_encode([
        "success" => false,
        "message" => "SQL Prepare Error: " . mysqli_error($conn)
    ]);

    exit;
}


/* =========================================
   BIND USER ID
========================================= */

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);


/* =========================================
   EXECUTE QUERY
========================================= */

if (!mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "success" => false,
        "message" => "SQL Execute Error: " . mysqli_stmt_error($stmt)
    ]);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    exit;
}


/* =========================================
   GET RESULT
========================================= */

$result = mysqli_stmt_get_result($stmt);

if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Could not get database result."
    ]);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    exit;
}


/* =========================================
   CHECK USER
========================================= */

if (mysqli_num_rows($result) == 0) {

    echo json_encode([
        "success" => false,
        "message" => "No user found with user_id = " . $user_id
    ]);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    exit;
}


/* =========================================
   FETCH USER
========================================= */

$user = mysqli_fetch_assoc($result);


/* =========================================
   SEND JSON RESPONSE
========================================= */

echo json_encode([
    "success" => true,
    "user" => $user
]);


/* =========================================
   CLOSE
========================================= */

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>