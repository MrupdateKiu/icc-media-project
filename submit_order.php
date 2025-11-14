<?php
$host = "localhost";
$db = "ICCMEDIA_PROJECT";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $location = htmlspecialchars($_POST['location']);
    $size = $_POST['size'];
    $color = $_POST['color'];
    $quality = $_POST['quality'];
    $payment_option = $_POST['payment_option'];

    $price_map = [
        '35k-long' => 35000,
        '25k-short'=> 25000,
        '15k-short'=> 15000
    ];
    $price = $price_map[$quality];

    $stmt = $pdo->prepare("INSERT INTO tshirt_orders (name,email,phone,location,size,color,quality,price,payment_option) VALUES (?,?,?,?,?,?,?,?,?)");
    if ($stmt->execute([$name,$email,$phone,$location,$size,$color,$quality,$price,$payment_option])) {
        echo "<p style='text-align:center;color:green;font-weight:bold;'>Thank you, $name! Your order has been received. We appreciate your support for ICC Media Ministry.</p>";
        echo "<p style='text-align:center;'>A confirmation message will be sent to $email after payment verification.</p>";
        echo "<p style='text-align:center;'><a href='index.php'>Back to Home</a></p>";
    } else {
        echo "<p style='text-align:center;color:red;'>Error submitting order. Please try again.</p>";
    }
}
?>
