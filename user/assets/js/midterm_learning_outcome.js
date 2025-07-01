// midterm_learning_outcome.js

function updateMLO(id, column, value) {
    $.ajax({
        url: '/cqi/user/operations/update_mlo.php', // Path from JS file to PHP script
        type: 'POST',
        data: { id: id, column: column, value: value },
        success: function (response) {
            console.log("Server Response:", response);
            if (response.trim() !== "Success") {
                alert('Failed to save changes: ' + response);
            }
        },
        error: function (xhr, status, error) {
            alert('AJAX error: ' + error);
        }
    });
}

$(document).ready(function() {
    $(document).on("blur", ".editable", function() {
        let id = $(this).data("id");
        let column = $(this).data("column");
        let value = $(this).text().trim();
        updateMLO(id, column, value);
    });
});

function updateMLOS(schedule_id) {
    if (!confirm("Are you sure you want to update MLOS?")) return;

    $.ajax({
        url: '/cqi/user/operations/update_mlo.php',
        type: 'POST',
        data: { schedule_id: schedule_id },
        success: function(response) {
            alert(response);
            location.reload();
        },
        error: function(xhr, status, error) {
            alert('Error updating MLOS: ' + error);
        }
    });
}

$(document).ready(function () {
    // Trigger update on blur of editable cells
    $(document).on("blur", ".editable", function () {
        const id = $(this).data("id");
        const column = $(this).data("column");
        const value = $(this).text().trim();
        if (id && column) {
            updateMLO(id, column, value);
        }
    });

    // Highlight NOT ATTAINED remarks
    $("td:nth-child(7)").each(function () {
        if ($(this).text().trim() === "NOT ATTAINED") {
            $(this).css({
                "color": "red",
                "font-weight": "bold",
                "font-style": "italic"
            });
        }
    });
});
