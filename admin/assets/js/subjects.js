//search table
function filterTable() {
    const searchBar = document.getElementById('searchBar');
    const filter = searchBar.value.toLowerCase();
    const table = document.getElementById('userTable');
    const rows = table.getElementsByTagName('tr');
    let noMatch = true;

    for (let i = 0; i < rows.length; i++) {
        if (rows[i].id === 'noRecordRow'){
            continue;
        }

        const subject_id = rows[i].getElementsByTagName('th')[0]?.textContent || '';
        const subject_code = rows[i].getElementsByTagName('td')[1]?.textContent || '';
        const description = rows[i].getElementsByTagName('td')[2]?.textContent || '';

        if (
            subject_id.toLowerCase().indexOf(filter) > -1 ||
            subject_code.toLowerCase().indexOf(filter) > -1 ||
            description.toLowerCase().indexOf(filter) > -1
        ) {
            rows[i].style.display = '';
            noMatch = false;
        } else {
            rows[i].style.display = 'none';
        }
    }

    let noRecordRow = document.getElementById('noRecordRow');
    if (noMatch) {
        if (!noRecordRow) {
            noRecordRow = document.createElement('tr');
            noRecordRow.id = 'noRecordRow';
            noRecordRow.innerHTML = `
            <td colspan="4" style="text-align: center; color: red;">No Record Found</td>
            `;
            table.appendChild(noRecordRow);
        }
    } else {
        if (noRecordRow) {
            noRecordRow.remove();
        }
    }

}

// Add subject
document.getElementById('addSubjectForm').addEventListener('submit', function(e) {
    e.preventDefault();

    var subjectId = document.getElementById('subject_id').value;
    var subjectCode = document.getElementById('subject_code').value;
    var description = document.getElementById('description').value;

    var data = {
        subject_id: subjectId,
        subject_code: subjectCode,
        description: description
    };

    // Send data via AJAX
    fetch('operations/add_subject.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the table or update the UI as needed
            showAlert('Subject Added Successfully!');
            location.reload();  // You can refresh the page or update the table dynamically
        } else {
            showAlert('Error adding subject');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});


// Populate modal with selected data when 'Update' button is clicked
const updateModal = document.getElementById('updateModal');
updateModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;  // The button that triggered the modal

    // Get data from the button's data attributes
    const subjectID = button.getAttribute('data-subject_id');
    const subjectCode = button.getAttribute('data-course_code');
    const description = button.getAttribute('data-description');

    // Populate the modal fields with the subject data
    document.getElementById('update_subject_id').value = subjectID;
    document.getElementById('update_course_code').value = subjectCode;
    document.getElementById('update_description').value = description;
});

// Handle the form submission to update the subject
document.getElementById('updateSubjectForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true; // Disable the submit button to prevent duplicate submissions

    const subjectID = document.getElementById('update_subject_id').value;
    const courseCode = document.getElementById('update_course_code').value;
    const description = document.getElementById('update_description').value;

    // Send data via AJAX to update the subject
    const data = {
        subjectID: subjectID,
        courseCode: courseCode,
        description: description
    };

    fetch('operations/update_subject.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Subject updated successfully!');
            location.reload(); // Reload the page to reflect changes
        } else {
            alert('Error updating subject: ' + data.message); // Show error message
        }
    })
    .catch(error => {
        alert('Error: ' + error.message); // Show error alert
    })
    .finally(() => {
        submitButton.disabled = false; // Re-enable button after AJAX completion
    });
});

//store subject ID
document.getElementById('subject_id').addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    const subjectCode = selectedOption.getAttribute('data-subject-code');

    // Set the subject_code hidden input value
    document.getElementById('subject_code').value = subjectCode;
});


// Handle delete button click
document.querySelectorAll('.delete-btn').forEach(function(button) {
    button.addEventListener('click', function() {
        var subjectId = this.getAttribute('data-subject_id');
        
        // Show the confirmation modal
        var confirmDeleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
        confirmDeleteModal.show();

        // Handle the "Delete" button in the confirmation modal
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            // Send delete request via AJAX
            fetch('operations/delete_subject.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ subject_id: subjectId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the row from the table
                    button.closest('tr').remove();
                    showAlert('Subject deleted successfully!'); // Show success modal
                } else {
                    showAlert('Error deleting subject'); // Show error modal
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });

            // Hide the confirmation modal
            confirmDeleteModal.hide();
        });
    });
});


// Function to show the alert modal with a custom message
function showAlert(message) {
    // Set the message in the modal body
    document.getElementById('alertMessage').textContent = message;

    // Show the modal
    var alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
    alertModal.show();
}