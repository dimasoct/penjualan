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

// Ambil nilai pencarian
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Hitung total data dengan filter pencarian
$total_sql = "SELECT COUNT(*) as total FROM stok_masuk sm
              JOIN barang b ON sm.id_barang = b.id_barang
              WHERE sm.id_stok_masuk LIKE ? OR b.nama_barang LIKE ? OR sm.asal_barang LIKE ?";
$total_stmt = $conn->prepare($total_sql);
$searchWildcard = "%" . $searchTerm . "%";
$total_stmt->bind_param("sss", $searchWildcard, $searchWildcard, $searchWildcard);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_data = $total_result->fetch_assoc()['total'];

// Total halaman
$total_pages = $limit === 'all' ? 1 : ceil($total_data / $limit);

// Query untuk mengambil data stok masuk dengan filter pencarian
$sql_stok_masuk = $limit === 'all'
    ? "SELECT sm.id_stok_masuk, sm.id_barang, sm.stok_masuk, sm.tanggal_masuk, sm.asal_barang, 
             b.nama_barang, b.harga_beli, (sm.stok_masuk * b.harga_beli) AS total_harga_beli
       FROM stok_masuk sm
       JOIN barang b ON sm.id_barang = b.id_barang
       WHERE sm.id_stok_masuk LIKE ? OR b.nama_barang LIKE ? OR sm.asal_barang LIKE ?"
    : "SELECT sm.id_stok_masuk, sm.id_barang, sm.stok_masuk, sm.tanggal_masuk, sm.asal_barang, 
             b.nama_barang, b.harga_beli, (sm.stok_masuk * b.harga_beli) AS total_harga_beli
       FROM stok_masuk sm
       JOIN barang b ON sm.id_barang = b.id_barang
       WHERE sm.id_stok_masuk LIKE ? OR b.nama_barang LIKE ? OR sm.asal_barang LIKE ?
       LIMIT $start, $limit";

$stmt = $conn->prepare($sql_stok_masuk);
$stmt->bind_param("sss", $searchWildcard, $searchWildcard, $searchWildcard);
$stmt->execute();
$result_stok_masuk = $stmt->get_result();
?>

<?php include '../header/header.php'; ?>

<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Management</strong> Stok Masuk</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <a class="btn btn-success col-auto mt-0 mb-2" href="add_stok_masuk.php">Tambah Stok Masuk</a>
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
                            <table class="table table-bordered" id="stokmasukTable">
                                <thead>
                                    <tr>
                                        <th class="center">No</th>
                                        <th class="center">ID Stok Masuk</th>
                                        <th class="center">Nama Barang</th>
                                        <th class="center">Stok Masuk</th>
                                        <th class="center">Tanggal Masuk</th>
                                        <th class="center">Asal Barang</th>
                                        <th class="center">Harga Beli</th>
                                        <th class="center">Total Harga Beli</th>
                                        <th class="center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result_stok_masuk->num_rows > 0): ?>
                                        <?php $nomor = 1; ?>
                                        <?php while ($row = $result_stok_masuk->fetch_assoc()): ?>
                                            <tr>
                                                <td class="center"><?php echo $nomor++; ?></td>
                                                <td class="center"><?php echo htmlspecialchars($row['id_stok_masuk']); ?></td>
                                                <td class="center"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                                                <td class="center"><?php echo htmlspecialchars($row['stok_masuk']); ?></td>
                                                <td class="center"><?php echo htmlspecialchars($row['tanggal_masuk']); ?></td>
                                                <td class="center"><?php echo htmlspecialchars($row['asal_barang']); ?></td>
                                                <td class="center"><?php echo "Rp " . number_format($row['harga_beli'], 0, ',', '.'); ?></td>
                                                <td class="center"><?php echo "Rp " . number_format($row['total_harga_beli'], 0, ',', '.'); ?></td>
                                                <td class="center">
                                                    <a href="edit_stok_masuk.php?id_stok_masuk=<?php echo htmlspecialchars($row['id_stok_masuk']); ?>" class="btn btn-primary btn-sm">
                                                        <i class="align-middle" data-feather="edit"></i>
                                                    </a><br><br>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteStokMasuk('<?php echo htmlspecialchars($row['id_stok_masuk']); ?>')">
                                                        <i class="align-middle" data-feather="trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10" class="text-center">No data found</td>
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
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>" 
                                                style="display: inline-block; padding: 5px 10px; border: 1px solid #dee2e6; border-radius: 4px; 
                                                        text-decoration: none; color: #007bff; background-color: #fff;">&laquo; Previous</a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>" style="list-style: none;">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>" 
                                                style="display: inline-block; padding: 5px 10px; border: 1px solid #dee2e6; border-radius: 4px; 
                                                        text-decoration: none; color: #007bff; background-color: <?php echo ($i == $page) ? '#007bff' : '#fff'; ?>;
                                                        color: <?php echo ($i == $page) ? '#fff' : '#007bff'; ?>;"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item" style="list-style: none;">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>" 
                                                style="display: inline-block; padding: 5px 10px; border: 1px solid #dee2e6; border-radius: 4px; 
                                                        text-decoration: none; color: #007bff; background-color: #fff;">Next &raquo;</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function deleteStokMasuk(id_stok_masuk) {
    if (confirm("Apakah Anda yakin ingin menghapus data stok masuk ini?")) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_stok_masuk.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    alert("Stok masuk berhasil dihapus.");
                    location.reload();
                } else {
                    alert("Gagal menghapus data stok masuk: " + xhr.responseText);
                }
            }
        };

        xhr.send("id_stok_masuk=" + encodeURIComponent(id_stok_masuk));
    }
}


function changeEntriesPerPage(limit) {
    const url = new URL(window.location.href);
    url.searchParams.set('limit', limit);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
}

document.addEventListener("DOMContentLoaded", function () {
    const entriesPerPageSelect = document.getElementById('entriesPerPage');
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('stokmasukTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        let entryCount = entriesPerPageSelect.value === 'all' ? rows.length : parseInt(entriesPerPageSelect.value);
        let displayedRows = 0;

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

            if (rowMatch && displayedRows < entryCount) {
                row.style.display = '';
                displayedRows++;
            } else {
                row.style.display = 'none';
            }
        }
    }

    entriesPerPageSelect.addEventListener('change', filterTable);
    searchInput.addEventListener('input', filterTable);
    filterTable(); // Initial call to set up the table
});

</script>

<?php include '../footer/footer.php'; ?>
