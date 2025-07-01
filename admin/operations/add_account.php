<?php
include '../../database/dbconn.php'; // Make sure the database connection is correctly included

// Check if the request is a POST request and the required data is present
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the data from the AJAX request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['userID'], $data['username'], $data['email'], $data['password'], $data['usertype'])) {
        // Sanitize input data to avoid SQL injection
        $userID = $conn->real_escape_string($data['userID']);
        $username = $conn->real_escape_string($data['username']);
        $email = $conn->real_escape_string($data['email']);
        $password = password_hash($data['password'], PASSWORD_BCRYPT);  // Encrypt the password
        $usertype = $conn->real_escape_string($data['usertype']);
        
        // Validate the email to make sure it ends with '@cvsu.edu.ph'
        if (!preg_match('/^[a-zA-Z0-9._%+-]+@cvsu\.edu\.ph$/', $email)) {
            echo json_encode(['success' => false, 'message' => 'Email must end with @cvsu.edu.ph']);
            exit();
        }
        
        $status = "Active";

        // Prepare the SQL query to insert the new account into the database
        $query = "INSERT INTO account (userID, username, email, password, usertype, status) VALUES (?, ?, ?, ?, ?, ?)";
        
        // Use a prepared statement to avoid SQL injection
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("ssssss", $userID, $username, $email, $password, $usertype, $status);
            
            // Execute the query
            if ($stmt->execute()) {
                // Respond with a success message
                echo json_encode(['success' => true, 'message' => 'Account added successfully']);
            } else {
                // Respond with an error message
                echo json_encode(['success' => false, 'message' => 'Error adding account']);
            }
            
            // Close the statement
            $stmt->close();
        } else {
            // Respond with an error if the prepared statement fails
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } else {
        // Respond with an error if the required data is not set
        echo json_encode(['success' => false, 'message' => 'Missing data']);
    }
} else {
    // Respond with an error if the request method is not POST
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Close the database connection
$conn->close();
?>
