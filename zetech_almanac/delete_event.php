<?php
session_start();

// Ensure session variables are set
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

include("db.php"); // Database connection

// Check if the event_id is provided
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    // Prepare a query to delete the event
    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);

    // Execute the deletion
    if ($stmt->execute()) {
        // Redirect to dashboard or the same page after deletion
        header("Location: admin.php?message=Event deleted successfully");
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    echo "Event ID is missing!";
}
?>
