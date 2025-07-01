//Search Function
function filterTable() {
    const searchBar = document.getElementById('searchBar');
    const filter = searchBar.value.toLowerCase();  // Get the search filter in lowercase
    const table = document.getElementById('userTable');
    const rows = table.getElementsByTagName('tr');  // Get all rows in the table
    let noMatch = true;

    for (let i = 0; i < rows.length; i++) {
        if (rows[i].id === 'noRecordRow') {
            continue;
        }

        // Extracting the values from the row
        const cid = rows[i].getElementsByTagName('th')[0]?.textContent || '';  // Get the CID (first column)
        const course_code = rows[i].getElementsByTagName('td')[0]?.textContent || '';  // Get the course code (second column)
        const course_name = rows[i].getElementsByTagName('td')[1]?.textContent || '';  // Get the course name (third column)

        // Check if any of the columns contain the filter text
        if (
            cid.toLowerCase().indexOf(filter) > -1 ||
            course_code.toLowerCase().indexOf(filter) > -1 ||
            course_name.toLowerCase().indexOf(filter) > -1
        ) {
            rows[i].style.display = '';  // Show the row if it matches
            noMatch = false;
        } else {
            rows[i].style.display = 'none';  // Hide the row if it doesn't match
        }
    }

    // Check if there were any matches; if not, display a "No Record Found" message
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
            noRecordRow.remove();  // Remove "No Record Found" message if there are matches
        }
    }
}


// Populate modal with selected data when 'Update' button is clicked
const updateModal = document.getElementById('updateModal');
updateModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;  // The button that triggered the modal

    // Get data from the button
    const cid = button.getAttribute('data-cid');
    const courseCode = button.getAttribute('data-course_code');
    const courseName = button.getAttribute('data-course_name');

    // Populate the modal fields
    document.getElementById('update_cid').value = cid;
    document.getElementById('update_course_code').value = courseCode;
    document.getElementById('update_course_name').value = courseName;
});

// Handle the form submission to update the course
document.getElementById('updateCourseForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;

    const cid = document.getElementById('update_cid').value;
    const courseCode = document.getElementById('update_course_code').value;
    const courseName = document.getElementById('update_course_name').value;

    const data = {
        cid: cid,
        course_code: courseCode,
        course_name: courseName
    };

    fetch('operations/update_course.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Course updated successfully!');
            location.reload();
        } else {
            alert('Error updating course: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        submitButton.disabled = false;
    });
});

//Delete Course
document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function () {
        const cid = this.getAttribute('data-cid');
        if (confirm('Are you sure you want to delete this course?')) {
            fetch('operations/delete_course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ cid: cid })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Course deleted successfully!');
                    location.reload();
                } else {
                    alert('Error deleting course: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
    });
});
