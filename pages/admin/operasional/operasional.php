<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Jumlah data per halaman dari dropdown
$limit = isset($_GET['limit']) && $_GET['limit'] !== 'all' ? (int)$_GET['limit'] : 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Hitung total data
$total_sql = "SELECT COUNT(*) as total FROM operasional";
$total_result = $conn->query($total_sql);
$total_data = $total_result->fetch_assoc()['total'];

// Total halaman
$total_pages = $limit === 'all' ? 1 : ceil($total_data / $limit);

// Query dengan paginasi
$sql_biaya = $limit === 'all' 
    ? "SELECT id_biaya, keterangan, jumlah_biaya, tanggal_biaya FROM operasional"
    : "SELECT id_biaya, keterangan, jumlah_biaya, tanggal_biaya FROM operasional LIMIT $start, $limit";
$result_biaya = $conn->query($sql_biaya);
?>

<?php include '../header/header.php'; ?>

<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Biaya</strong> Operasional</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <a class="btn btn-success col-auto mt-0 mb-2" href="add_operasional.php">Tambah Biaya</a>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <label for="entriesPerPage" class="me-2">Show:</label>
                                <select id="entriesPerPage" class="form-select form-select-sm d-inline w-auto" onchange="changeEntriesPerPage(this.value)">
                                    <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
                                    <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                                    <option value="15" <?php echo $limit == 15 ? 'selected' : ''; ?>>15</option>
                                    <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
                                    <option value="all" <?php echo $limit == 'all' ? 'selected' : ''; ?>>All</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center">
                                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search...">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="operasionalTable">
                                <thead>
                                    <tr>
                                        <th class="center">No</th>
                                        <th class="center">ID Biaya</th>
                                        <th class="center">Keterangan</th>
                                        <th class="center">Jumlah Biaya</th>
                                        <th class="center">Tanggal Biaya</th>
                                        <th class="center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <meta charset="UTF-8">
                                    <?php if ($result_biaya->num_rows > 0): ?>
                                        <?php $nomor = $start + 1; ?>
                                        <?php while ($row = $result_biaya->fetch_assoc()): ?>
                                            <tr>
                                                <td class="center"><?php echo $nomor++; ?></td>
                                                <td class="center"><?php echo htmlspecialchars($row['id_biaya']); ?></td>
                                                <td class="center"><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                                <td class="center">Rp <?php echo number_format($row['jumlah_biaya'], 0, ',', '.'); ?></td>
                                                <td class="center"><?php echo htmlspecialchars($row['tanggal_biaya']); ?></td>
                                                <td class="center">
                                                    <a href="edit_operasional.php?id_biaya=<?php echo htmlspecialchars($row['id_biaya']); ?>" class="btn btn-primary btn-sm mb-3">
                                                        <i class="align-middle" data-feather="edit"></i>
                                                    </a><br>
                                                    <button class="btn btn-danger btn-sm mb-3" onclick="deleteOperasional(<?php echo htmlspecialchars($row['id_biaya']); ?>)">
                                                        <i class="align-middle" data-feather="trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8">No data found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span>
                                    Showing <?php echo $limit === 'all' ? 1 : $start + 1; ?> to 
                                    <?php echo $limit === 'all' ? $total_data : min($start + $limit, $total_data); ?> of 
                                    <?php echo $total_data; ?> entries
                                </span>

                                <nav>
                                <ul class="pagination pagination-sm mb-0" style="display: flex; justify-content: flex-start; gap: 5px; list-style: none; padding: 0; margin: 0;">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item" style="list-style: none;">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>" style="display: inline-block; padding: 5px 10px; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #007bff; background-color: #fff;">&laquo; Previous</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>" style="list-style: none;">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>" style="display: inline-block; padding: 5px 10px; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #007bff; background-color: <?php echo ($i == $page) ? '#007bff' : '#fff'; ?>; color: <?php echo ($i == $page) ? '#fff' : '#007bff'; ?>;"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item" style="list-style: none;">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>" style="display: inline-block; padding: 5px 10px; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #007bff; background-color: #fff;">Next &raquo;</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <!-- End of table-responsive -->
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    function deleteOperasional(id_biaya) {
    // Konfirmasi sebelum penghapusan
    if (confirm("Apakah Anda yakin ingin menghapus data ini?")) {
        // Buat permintaan AJAX
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_operasional.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Jika berhasil, tampilkan pesan dan refresh halaman
                    alert("Data berhasil dihapus.");
                    location.reload();
                } else {
                    // Jika gagal, tampilkan pesan error
                    alert("Gagal menghapus data: " + xhr.responseText);
                }
            }
        };

        // Kirim permintaan dengan data id_biaya
        xhr.send("id_biaya=" + encodeURIComponent(id_biaya));
    }
}

function changeEntriesPerPage(limit) {
    const url = new URL(window.location.href);
    url.searchParams.set('limit', limit);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
}

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('operasionalTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let rowMatch = false;

            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                if (cell.textContent.toLowerCase().includes(searchTerm)) {
                    rowMatch = true;
                    break;
                }
            }

            row.style.display = rowMatch ? '' : 'none';
        }
    }

    searchInput.addEventListener('input', filterTable);
});
</script>

<?php include '../footer/footer.php'; ?>
