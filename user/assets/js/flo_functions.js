$(document).ready(function(){
    $(".editable").on("blur", function(){
    var id = $(this).data("id");
    var column = $(this).data("column");
    var value = $(this).text();
    var cell = $(this);

    $.ajax({
        url: column === "p_timeline" ? "/cqi/user/operations/update_faps.php" : "/cqi/user/operations/update_flos.php",
        type: "POST",
        data: { id: id, column: column, value: value },
        success: function(response){
            if (response.trim() === "Success") {
                cell.css("background-color", "#c8e6c9");
                setTimeout(() => cell.css("background-color", ""), 1000);
            } else {
                alert(response);
            }
        }
    });
});

    // === Delete Row Handler ===

    $(".delete-btn").on("click", function () {
        const id = $(this).data("id");
        const table = $(this).data("table");
        const row = $(this).closest("tr");

        if (confirm("Are you sure?")) {
            $.ajax({
                url: "/cqi/user/operation/delete_fcos_flos.php",  // corrected path
                type: "POST",
                data: { id: id, table: table },
                success: function (response) {
                    if (response.trim() === "Success") {
                        row.fadeOut("slow", function () {
                            $(this).remove();
                        });
                    }
                }
            });
        }
    });

    // === Summarize CO Attainment Handler ===

    
});
