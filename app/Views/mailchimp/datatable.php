
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Table Example</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
</head>
<body>

<div style="margin: 20px;">
    <h2>Data Table with Search Filter</h2>

    <table id="dataTable" class="display" style="text-align: center;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Member Id</th>
                <th>Email</th>
                <th>Status</th>
                <!-- Add other columns as needed -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($db_members as $row): ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= $row['member_id']; ?></td>
                    <td><?= $row['email']; ?></td>
                    <td><?= $row['member_status']; ?></td>
                    <!-- Add other cells corresponding to the columns -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#dataTable').DataTable();

        // Add search functionality for ID column
        $('#dataTable').DataTable().columns().every(function () {
            var column = this;

            // Apply the search functionality to the ID column
            if (column.index() === 0) {
                // Input for the search filter
                var input = $('<input type="text" placeholder="Search ID" />')
                    .appendTo($(column.header()))
                    .on('keyup', function () {
                        column.search(this.value).draw();
                    });
            }
        });
    });
</script>

</body>
</html>