<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ratings";


$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$createTableQuery = "CREATE TABLE IF NOT EXISTS review_table (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    rating INT NOT NULL,
    message TEXT NOT NULL,
    datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $createTableQuery);

if (isset($_POST['rating_value'])) {
    $rating_value = $_POST['rating_value'];
    $userName = $_POST['userName'];
    $userMessage = $_POST['userMessage'];

    $stmt = $conn->prepare("INSERT INTO review_table (name, rating, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $userName, $rating_value, $userMessage);

    if ($stmt->execute()) {
        echo "New Review Added Successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

if (isset($_POST['action'])) {
    $avgUserRatings = 0;
    $totalReviews = 0;
    $totalRatings5 = 0;
    $totalRatings4 = 0;
    $totalRatings3 = 0;
    $totalRatings2 = 0;
    $totalRatings1 = 0;
    $ratingsList = array();
    $totalRatings_avg = 0;

    $sql = "SELECT * FROM review_table ORDER BY review_id DESC";
    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $ratingsList[] = array(
            'review_id' => $row['review_id'],
            'name' => $row['name'],
            'rating' => $row['rating'],
            'message' => $row['message'],
            'datetime' => date('l jS \of F Y h:i:s A', strtotime($row['datetime']))
        );
        switch ($row['rating']) {
            case '5':
                $totalRatings5++;
                break;
            case '4':
                $totalRatings4++;
                break;
            case '3':
                $totalRatings3++;
                break;
            case '2':
                $totalRatings2++;
                break;
            case '1':
                $totalRatings1++;
                break;
        }
        $totalReviews++;
        $totalRatings_avg += intval($row['rating']);
    }
    $avgUserRatings = $totalRatings_avg / $totalReviews;

    $output = array(
        'avgUserRatings' => number_format($avgUserRatings, 1),
        'totalReviews' => $totalReviews,
        'totalRatings5' => $totalRatings5,
        'totalRatings4' => $totalRatings4,
        'totalRatings3' => $totalRatings3,
        'totalRatings2' => $totalRatings2,
        'totalRatings1' => $totalRatings1,
        'ratingsList' => $ratingsList
    );

    echo json_encode($output);
}

mysqli_close($conn);
?>
