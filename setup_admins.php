<?php
// Database connection
$host = "localhost";
$db = "ICCMEDIA_PROJECT";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Database connection failed: ".$e->getMessage());
}

// Admins data
$admins = [
    ['name'=>'Best Tyson', 'role'=>'coordinator', 'photo'=>'tyson.jpg', 'contact'=>'+256751707474', 'username'=>'tyson', 'password'=>'update$$!'],
    ['name'=>'Ronald Waisswa', 'role'=>'admin', 'photo'=>'ronald.jpg', 'contact'=>'+256708001819', 'username'=>'ronald', 'password'=>'ronald123'],
    ['name'=>'Shema Nickson', 'role'=>'admin', 'photo'=>'shema.jpg', 'contact'=>'+256764522222', 'username'=>'shema', 'password'=>'shema123'],
    ['name'=>'David Kisembo', 'role'=>'admin', 'photo'=>'david.jpg', 'contact'=>'+243828190978', 'username'=>'david', 'password'=>'david123'],
    ['name'=>'Kabazzi Douglas', 'role'=>'admin', 'photo'=>'douglas.jpg', 'contact'=>'+256726319271', 'username'=>'douglas', 'password'=>'douglas123'],
    ['name'=>'Philip Akol', 'role'=>'admin', 'photo'=>'philip.jpg', 'contact'=>'+256778772001', 'username'=>'philip', 'password'=>'philip123'],
    ['name'=>'Kemigisha Mercy', 'role'=>'admin', 'photo'=>'mercy.jpg', 'contact'=>'+256772771729', 'username'=>'mercy', 'password'=>'mercy123'],
];

// Insert or update admins (upsert)
foreach($admins as $a){
    $hash = password_hash($a['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO admins (name, role, photo, contact, username, password)
        VALUES (?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE
            name=VALUES(name),
            role=VALUES(role),
            photo=VALUES(photo),
            contact=VALUES(contact),
            password=VALUES(password)
    ");

    $stmt->execute([$a['name'],$a['role'],$a['photo'],$a['contact'],$a['username'],$hash]);
    echo "Inserted/Updated: ".$a['name']."<br>";
}

echo "<br>All admins inserted/updated successfully!";
?>
