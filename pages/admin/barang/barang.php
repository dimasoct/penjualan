<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Cek notifikasi dari session
if(isset($_SESSION['status']) && isset($_SESSION['nama_barang'])) {
    $status = $_SESSION['status'];
    $nama = $_SESSION['nama_barang'];
    
    // Hapus data session setelah digunakan
    unset($_SESSION['status']);
    unset($_SESSION['nama_barang']);
    
    if($status == 'added') {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                Barang <strong>$nama</strong> berhasil ditambahkan!
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    } else if($status == 'edited') {
        echo "<div class='alert alert-info alert-dismissible fade show' role='alert'>
                Barang <strong>$nama</strong> berhasil diupdate!
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}

// Query untuk mengambil data semua barang dengan join ke tabel kategori_barang
$sql_barang = "SELECT b.id_barang, b.nama_barang, k.nama_kategori, b.harga_beli, b.harga_jual 
               FROM barang b
               JOIN kategori_barang k ON b.kategori = k.id_kategori";
$result_barang = $conn->query($sql_barang);
?>
<?php
// Jumlah data per halaman dari dropdown
$limit = isset($_GET['limit']) && $_GET['limit'] !== 'all' ? (int)$_GET['limit'] : 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Hitung total data
$total_sql = "SELECT COUNT(*) as total FROM barang";
$total_result = $conn->query($total_sql);
$total_data = $total_result->fetch_assoc()['total'];

// Total halaman
$total_pages = $limit === 'all' ? 1 : ceil($total_data / $limit);

// Query dengan paginasi
$sql_barang = $limit === 'all'
    ? "SELECT b.id_barang, b.nama_barang, k.nama_kategori, b.harga_beli, b.harga_jual 
       FROM barang b
       JOIN kategori_barang k ON b.kategori = k.id_kategori"
    : "SELECT b.id_barang, b.nama_barang, k.nama_kategori, b.harga_beli, b.harga_jual 
       FROM barang b
       JOIN kategori_barang k ON b.kategori = k.id_kategori 
       LIMIT $start, $limit";
$result_barang = $conn->query($sql_barang);

?>


<?php include '../header/header.php'; ?>

<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Management</strong> Stok Barang</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <a class="btn btn-success col-auto mt-0 mb-2" href="add_barang.php">Tambah Barang</a>
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
                        <?php
                        // Cek notifikasi dari session
                        if(isset($_SESSION['status']) && isset($_SESSION['nama_barang'])) {
                            $status = $_SESSION['status'];
                            $nama = $_SESSION['nama_barang'];
                            
                            // Hapus data session setelah digunakan
                            unset($_SESSION['status']);
                            unset($_SESSION['nama_barang']);
                            
                            if($status == 'added') {
                                echo "<div class='alert alert-success fade show' role='alert' style='color: green' id='alert-message'>
                                        Barang <strong>$nama</strong> berhasil ditambahkan!
                                    </div>";
                                echo "<script>
                                        setTimeout(function() {
                                            document.getElementById('alert-message').style.display = 'none';
                                        }, 5000);
                                    </script>";
                            } else if($status == 'edited') {
                                echo "<div class='alert alert-success fade show' role='alert' style='color: green' id='alert-message'>
                                        Barang <strong>$nama</strong> berhasil diupdate!
                                    </div>";
                                echo "<script>
                                        setTimeout(function() {
                                            document.getElementById('alert-message').style.display = 'none';
                                        }, 5000);
                                    </script>";
                            }
                        }
                        ?>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="barangTable">
                                <thead>
                                    <tr>
                                        <th class="center">No</th>
                                        <th class="center">ID Barang</th>
                                        <th class="center">Nama Barang</th>
                                        <th class="center">Kategori</th>
                                        <th class="center">Harga Beli</th>
                                        <th class="center">Harga Jual</th>
                                        <th class="center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result_barang->num_rows > 0): ?>
                                        <?php $nomor = 1; ?>
                                        <?php while ($row = $result_barang->fetch_assoc()): ?>
                                            <tr>
                                                <td class="center"><?php echo $nomor++; ?></td>
                                                <td class="center"><?php echo htmlspecialchars($row['id_barang']); ?></td>
                                                <td class="center"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                                                <td class="center"><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                                <td class="center">Rp <?php echo number_format($row['harga_beli'], 0, ',', '.'); ?></td>
                                                <td class="center">Rp <?php echo number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                                                <td class="center">
                                                    <a href="edit_barang.php?id_barang=<?php echo htmlspecialchars($row['id_barang']); ?>" class="btn btn-primary btn-sm mb-3">
                                                        <i class="align-middle" data-feather="edit"></i>
                                                    </a><br>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteBarang('<?php echo htmlspecialchars($row['id_barang']); ?>')">
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
                    </div>
                    <!-- End of table-responsive -->
                </div>
            </div>
        </div>
    </div>
</main>


<script>
function deleteBarang(id_barang) {
    console.log("Delete button clicked for ID:", id_barang); // Debugging
    if (confirm("Apakah Anda yakin ingin menghapus data ini?")) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_barang.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                console.log("Server Response:", xhr.responseText); // Debugging
                if (xhr.status === 200) {
                    alert("Data berhasil dihapus.");
                    location.reload();
                } else {
                    alert("Gagal menghapus data: " + xhr.responseText);
                }
            }
        };

        xhr.send("id_barang=" + encodeURIComponent(id_barang));
    }
}

function changeEntriesPerPage(limit) {
    const url = new URL(window.location.href);
    url.searchParams.set('limit', limit);
    url.searchParams.set('page', 1); // Reset ke halaman 1
    window.location.href = url.toString();
}


document.addEventListener("DOMContentLoaded", function () {
    const entriesPerPageSelect = document.getElementById('entriesPerPage');
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('barangTable');
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