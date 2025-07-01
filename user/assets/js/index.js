// index.js

// Function to filter table
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

        const scheduleID = rows[i].getElementsByTagName('th')[0]?.textContent || '';
        const courseCode = rows[i].getElementsByTagName('td')[0]?.textContent || '';
        const courseTitle = rows[i].getElementsByTagName('td')[1]?.textContent || '';
        const fullName = rows[i].getElementsByTagName('td')[2]?.textContent || '';
        const academicYear = rows[i].getElementsByTagName('td')[3]?.textContent || '';
        const semester = rows[i].getElementsByTagName('td')[4]?.textContent || '';
        const classInfo = rows[i].getElementsByTagName('td')[5]?.textContent || '';

        if (
            scheduleID.toLowerCase().indexOf(filter) > -1 ||
            courseCode.toLowerCase().indexOf(filter) > -1 ||
            courseTitle.toLowerCase().indexOf(filter) > -1 ||
            fullName.toLowerCase().indexOf(filter) > -1 ||
            academicYear.toLowerCase().indexOf(filter) > -1 ||
            semester.toLowerCase().indexOf(filter) > -1 ||
            classInfo.toLowerCase().indexOf(filter) > -1
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
            <td colspan="8" style="text-align: center; color: red;">No Record Found</td>
            `;
            table.appendChild(noRecordRow);
        }
    } else {
        if (noRecordRow) {
            noRecordRow.remove();
        }
    }
}
