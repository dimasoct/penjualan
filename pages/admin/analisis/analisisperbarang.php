<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Set default date range to current month if not specified
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Jumlah data per halaman dari dropdown
$limit = isset($_GET['limit']) && $_GET['limit'] !== 'all' ? (int)$_GET['limit'] : 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Hitung total data
$total_sql = "SELECT COUNT(DISTINCT b.id_barang) as total FROM barang b";
$total_result = $conn->query($total_sql);
$total_data = $total_result->fetch_assoc()['total'];

// Total halaman
$total_pages = $limit === 'all' ? 1 : ceil($total_data / $limit);

// Query untuk mengambil data analisis per barang dengan filter tanggal
$sql = "SELECT 
            b.nama_barang,
            (SELECT COALESCE(SUM(stok_masuk), 0) 
             FROM stok_masuk sm2 
             WHERE sm2.id_barang = b.id_barang 
             AND sm2.tanggal_masuk BETWEEN ? AND ?) as total_stok_masuk,
            (SELECT COALESCE(SUM(stok_masuk * harga_beli), 0)
             FROM stok_masuk sm3
             WHERE sm3.id_barang = b.id_barang
             AND sm3.tanggal_masuk BETWEEN ? AND ?) as total_modal,
            (SELECT COALESCE(SUM(stok_keluar), 0)
             FROM stok_keluar sk2
             WHERE sk2.id_barang = b.id_barang
             AND sk2.tanggal_keluar BETWEEN ? AND ?) as total_stok_keluar,
            (SELECT COALESCE(SUM(stok_keluar * harga_jual), 0)
             FROM stok_keluar sk3
             WHERE sk3.id_barang = b.id_barang
             AND sk3.tanggal_keluar BETWEEN ? AND ?) as total_penjualan,
            (SELECT COALESCE(SUM(stok_keluar * harga_jual), 0)
             FROM stok_keluar sk4
             WHERE sk4.id_barang = b.id_barang
             AND sk4.tanggal_keluar BETWEEN ? AND ?) -
            (SELECT COALESCE(SUM(stok_masuk * harga_beli), 0)
             FROM stok_masuk sm4
             WHERE sm4.id_barang = b.id_barang
             AND sm4.tanggal_masuk BETWEEN ? AND ?) as keuntungan
        FROM barang b";

if ($limit !== 'all') {
    $sql .= " LIMIT ?, ?";
}

$stmt = $conn->prepare($sql);
if ($limit !== 'all') {
    $stmt->bind_param("ssssssssssssii", 
        $start_date, $end_date,
        $start_date, $end_date,
        $start_date, $end_date,
        $start_date, $end_date,
        $start_date, $end_date,
        $start_date, $end_date,
        $start, $limit
    );
} else {
    $stmt->bind_param("ssssssssssss", 
        $start_date, $end_date,
        $start_date, $end_date,
        $start_date, $end_date,
        $start_date, $end_date,
        $start_date, $end_date,
        $start_date, $end_date
    );
}
$stmt->execute();
$result = $stmt->get_result();

// Hitung jumlah baris yang ditampilkan
$displayed_rows = $result->num_rows;
$start_entry = ($page - 1) * ($limit == 'all' ? $total_data : $limit) + 1;
$end_entry = $start_entry + $displayed_rows - 1;
?>

<?php include '../header/header.php';?>

<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Analisis</strong> Per Barang</h1>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <form method="GET" class="mb-4">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="start_date">Tanggal Mulai:</label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="end_date">Tanggal Akhir:</label>
                                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                                        </div>
                                    </div>
                                
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label><br>
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                            <a href="?start_date=<?php echo date('Y-m-01'); ?>&end_date=<?php echo date('Y-m-t'); ?>" class="btn btn-secondary">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
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
                            <table class="table table-bordered" id="analisisTable">
                                <thead>
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th class="text-center">Nama Barang</th>
                                        <th class="text-center">Total Stok Masuk</th>
                                        <th class="text-center">Total Modal (Rp)</th>
                                        <th class="text-center">Total Stok Keluar</th>
                                        <th class="text-center">Total Penjualan (Rp)</th>
                                        <th class="text-center">Keuntungan (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php $no = $start + 1; ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td class="text-center"><?php echo $no++; ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                                                <td class="text-center"><?php echo number_format($row['total_stok_masuk']); ?></td>
                                                <td class="text-center">Rp <?php echo number_format($row['total_modal'], 0, ',', '.'); ?></td>
                                                <td class="text-center"><?php echo number_format($row['total_stok_keluar']); ?></td>
                                                <td class="text-center">Rp <?php echo number_format($row['total_penjualan'], 0, ',', '.'); ?></td>
                                                <td class="text-center">Rp <?php echo number_format($row['keuntungan'], 0, ',', '.'); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Tidak ada data</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

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
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    function changeEntriesPerPage(limit) {
    const url = new URL(window.location.href);
    url.searchParams.set('limit', limit);
    url.searchParams.set('page', 1); // Reset ke halaman 1
    window.location.href = url.toString();
}
document.addEventListener("DOMContentLoaded", function () {
    const entriesPerPageSelect = document.getElementById('entriesPerPage');
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('analisisTable');
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

<?php include '../footer/footer.php';?>
