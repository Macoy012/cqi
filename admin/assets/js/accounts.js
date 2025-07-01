function filterTable() {
    const searchBar = document.getElementById('searchBar');
    const filter = searchBar.value.trim().toLowerCase();
    const table = document.getElementById('userTable');
    const rows = table.getElementsByTagName('tr');
    let noMatch = true;

    for (let i = 0; i < rows.length; i++) {
        if (rows[i].id === 'noRecordRow') continue;

        const userID = rows[i].getElementsByTagName('th')[0]?.textContent.trim().toLowerCase() || '';
        const fullName = rows[i].getElementsByTagName('td')[0]?.textContent.trim().toLowerCase() || '';
        const email = rows[i].getElementsByTagName('td')[2]?.textContent.trim().toLowerCase() || '';
        const userType = rows[i].getElementsByTagName('td')[3]?.textContent.trim().toLowerCase() || '';
        const status = rows[i].getElementsByTagName('td')[4]?.textContent.trim().toLowerCase() || ''; // Ensure lowercase

        // Handle strict matching for "active" or "inactive" status
        let matchesStatus = false;
        if (filter === "active" || filter === "inactive") {
            matchesStatus = status === filter; // Strict match
        } else {
            matchesStatus = status.includes(filter); // Allow partial match for other searches
        }

        // Check if the row matches any of the columns
        if (
            userID.includes(filter) ||
            fullName.includes(filter) ||
            email.includes(filter) ||
            userType.includes(filter) ||
            matchesStatus // Use specialized status match
        ) {
            rows[i].style.display = ''; // Show matching row
            noMatch = false;

            // Set font color to red for "inactive" status
            if (status === "inactive") {
                rows[i].style.color = 'red'; // Apply red font color
            } else {
                rows[i].style.color = ''; // Reset font color for other statuses
            }
        } else {
            rows[i].style.display = 'none'; // Hide non-matching row
        }
    }

    // Handle "No Record Found" row
    let noRecordRow = document.getElementById('noRecordRow');
    if (noMatch) {
        if (!noRecordRow) {
            noRecordRow = document.createElement('tr');
            noRecordRow.id = 'noRecordRow';
            noRecordRow.innerHTML = `
                <td colspan="6" style="text-align: center; color: red;">No Record Found</td>
            `;
            table.appendChild(noRecordRow);
        }
    } else if (noRecordRow) {
        noRecordRow.remove();
    }
}

// Add Account
document.getElementById('addAccountForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const submitButton = document.querySelector('#addAccountForm button[type="submit"]');
    submitButton.disabled = true;

    const userID = document.getElementById('userID').value;
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const usertype = document.getElementById('userType').value;

    // Automatically set status to "Active"
    const status = "Active";

    // Check if the email ends with '@cvsu.edu.ph'
    const emailRegex = /^[a-zA-Z0-9._%+-]+@cvsu\.edu\.ph$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address with the domain @cvsu.edu.ph');
        submitButton.disabled = false;
        return;
    }

    const data = { userID, username, email, password, usertype, status };

    // Send data via AJAX
    fetch('operations/add_account.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Account added successfully!');
                location.reload();
            } else {
                alert('Error adding account: ' + data.message);
            }
        })
        .catch(error => alert('Error: ' + error.message))
        .finally(() => (submitButton.disabled = false));
});

// Populate modal with selected data when 'Update' button is clicked
const updateModal = document.getElementById('updateModal');
updateModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;

    // Get data from the button
    const userID = button.getAttribute('data-id');
    const username = button.getAttribute('data-username');
    const email = button.getAttribute('data-email');
    const usertype = button.getAttribute('data-usertype');
    const status = button.getAttribute('data-status');
    const password = button.getAttribute('data-password');

    // Populate the modal fields
    document.getElementById('modalUserID').value = userID;
    document.getElementById('modalUsername').value = username;
    document.getElementById('modalEmail').value = email;
    document.getElementById('modalUserType').value = usertype;
    document.getElementById('modalStatus').value = status;

    // Handle password field
    const modalPassword = document.getElementById('modalPassword');
    const actualPassword = document.getElementById('actualPassword');

    if (password) {
        modalPassword.value = ''; // Leave empty for security
        actualPassword.value = password; // Store hashed password in a hidden field
        document.getElementById('passwordMessage').textContent = 'Password cannot be shown for security reasons';
    } else {
        modalPassword.value = ''; // No password set
        actualPassword.value = '';
        document.getElementById('passwordMessage').textContent = '';
    }
});

// Populate the Assign Department Modal
document.getElementById('assignDepartmentModal').addEventListener('show.bs.modal', (event) => {
    const button = event.relatedTarget; // Button that triggered the modal
    const userId = button.getAttribute('data-id');
    const username = button.getAttribute('data-username');

    // Populate modal fields
    document.getElementById('assign_userID').value = userId;
    document.getElementById('assign_username').value = username;
});

//ASSIGNING DEPARTMENT OF PROGRAM HEAD
document.getElementById("assignDepartmentForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch("operations/assign_department.php", {
        method: "POST",
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert("Department assigned successfully.");
                location.reload(); // Optionally, reload or update the table dynamically.
            } else {
                alert(data.message);
            }
        })
        .catch((error) => console.error("Error:", error));
});