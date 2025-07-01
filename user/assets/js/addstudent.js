function addRow() {
    let table = document.getElementById("listofstudents");
    let row = table.insertRow();
    row.innerHTML = `
        <td><input type="text" name="studID[]" required></td>
        <td><input type="text" name="fullname[]" required></td>
        <td><button type="button" onclick="deleteRow(this)">Delete</button></td>
    `;
}

function deleteRow(btn) {
    let row = btn.parentNode.parentNode;
    row.parentNode.removeChild(row);
}

function convertExcelToCSV(schedule_id) {
    const fileInput = document.getElementById('excelFile');
    const file = fileInput.files[0];
    if (!file) return alert("Please select an Excel file.");

    const reader = new FileReader();
    reader.onload = function(e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });

        const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
        const csv = XLSX.utils.sheet_to_csv(firstSheet);

        // Create a form to POST data
        const form = document.createElement('form');
        form.method = 'POST';
        form.enctype = 'multipart/form-data';
        form.action =   'operations/addstudentexcel.php';

        const scheduleInput = document.createElement('input');
        scheduleInput.type = 'hidden';
        scheduleInput.name = 'schedule_id';
        scheduleInput.value = schedule_id;

        const csvInput = document.createElement('input');
        csvInput.type = 'hidden';
        csvInput.name = 'csv_data';
        csvInput.value = csv;

        form.appendChild(scheduleInput);
        form.appendChild(csvInput);
        document.body.appendChild(form);
        form.submit();
    };
    reader.readAsArrayBuffer(file);
}
