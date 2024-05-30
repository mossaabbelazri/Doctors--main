<?php
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbname = "doctor";

// Create connection
$conn = mysqli_connect($servername, $dbUsername, $dbPassword, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $doctorName = $data['doctorName'];

    // Replace with your FCM token (usually stored in your database)
    $fcmToken = "";

    $notification = [
        'title' => 'Emergency Notification',
        'body' => "You have an emergency intervention request, $doctorName.",
        'icon' => 'sos.png',
        'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
    ];

    $fields = [
        'to' => $fcmToken,
        'notification' => $notification,
        'data' => [
            'doctorName' => $doctorName
        ]
    ];

    $headers = [
        'Authorization: key=', //your key server in FCM
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    $result = curl_exec($ch);
    if ($result === FALSE) {
        die('Curl failed: ' . curl_error($ch));
    }
    curl_close($ch);

    echo json_encode(['success' => true, 'message' => 'Notification sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>


