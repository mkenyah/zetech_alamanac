<?php

// Database connection (assuming the credentials are correct)
include("db.php");

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
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: navy;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .dashboard {
            margin-top: 20px;
        }
        .container {
            max-width: 95%;
            box-shadow: 0 4px 20px rgba(8, 8, 8, 0.9);
            background-color: white;
            padding: 20px;
            border-radius: 2px;
        }
        .btn-custom {
            background-color: navy;
            color: white;
        }
        footer {
            background-color: navy;
            color: white;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        table {
            border-collapse: collapse;
        }
        th, td {
            text-align: center;
        }
        th {
            background-color: navy;
            color: whitesmoke;
        }
        .yearb {
            margin-top: 20px;
        }
        .yearb .btn {
            margin: 5px 30px 2px;
        }
        #btnd {
            background-color: navy;
            color: white;
        }
        #btnd:hover {
            background-color: white;
            color: navy;
            border: 1px solid navy;
        }
        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            width: 30%;
            border-radius: 5px;
            text-align: center;
        }
        .modal input {
            width: 100%;
            margin-bottom: 10px;
            padding: 5px;
        }

        @media print {
            /* Hide elements you don't want to appear in the print version */
            body * {
                visibility: hidden;
            }

            /* Make sure only specific elements are visible when printed */
            .printable, .printable * {
                visibility: visible;
            }

            /* Hide the Actions column and other unwanted columns */
            .actions-column {
                display: none;
            }

            /* Hide the navigation and footer */
            nav, footer {
                display: none;
            }

            /* Position the printable section at the top of the page */
            .printable {
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%); /* Center the table */
        width: 105%; /* Adjust table width */
    }

            /* Ensure the table takes up the full width of the page */
            table {
                width: 100%;
                border-collapse: collapse;
            }

            /* Make table headers bold and ensure a clear, clean look */
            th {
                font-weight: bold;
                color: black;
            }

            /* Remove any borders and padding that might cause layout issues */
            th, td {
                padding: 5px;
                border: 1px solid black;
                color: black;
                font-size: 23px;
            }

            /* Hide specific columns for print */
            td:nth-child(5), th:nth-child(5) { /* Actions column */
                display: none;
            }

            /* Ensure the "Week" column is visible */
            td:nth-child(1), th:nth-child(1) { /* Week column */
                visibility: visible;
            }

            /* Ensure there is no extra space or unwanted margins */
            body {
                margin: 0;
                padding: 0;
            }

            .print-footer {
                visibility: visible;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <a class="navbar-brand" href="#">Zetech University Almanac</a>
    <div class="ml-auto">
        <span class="text-white">Welcome, Vistor</span>
        <a href="logout.php" class="btn btn-sm btn-light ml-3">Logout</a>
    </div>
</nav>

<div class="container dashboard">
    <div class="events">
        <div class="text-right mb-3">
            <!-- Print Button -->
            
            <button id="btnd" class="btn btn-custom btn-sm" onclick="printTable()">Download</button>
        </div>
        <div class="table-responsive">
            
            <div class="printable">
            <h3 class="text-center">January - December 2025</h3>

                <table class="table table-striped table-bordered" id="data-table">
                    <thead class="thead-navy">
                        <tr>
                            <th>Week</th>
                            <th>Date</th>
                            <th>January-April 2025 Semester</th>
                            <th>Committee/Meeting</th>
                           
                        </tr>
                    </thead>
                    <tbody id="eventTable">
                        <!-- PHP logic to fetch events from the database -->
                        <?php
                        // Use mysqli query instead of PDO for fetch
                        $stmt = $conn->prepare("SELECT * FROM events");
                        $stmt->execute();
                        $result = $stmt->get_result(); 

                        if ($result->num_rows > 0) {
                            while ($event = $result->fetch_assoc()) {
                                // Use the formatDateRange function for custom date formatting
                                $formattedDate = formatDateRange($event['date']);
                                echo "<tr data-event-id='{$event['event_id']}'>
                                        <td>{$event['week']}</td>
                                        <td>{$formattedDate}</td>  <!-- Custom formatted date -->
                                        <td>{$event['semester_event']}</td>
                                        <td>{$event['committee']}</td>
                                      
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No events found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<footer class="text-center py-3">
    &copy; 2025 Zetech University. All rights reserved.
</footer>

<script>
// Function to confirm deletion
function confirmDelete() {
    return confirm("Are you sure you want to delete this event?");
}

function printTable() {
    window.print();
}
</script>
</body>
</html>
