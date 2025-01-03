    <?php
    function addStokMasuk($id_barang, $stok_masuk, $tanggal_masuk, $asal_barang) {
        include '../../../database/db.php';
    
        $conn = new mysqli($host, $username, $password, $dbname);
    
        // Periksa koneksi
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    
        // Ambil harga_beli dari tabel barang berdasarkan id_barang
        $sql_harga = "SELECT harga_beli FROM barang WHERE id_barang = ?";
        $stmt_harga = $conn->prepare($sql_harga);
        $stmt_harga->bind_param("s", $id_barang);
        $stmt_harga->execute();
        $stmt_harga->store_result();
        $stmt_harga->bind_result($harga_beli);
    
        // Jika harga_beli ditemukan
        if ($stmt_harga->fetch()) {
            // Generate ID stok masuk (contoh: SM-xxxx)
            $id_stok_masuk = 'SM-' . strtoupper(bin2hex(random_bytes(2))); // Generate random 4-bit hex
    
            // Query untuk memasukkan stok masuk ke tabel stok_masuk
            $sql = "INSERT INTO stok_masuk (id_stok_masuk, id_barang, stok_masuk, tanggal_masuk, harga_beli, asal_barang) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    
            // Siapkan statement dan bind parameter
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssisss", $id_stok_masuk, $id_barang, $stok_masuk, $tanggal_masuk, $harga_beli, $asal_barang);
    
            // Eksekusi untuk memasukkan data ke tabel stok_masuk
            if ($stmt->execute()) {
                // Setelah stok masuk tercatat, update tabel stok dengan menambah stok
                $sql_update_stok = "UPDATE stok SET stok = stok + ? WHERE id_barang = ?";
                $stmt_update_stok = $conn->prepare($sql_update_stok);
                $stmt_update_stok->bind_param("is", $stok_masuk, $id_barang);
    
                // Eksekusi query untuk update stok
                if ($stmt_update_stok->execute()) {
                    header("Location: stok_masuk.php");  // Redirect ke halaman stok_masuk setelah berhasil ditambahkan
                    exit();
                } else {
                    echo "Error updating stock: " . $conn->error;
                }
            } else {
                echo "Error inserting stock masuk: " . $conn->error;
            }
        } else {
            echo "Harga beli tidak ditemukan untuk barang dengan ID: $id_barang";
        }
    
        $stmt->close();
        $stmt_harga->close();
        $conn->close();
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id_barang = $_POST['id_barang'];
        $stok_masuk = $_POST['stok_masuk'];
        $tanggal_masuk = $_POST['tanggal_masuk'];
        $asal_barang = $_POST['asal_barang'];
    
        // Panggil fungsi untuk menambahkan stok masuk ke database
        addStokMasuk($id_barang, $stok_masuk, $tanggal_masuk, $asal_barang);
    }
    
    // Generate ID stok masuk (contoh: SM-xxxx) untuk ditampilkan di form
    $id_stok_masuk = 'SM-' . strtoupper(bin2hex(random_bytes(2)));
    
    // Query untuk mendapatkan barang untuk dropdown
    include '../../../database/db.php';
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql_barang = "SELECT id_barang, nama_barang FROM barang ORDER BY nama_barang ASC";
    $result_barang = $conn->query($sql_barang);
    
    // Query untuk mendapatkan stok barang
    $sql_stok = "SELECT id_barang, stok FROM stok";
    $result_stok = $conn->query($sql_stok);
    
    // Prepare stok data untuk JavaScript
    $stok_data = [];
    if ($result_stok->num_rows > 0) {
        while ($stok_row = $result_stok->fetch_assoc()) {
            $stok_data[$stok_row['id_barang']] = $stok_row['stok'];
        }
    }
    
    // Query untuk mendapatkan harga_beli dari tabel barang
    $sql_harga_beli = "SELECT id_barang, harga_beli FROM barang";
    $result_harga_beli = $conn->query($sql_harga_beli);
    
    // Prepare harga_beli data untuk JavaScript
    $harga_beli_data = [];
    if ($result_harga_beli->num_rows > 0) {
        while ($row = $result_harga_beli->fetch_assoc()) {
            $harga_beli_data[$row['id_barang']] = $row['harga_beli'];
        }
    }
    
    $conn->close();
    ?>
    
    <?php include '../header/header.php'; ?>
    
    <main class="content">
        <div class="container-fluid p-0">
            <h1 class="h3 mb-3"><strong>Add</strong> Stok Masuk</h1>
            <div class="row">
                <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <form method="post" enctype="multipart/form-data" action="">
                                
                                <!-- ID Stok Masuk (Disabled) -->
                                <div class="mb-3">
                                    <label class="form-label" for="id_stok_masuk">ID Stok Masuk</label>
                                    <input class="form-control form-control-lg" type="text" id="id_stok_masuk" name="id_stok_masuk" value="<?php echo $id_stok_masuk; ?>" disabled>
                                </div>
    
                                <!-- Select Nama Barang -->
                                <div class="mb-3">
                                    <label class="form-label" for="nama_barang">Nama Barang</label>
                                    <select class="form-select form-select-lg" id="id_barang_select" name="id_barang" required>
                                        <option value="">Pilih Barang</option>
                                        <?php if ($result_barang->num_rows > 0): ?>
                                            <?php while ($row = $result_barang->fetch_assoc()): ?>
                                                <option value="<?php echo htmlspecialchars($row['id_barang']); ?>">
                                                    <?php echo htmlspecialchars($row['nama_barang']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <option value="" disabled>No barang found</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                    <!-- Current Stok (Disabled) -->
                                    <div class="mb-3">
                                        <label class="form-label" for="current_stok">Stok Sekarang</label>
                                        <input class="form-control form-control-lg" type="number" id="current_stok" name="current_stok" value="0" disabled>
                                    </div>
                                    <!-- Jumlah Stok Masuk -->
                                    <div class="mb-3">
                                        <label class="form-label" for="stok_masuk">Stok Masuk</label>
                                        <input class="form-control form-control-lg" type="number" id="stok_masuk" name="stok_masuk" required>
                                    </div>
                                    <!-- Total Stok -->
                                    <div class="mb-3">
                                        <label class="form-label" for="total_stok">Total Stok</label>
                                        <input class="form-control form-control-lg" type="number" id="total_stok" name="total_stok" value="0" disabled>
                                    </div>
                                    <!-- Harga Beli (Readonly) -->
                                    <div class="mb-3">
                                        <label class="form-label" for="harga_beli">Harga Beli</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input class="form-control form-control-lg" type="number" id="harga_beli" name="harga_beli" value="0" readonly>
                                        </div>
                                    </div>
                                <!-- Total Harga (Stok Masuk x Harga Beli) -->
                                <div class="mb-3">
                                    <label class="form-label" for="total_harga">Total Harga Beli</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input class="form-control form-control-lg" type="number" id="total_harga" name="total_harga" value="0" readonly>
                                    </div>
                                </div>
                                <!-- Tanggal Masuk -->
                                <div class="mb-3">
                                    <label class="form-label" for="tanggal_masuk">Tanggal Masuk</label>
                                    <input class="form-control form-control-lg" type="date" id="tanggal_masuk" name="tanggal_masuk" required>
                                </div>
    
                                <!-- Asal Barang -->
                                <div class="mb-3">
                                    <label class="form-label" for="asal_barang">Asal Barang</label>
                                    <input class="form-control form-control-lg" type="text" id="asal_barang" name="asal_barang" required>
                                </div><br>
                                <div class="d-grid gap-2 mt-3">
                                    <div class="row">
                                        <div class="col">
                                            <a href="stok_masuk.php" class="btn btn-lg btn-danger w-100">Kembali</a>
                                        </div>
                                        <div class="col">
                                            <input class="btn btn-lg btn-primary w-100" type="submit" value="Add Stok Masuk">
                                        </div>
                                    </div>
                                </div>
                            </form>
    
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../footer/footer.php'; ?>
    
    <!-- JavaScript untuk mengupdate ID Barang dan Current Stok serta Harga Beli secara otomatis -->
    <script>
    // Stok data from PHP
    const stokData = <?php echo json_encode($stok_data); ?>;
    
    // Harga Beli data from PHP
    const hargaBeliData = <?php echo json_encode($harga_beli_data); ?>;
    
    function calculateTotalStok() {
        var currentStok = parseInt(document.getElementById('current_stok').value) || 0;
        var stokMasuk = parseInt(document.getElementById('stok_masuk').value) || 0;
        var totalStok = currentStok + stokMasuk;
        document.getElementById('total_stok').value = totalStok;
    }

    function calculateTotalHarga() {
        var stokMasuk = parseInt(document.getElementById('stok_masuk').value) || 0;
        var hargaBeli = parseInt(document.getElementById('harga_beli').value) || 0;
        var totalHarga = stokMasuk * hargaBeli;
        document.getElementById('total_harga').value = totalHarga;
    }
    
    document.getElementById('id_barang_select').addEventListener('change', function() {
        var selectedId = this.value;
        // Update Current Stok
        var currentStok = stokData[selectedId] || 0;
        document.getElementById('current_stok').value = currentStok;
        
        // Update Harga Beli
        var hargaBeli = hargaBeliData[selectedId] || 0;
        document.getElementById('harga_beli').value = hargaBeli;
        
        // Recalculate Total Stok and Total Harga
        calculateTotalStok();
        calculateTotalHarga();
    });
    
    // Add event listener for stok_masuk input
    document.getElementById('stok_masuk').addEventListener('input', function() {
        calculateTotalStok();
        calculateTotalHarga();
    });
    
    // Check for similar nama_barang (optional implementation)
    // Example: Alert if multiple barang have the same name
    // This requires additional backend processing to determine similarity
    // For simplicity, this part is omitted
    </script>
