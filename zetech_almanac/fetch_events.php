<?php
include 'db.php';

$query = "SELECT * FROM events";
$stmt = $pdo->query($query);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($events);
?>
