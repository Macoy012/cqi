function filterTable() {
    const searchBar = document.getElementById('searchBar');
    const filter = searchBar.value.toLowerCase();  // Get the search input in lowercase
    const table = document.getElementById('userTable');
    const rows = table.getElementsByTagName('tr');  // Get all rows in the table
    let noMatch = true;

    // Special handling for /sem search (for Semester)
    let isSemesterSearch = filter.startsWith('/sem');
    if (isSemesterSearch) {
        // Extract the semester value after the /sem keyword (e.g., /sem 1st or /sem Spring)
        const semesterFilter = filter.replace('/sem', '').trim();  // Remove '/sem' and trim spaces
        for (let i = 0; i < rows.length; i++) {
            if (rows[i].id === 'noRecordRow') {
                continue;
            }

            const semester = rows[i].getElementsByTagName('td')[4]?.textContent || '';  // Semester column
            if (semester.toLowerCase().indexOf(semesterFilter) > -1) {
                rows[i].style.display = '';  // Show the row if it matches the semester filter
                noMatch = false;
            } else {
                rows[i].style.display = 'none';  // Hide the row if it doesn't match
            }
        }
    } else {
        // General search for all fields (without /sem)
        for (let i = 0; i < rows.length; i++) {
            if (rows[i].id === 'noRecordRow') {
                continue;
            }

            // Extracting values from the row
            const schedule_id = rows[i].getElementsByTagName('th')[0]?.textContent || '';  // Schedule Code
            const subject_code = rows[i].getElementsByTagName('td')[0]?.textContent || '';  // Subject Code
            const course_title = rows[i].getElementsByTagName('td')[1]?.textContent || '';  // Course Title
            const faculty = rows[i].getElementsByTagName('td')[2]?.textContent || '';  // Faculty
            const academic_year = rows[i].getElementsByTagName('td')[3]?.textContent || '';  // Academic Year
            const semester = rows[i].getElementsByTagName('td')[4]?.textContent || '';  // Semester
            const year_section = rows[i].getElementsByTagName('td')[5]?.textContent || '';  // Course/Year/Section

            // General search for all fields
            if (
                schedule_id.toLowerCase().indexOf(filter) > -1 ||
                subject_code.toLowerCase().indexOf(filter) > -1 ||
                course_title.toLowerCase().indexOf(filter) > -1 ||
                faculty.toLowerCase().indexOf(filter) > -1 ||
                academic_year.toLowerCase().indexOf(filter) > -1 ||
                semester.toLowerCase().indexOf(filter) > -1 ||
                year_section.toLowerCase().indexOf(filter) > -1
            ) {
                rows[i].style.display = '';  // Show the row if it matches
                noMatch = false;
            } else {
                rows[i].style.display = 'none';  // Hide the row if it doesn't match
            }
        }
    }

    // Check if there were no matches, and display a "No Record Found" message
    let noRecordRow = document.getElementById('noRecordRow');
    if (noMatch) {
        if (!noRecordRow) {
            noRecordRow = document.createElement('tr');
            noRecordRow.id = 'noRecordRow';
            noRecordRow.innerHTML = `
                <td colspan="8" style="text-align: center; color: red;">No Record Found</td>
            `;
            table.appendChild(noRecordRow);
        }
    } else {
        if (noRecordRow) {
            noRecordRow.remove();  // Remove "No Record Found" message if matches exist
        }
    }
}

document.getElementById('addScheduleForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent default form submission

    const formData = new FormData(this);

    fetch('operations/add_schedule.php', {
        method: 'POST',
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert(data.message);
                location.reload(); // Refresh the page
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
        });
});

// Set hidden values when a subject is selected
document.getElementById('subject_id').addEventListener('change', function() {
    var subjectCode = this.options[this.selectedIndex].dataset.subject_code;
    var description = this.options[this.selectedIndex].text.split(" (")[1].slice(0, -1);  // Extract full description part

    document.getElementById('subject_code').value = subjectCode;
    document.getElementById('description').value = description;
});

// Set hidden value when a course is selected
document.getElementById('cid').addEventListener('change', function() {
    var courseCode = this.options[this.selectedIndex].text.split(" ")[0];  // Assuming course code is the first part
    document.getElementById('course_code').value = courseCode;
});

// Set hidden value when a faculty is selected
document.getElementById('userID').addEventListener('change', function() {
    var username = this.options[this.selectedIndex].text.split(" (")[1].slice(0, -1);  // Get the full username
    document.getElementById('username').value = username;  // Set it to the hidden input
});

//Populate modal
document.addEventListener('DOMContentLoaded', () => {
    const updateModal = document.getElementById('updateModal');

    updateModal.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget; // Button that triggered the modal

        // Retrieve data-* attributes from the button
        const scheduleId = button.getAttribute('data-schedule_id');
        const subjectId = button.getAttribute('data-subject_id');
        const courseCode = button.getAttribute('data-course_code');
        const subjectCode = button.getAttribute('data-subject_code');
        const description = button.getAttribute('data-description');
        const username = button.getAttribute('data-username');
        const academicYear = button.getAttribute('data-academic_year');
        const semester = button.getAttribute('data-semester');
        const yearSection = button.getAttribute('data-year_section'); // This field contains both year and section
        const section = button.getAttribute('data-section'); // Separate field for section (if needed)

        // Populate the modal fields
        document.getElementById('update_schedule_id').value = scheduleId || '';
        document.getElementById('update_subject_id').value = subjectId || '';
        document.getElementById('update_cid').value = courseCode || '';
        document.getElementById('update_userID').value = username || '';
        document.getElementById('update_academic_year').value = academicYear || '';
        document.getElementById('update_semester').value = semester || '';

        // Populate the year and section fields
        if (yearSection) {
            const [year, sec] = yearSection.split('-');  // Assuming the year-section format is '1-Section A'
            document.getElementById('update_year').value = year || '';  // Set year field
            document.getElementById('update_section').value = sec || '';  // Set section field
        }

        // If section is passed as a separate field, set it (optional based on your data)
        if (section) {
            document.getElementById('update_section').value = section || '';
        }
    });
});
