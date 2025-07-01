document.getElementById('addStudentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Get the input values
    const studentId = document.getElementById('studentIdInput').value.trim();
    const fullName = document.getElementById('fullNameInput').value.trim();
    const scheduleId = document.getElementById('scheduleIdHidden').value;  // Get schedule_id from PHP

    // Check if the fields are not empty
    if (studentId === '' || fullName === '') {
        alert('Please fill in all fields');
        return;
    }

    // Create the data to send in the request
    const data = {
        studID: studentId,
        fullname: fullName,
        schedule_id: scheduleId
    };

    // Send AJAX request to the PHP server to add the student
    fetch('operations/add_student.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Student added successfully');
            // Optionally, reload the page or update the table dynamically
            location.reload();  // Or you can dynamically add the row without refreshing the page
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error adding the student.');
    });
});
