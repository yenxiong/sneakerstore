<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: rgb(14, 82, 53);
            padding: 20px;
            color: #333;
        }

        h2 {
            text-align: center;
            color: #fff;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 0 10px rgb(36, 136, 116);
        }

        th, td {
            padding: 12px 16px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #222;
            color: white;
            cursor: pointer;
        }

        tr {
            background-color: rgb(36, 96, 224);
            color: white;
        }

        td button {
            background-color: #d9534f;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        td button:hover {
            background-color: #c9302c;
        }

        .expired {
            background-color: #f8d7da !important;
        }

        .search-container {
            margin-bottom: 20px;
            text-align: center;
        }

        .search-container input, .search-container select {
            padding: 10px;
            width: 300px;
            margin-bottom: 20px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination button {
            padding: 5px 10px;
            margin: 0 2px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }

        .pagination .active {
            background-color: #222;
            color: white;
        }

        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }

            tr {
                margin-bottom: 15px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                background-color: white;
                border-radius: 6px;
            }

            td {
                text-align: left;
                padding: 10px 20px;
                border: none;
                position: relative;
            }

            td::before {
                content: attr(data-label);
                font-weight: bold;
                position: absolute;
                left: 10px;
                top: 10px;
                color: #555;
            }

            th {
                display: none;
            }
        }
    </style>
</head>
<body>
    <h2>Payment Records</h2>

    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Search by Name, Phone, Card Number..." onkeyup="searchTable()">
    </div>

    <div class="search-container">
        <select id="expiryFilter" onchange="filterByExpiry()">
            <option value="">Filter by Expiry Year</option>
        </select>
    </div>

    <button onclick="exportTableToCSV()" style="margin: 0 auto; display: block; padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Export CSV</button>

    <table id="paymentTable">
        <thead>
            <tr>
                <th onclick="sortTable(0)">No.</th>
                <th onclick="sortTable(1)">Name</th>
                <th onclick="sortTable(2)">Phone</th>
                <th onclick="sortTable(3)">Address</th>
                <th onclick="sortTable(4)">Card Number</th>
                <th onclick="sortTable(5)">Expiry Month</th>
                <th onclick="sortTable(6)">Expiry Year</th>
                <th onclick="sortTable(7)">CVV</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <!-- PHP will render content here -->
        </tbody>
    </table>

    <div class="pagination" id="pagination"></div>

    <script>
        const table = document.getElementById("paymentTable");
        let tableData = Array.from(document.querySelectorAll("#tableBody tr"));
        const rowsPerPage = 5;
        let currentPage = 1;

        function sortTable(n) {
            let rows = [...tableData];
            const dir = table.getAttribute('data-sort-dir') === 'asc' ? 'desc' : 'asc';
            rows.sort((a, b) => {
                const x = a.cells[n].textContent.toLowerCase();
                const y = b.cells[n].textContent.toLowerCase();
                return dir === 'asc' ? x.localeCompare(y) : y.localeCompare(x);
            });
            table.setAttribute('data-sort-dir', dir);
            tableData = rows;
            renderPage(currentPage);
        }

        function renderPage(page) {
            currentPage = page;
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const paginated = tableData.slice(start, end);
            const tbody = document.getElementById("tableBody");
            tbody.innerHTML = "";
            paginated.forEach(row => tbody.appendChild(row));
            renderPagination();
        }

        function renderPagination() {
            const pageCount = Math.ceil(tableData.length / rowsPerPage);
            const container = document.getElementById("pagination");
            container.innerHTML = "";
            for (let i = 1; i <= pageCount; i++) {
                const btn = document.createElement("button");
                btn.textContent = i;
                btn.className = (i === currentPage) ? "active" : "";
                btn.onclick = () => renderPage(i);
                container.appendChild(btn);
            }
        }

        function searchTable() {
            const query = document.getElementById("searchInput").value.toLowerCase();
            tableData = Array.from(document.querySelectorAll("#paymentTable tbody tr")).filter(row => {
                return Array.from(row.cells).some(cell => cell.textContent.toLowerCase().includes(query));
            });
            renderPage(1);
        }

        function filterByExpiry() {
            const year = document.getElementById("expiryFilter").value;
            tableData = Array.from(document.querySelectorAll("#paymentTable tbody tr")).filter(row => {
                return year === "" || row.cells[6].textContent === year;
            });
            renderPage(1);
        }

        function populateExpiryFilter() {
            const select = document.getElementById("expiryFilter");
            const years = [...new Set(Array.from(document.querySelectorAll("#paymentTable tbody tr")).map(row => row.cells[6].textContent))].sort();
            years.forEach(year => {
                const option = document.createElement("option");
                option.value = year;
                option.textContent = year;
                select.appendChild(option);
            });
        }

        function exportTableToCSV() {
            const rows = document.querySelectorAll("#paymentTable tr");
            const csv = [];
            for (const row of rows) {
                const cols = row.querySelectorAll("th, td");
                const rowData = Array.from(cols).map(col => '"' + col.innerText.replace(/"/g, '""') + '"');
                csv.push(rowData.join(","));
            }
            const blob = new Blob([csv.join("\n")], { type: 'text/csv' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'payment_records.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        populateExpiryFilter();
        renderPage(currentPage);
    </script>
</body>
</html>
