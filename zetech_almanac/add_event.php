<?php 
session_start();

// Ensure session variables are set
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

// Database connection
include("db.php");

// Initialize variables for form inputs
$event_id = $week = $date = $semester_event = $committee = "";

// Check if editing an event (event_id is provided in URL)
if (isset($_GET['event_id']) && !empty($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);

    // Fetch event data from database for the given event_id
    $sql = "SELECT * FROM events WHERE event_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    if ($event) {
        $week = htmlspecialchars($event['week']);
        $date = htmlspecialchars($event['date']);
        $semester_event = htmlspecialchars($event['semester_event']);
        $committee = htmlspecialchars($event['committee']);
    } else {
        echo "<script>alert('Event not found!');</script>";
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['week']) && !empty($_POST['date']) && !empty($_POST['semester_event']) && !empty($_POST['committee'])) {
        $week = $_POST['week'];
        $date = $_POST['date'];
        $semester_event = $_POST['semester_event'];
        $committee = $_POST['committee'];

        if (!empty($_POST['event_id'])) {
            // Editing an existing event
            $event_id = intval($_POST['event_id']);
            $sql = "UPDATE events SET week=?, date=?, semester_event=?, committee=? WHERE event_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $week, $date, $semester_event, $committee, $event_id);
            $success = $stmt->execute();
        } else {
            // Insert new event
            $sql = "INSERT INTO events (week, date, semester_event, committee) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $week, $date, $semester_event, $committee);
            $success = $stmt->execute();
        }

        if ($success) {
            echo "<script>alert('Event successfully saved.'); window.location.href = 'add_event.php';</script>";
        } else {
            echo "<script>alert('Error saving event: " . $stmt->error . "');</script>";
        }
    } else {
        echo "<script>alert('All fields are required.');</script>";
    }
}

// Fetch all events for display
$eventsQuery = "SELECT * FROM events";
$eventsResult = $conn->query($eventsQuery);

// Function to format date range
function formatDateRange($date) {
    $startDate = new DateTime($date);
    $endDate = clone $startDate;
    $endDate->modify('+6 days');

    return $startDate->format('jS M') . " - " . $endDate->format('jS M Y');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit or Add Event</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        #home_icon {
            color: blue;
            font-size: 40px;
            position: absolute;
            top: 10px;
            left: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <a href="./admin.php">
            <i id="home_icon" class="fa fa-home"></i>
        </a>

        <h2><?php echo isset($event_id) && !empty($event_id) ? "Edit Event" : "Add Event"; ?></h2>
        
        <form id="eventForm" action="add_event.php" method="POST">
            <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_id); ?>">

            <div class="form-group">
                <label for="week">Week:</label>
                <input type="text" class="form-control" id="week" name="week" value="<?php echo htmlspecialchars($week); ?>" required>
            </div>

            <div class="form-group">
                <label for="date">Date:</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>" required>
            </div>

            <div class="form-group">
                <label for="semester_event">Semester Event:</label>
                <input type="text" class="form-control" id="semester_event" name="semester_event" value="<?php echo htmlspecialchars($semester_event); ?>" required>
            </div>

            <div class="form-group">
                <label for="committee">Committee:</label>
                <input type="text" class="form-control" id="committee" name="committee" value="<?php echo htmlspecialchars($committee); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Save Event</button>
        </form>

        <h3 class="mt-5">Existing Events</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Week</th>
                    <th>Date</th>
                    <th>Semester Event</th>
                    <th>Committee</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $eventsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['week']); ?></td>
                        <td><?php echo formatDateRange($row['date']); ?></td>
                        <td><?php echo htmlspecialchars($row['semester_event']); ?></td>
                        <td><?php echo htmlspecialchars($row['committee']); ?></td>
                        <td>
                            <a href="add_event.php?event_id=<?php echo $row['event_id']; ?>" class="btn btn-success btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        window.onload = function() {
            if ("<?php echo isset($_POST['week']) ? 'true' : 'false'; ?>" === "true") {
                document.getElementById("eventForm").reset();
            }
        };
    </script>
</body>
</html>
