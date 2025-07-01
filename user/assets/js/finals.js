document.addEventListener("DOMContentLoaded", function () {
    const tableBody = document.getElementById("finalrecords");

    tableBody.addEventListener("change", function (e) {
        if (e.target.type === "checkbox") {
            const checkbox = e.target;
            const row = checkbox.closest("tr");
            const checkboxes = row.querySelectorAll("input[type='checkbox']");
            let qsum = 0;

            // Count how many boxes are checked for the current student (row)
            checkboxes.forEach(cb => {
                if (cb.checked) qsum++;
            });

            // Update qsum in the last cell of the row (Total per student)
            const totalCell = row.querySelector("td.sticky-right");
            if (totalCell) {
                totalCell.textContent = qsum;
            }

            // Update the grand total (total sum of all student qsum values)
            updateGrandTotal();

            // Send AJAX request to save the change
            const questionNumber = checkbox.value;
            const isChecked = checkbox.checked ? 1 : 0;
            const studentId = row.querySelector("input[name='students[]']").value;

            // Make an AJAX request to save the checked/uncheck data to the database
            fetch("operations/final_records.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `student_id=${studentId}&question=${questionNumber}&checked=${isChecked}`,
            })
            .then(response => response.text())
            .then(data => {
                console.log("Saved:", data);
            })
            .catch(error => {
                console.error("Error saving:", error);
            });
        }
    });

    // Function to update the grand total
    function updateGrandTotal() {
        let grandTotal = 0;
        const rows = tableBody.querySelectorAll("tr");
        rows.forEach(row => {
            const totalCell = row.querySelector("td.sticky-right");
            if (totalCell) {
                grandTotal += parseInt(totalCell.textContent || "0");
            }
        });
    }
});
