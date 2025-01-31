<?php 
session_start();

// Ensure session variables are set
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

// Database connection
include("db.php");

// Variables for the event form
$week = $date = $semester_event = $committee = "";

// If editing an event (event_id in URL), fetch event data
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    // Fetch event data from database for the given event_id
    $sql = "SELECT * FROM events WHERE event_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    // Populate form fields with existing event data
    if ($event) {
        $week = $event['week'];
        $date = $event['date'];
        $semester_event = $event['semester_event'];
        $committee = $event['committee'];

    }
    // } else {
    //     echo "Event not found!";
    // }
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure required fields are set
    if (isset($_POST['week'], $_POST['date'], $_POST['semester_event'], $_POST['committee'])) {
        // Capture the form values
        $week = $_POST['week'];
        $date = $_POST['date'];
        $semester_event = $_POST['semester_event'];
        $committee = $_POST['committee'];

        // Check if we are editing an existing event
        if (isset($_POST['event_id']) && !empty($_POST['event_id'])) {
            $event_id = $_POST['event_id'];  // Editing event
            // Prepare the SQL statement to update the event
            $sql = "UPDATE events SET week=?, date=?, semester_event=?, committee=? WHERE event_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $week, $date, $semester_event, $committee, $event_id);

            if ($stmt->execute()) {
                echo "<script>
                alert('Event successfully updated');
                </script>";
            }
            else {
                echo "Error updating event: " . $stmt->error;
            }
        } else {
            // Insert new event if no event_id is set
            $sql = "INSERT INTO events (week, date, semester_event, committee) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $week, $date, $semester_event, $committee);

            if ($stmt->execute()) {
                echo "<script>
                alert('New event added successfully.');
                </script>";
            } else {
                echo "Error adding event: " . $stmt->error;
            }
        }
    } else {
        echo "All fields are required.";
    }
}

// Fetch all events for display
$eventsQuery = "SELECT * FROM events";
$eventsResult = $conn->query($eventsQuery);

// Format date range
function formatDateRange($date) {
    // Convert the date to a DateTime object
    $startDate = new DateTime($date);
    
    // Get the next date (7 days later) to form the range
    $endDate = clone $startDate;
    $endDate->modify('+6 days');

    // Get the ordinal suffix for day numbers (e.g., 'st', 'nd', 'rd', 'th')
    $startDay = $startDate->format('j'); // Day of the month
    $endDay = $endDate->format('j'); // Day of the month

    $startOrdinal = getOrdinalSuffix($startDay);
    $endOrdinal = getOrdinalSuffix($endDay);

    // Format the date range as "16th Jan - 22nd Jan 2025"
    return $startDay . $startOrdinal . ' ' . $startDate->format('M') . ' - ' . $endDay . $endOrdinal . ' ' . $endDate->format('M Y');
}

function getOrdinalSuffix($day) {
    // Return the ordinal suffix for a given day
    if ($day >= 11 && $day <= 13) {
        return 'th';
    }
    switch ($day % 10) {
        case 1: return 'st';
        case 2: return 'nd';
        case 3: return 'rd';
        default: return 'th';
    }
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
        #home_icon{
            color: blue;
            font-size: 40px;
            position: absolute;
            top: 0px;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<a href="./admin.php">
            <i id="home_icon" class="fa fa-home"></i>
        </a>
    <div class="container mt-5">
        <div class="button-container">
            <a href="add_event.php" class="btn btn-primary">Add New Event</a>
            <!-- <a href="download_events.php" class="btn btn-success">Download Events</a> -->
        </div>

        <h2>Edit or Add Event</h2>
        <form id="eventForm" action="new_event.php" method="POST">
            <!-- Hidden input for event_id when editing -->
            <input type="hidden" name="event_id" value="<?php echo $event_id ?? ''; ?>">

            <div class="form-group">
                <label for="week">Week:</label>
                <input type="text" class="form-control" id="week" name="week" value="<?php echo $week; ?>" required>
            </div>
            <div class="form-group">
                <label for="date">Date:</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo $date; ?>" required>
            </div>
            <div class="form-group">
                <label for="semester_event">Semester Event:</label>
                <textarea type="text" class="form-control" id="semester_event" name="semester_event" required> <?php echo $semester_event; ?></textarea>
            </div>
            <div class="form-group">
                <label for="committee">Committee:</label>
                <textarea type="text" class="form-control" id="committee" name="committee" required><?php echo $committee; ?></textarea> 
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
                        <td><?php echo $row['week']; ?></td>
                        <td><?php echo formatDateRange($row['date']); ?></td>
                        <td><?php echo $row['semester_event']; ?></td>
                        <td><?php echo $row['committee']; ?></td>
                        <td>
                            <a href="add_event.php?event_id=<?php echo $row['event_id']; ?>" class="btn btn-success btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        function clearInputs() {
            document.getElementById('week').value = '';
            document.getElementById('date').value = '';
            document.getElementById('semester_event').value = '';
            document.getElementById('committee').value = '';
        }

        // Reset the form after successful submission
        window.onload = function() {
            if (<?php echo isset($_POST['event_id']) ? 'true' : 'false'; ?>) {
                document.getElementById("eventForm").reset();  // Reset the form
            }
        };
    </script>

</body>
</html>
