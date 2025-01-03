
    <?php

    include '../../../database/db.php';

    // Membuat koneksi
    $conn = new mysqli($host, $username, $password, $dbname);

    // Memeriksa koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Set default date range to current month
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');

    // Hitung uang modal dari stok masuk
    $sql_modal = "SELECT SUM(stok_masuk * harga_beli) as total_modal 
                  FROM stok_masuk 
                  WHERE tanggal_masuk BETWEEN ? AND ?";
    $stmt_modal = $conn->prepare($sql_modal);
    $stmt_modal->bind_param("ss", $start_date, $end_date);
    $stmt_modal->execute();
    $result_modal = $stmt_modal->get_result();
    $row_modal = $result_modal->fetch_assoc();
    $total_modal = $row_modal['total_modal'] ?? 0;

    // Hitung uang penjualan dari stok keluar
    $sql_penjualan = "SELECT SUM(stok_keluar * harga_jual) as total_penjualan 
                      FROM stok_keluar 
                      WHERE tanggal_keluar BETWEEN ? AND ?";
    $stmt_penjualan = $conn->prepare($sql_penjualan);
    $stmt_penjualan->bind_param("ss", $start_date, $end_date);
    $stmt_penjualan->execute();
    $result_penjualan = $stmt_penjualan->get_result();
    $row_penjualan = $result_penjualan->fetch_assoc();
    $total_penjualan = $row_penjualan['total_penjualan'] ?? 0;

    // Hitung total biaya operasional
    $sql_operasional = "SELECT SUM(jumlah_biaya) as total_biaya 
                        FROM operasional 
                        WHERE tanggal_biaya BETWEEN ? AND ?";
    $stmt_operasional = $conn->prepare($sql_operasional);
    $stmt_operasional->bind_param("ss", $start_date, $end_date);
    $stmt_operasional->execute();
    $result_operasional = $stmt_operasional->get_result();
    $row_operasional = $result_operasional->fetch_assoc();
    $total_operasional = $row_operasional['total_biaya'] ?? 0;

    // Hitung keuntungan kotor
    $keuntungan_kotor = $total_penjualan - $total_modal;

    // Hitung keuntungan bersih
    $keuntungan_bersih = $keuntungan_kotor - $total_operasional;

    // Hitung total stok terjual
    $sql_stok_terjual = "SELECT SUM(stok_keluar) as total_terjual 
                         FROM stok_keluar 
                         WHERE tanggal_keluar BETWEEN ? AND ?";
    $stmt_stok_terjual = $conn->prepare($sql_stok_terjual);
    $stmt_stok_terjual->bind_param("ss", $start_date, $end_date);
    $stmt_stok_terjual->execute();
    $result_stok_terjual = $stmt_stok_terjual->get_result();
    $row_stok_terjual = $result_stok_terjual->fetch_assoc();
    $total_stok_terjual = $row_stok_terjual['total_terjual'] ?? 0;

    // Get monthly sales data for chart
    $sql_monthly_sales = "SELECT DATE_FORMAT(tanggal_keluar, '%Y-%m') as sale_month,
                         SUM(stok_keluar * harga_jual) as monthly_total
                         FROM stok_keluar 
                         WHERE tanggal_keluar >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                         GROUP BY DATE_FORMAT(tanggal_keluar, '%Y-%m')
                         ORDER BY sale_month";
    $result_monthly = $conn->query($sql_monthly_sales);

    $months = [];
    $sales = [];
    while($row = $result_monthly->fetch_assoc()) {
        $months[] = date('M Y', strtotime($row['sale_month'] . '-01'));
        $sales[] = $row['monthly_total'];
    }

    // Get data for pie chart
    $sql_terjual = "SELECT b.nama_barang, SUM(sk.stok_keluar) as jumlah_terjual 
                    FROM stok_keluar sk
                    JOIN barang b ON sk.id_barang = b.id_barang
                    GROUP BY b.id_barang, b.nama_barang";
    $result_terjual = $conn->query($sql_terjual);

    $barang = [];
    $jumlahTerjual = [];
    while($row = $result_terjual->fetch_assoc()) {
        $barang[] = $row['nama_barang'];
        $jumlahTerjual[] = $row['jumlah_terjual'];
    }

    // Get daily sales data for calendar
    $sql_daily_sales = "SELECT DATE(tanggal_keluar) as sale_date, 
                        COUNT(*) as total_transactions
                        FROM stok_keluar 
                        WHERE tanggal_keluar >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                        GROUP BY DATE(tanggal_keluar)";
    $result_daily = $conn->query($sql_daily_sales);

    $daily_sales = [];
    while($row = $result_daily->fetch_assoc()) {
        $daily_sales[$row['sale_date']] = $row['total_transactions'];
    }

    ?>

    <?php include '../header/header.php';?>

        <main class="content">
          <div class="container-fluid p-0">
            <h1 class="h3 mb-3"><strong>Analytics</strong> Dashboard</h1>

            <div class="row">
              <div class="col-xl-12 col-xxl-12 d-flex">
                <div class="w-100">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="card">
                        <div class="card-body">
                          <div class="row">
                            <div class="col mt-0">
                              <h5 class="card-title">Modal (Stok Masuk)</h5>
                            </div>

                            <div class="col-auto">
                              <div class="stat text-primary">
                                <i class="align-middle" data-feather="dollar-sign"></i>
                              </div>
                            </div>
                          </div>
                          <h2 class="mt-1 mb-3">Rp <?php echo number_format($total_modal, 0, ',', '.'); ?></h2>
                          <div class="mb-0">
                            <span class="text-muted">Periode: <?php echo date('F Y'); ?></span>
                          </div>
                        </div>
                      </div>
                      <div class="card">
                        <div class="card-body">
                          <div class="row">
                            <div class="col mt-0">
                              <h5 class="card-title">Penjualan (Stok Keluar)</h5>
                            </div>

                            <div class="col-auto">
                              <div class="stat text-primary">
                                <i class="align-middle" data-feather="dollar-sign"></i>
                              </div>
                            </div>
                          </div>
                          <h2 class="mt-1 mb-3">Rp <?php echo number_format($total_penjualan, 0, ',', '.'); ?></h2>
                          <div class="mb-0">
                            <span class="text-muted">Periode: <?php echo date('F Y'); ?></span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="card">
                        <div class="card-body">
                          <div class="row">
                            <div class="col mt-0">
                              <h5 class="card-title">Keuntungan Kotor</h5>
                            </div>

                            <div class="col-auto">
                              <div class="stat text-primary">
                                <i class="align-middle" data-feather="dollar-sign"></i>
                              </div>
                            </div>
                          </div>
                          <h2 class="mt-1 mb-3">Rp <?php echo number_format($keuntungan_kotor, 0, ',', '.'); ?></h2>
                          <div class="mb-0">
                            <span class="text-muted">Periode: <?php echo date('F Y'); ?></span>
                          </div>
                        </div>
                      </div>
                      <div class="card">
                        <div class="card-body">
                          <div class="row">
                            <div class="col mt-0">
                              <h5 class="card-title">Biaya Operasional</h5>
                            </div>

                            <div class="col-auto">
                              <div class="stat text-primary">
                                <i class="align-middle" data-feather="dollar-sign"></i>
                              </div>
                            </div>
                          </div>
                          <h2 class="mt-1 mb-3">Rp <?php echo number_format($total_operasional, 0, ',', '.'); ?></h2>
                          <div class="mb-0">
                            <span class="text-muted">Periode: <?php echo date('F Y'); ?></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-xl-6 col-xxl-6">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col mt-0">
                        <h5 class="card-title">Keuntungan Bersih</h5>
                      </div>
                      <div class="col-auto">
                        <div class="stat text-primary">
                          <i class="align-middle" data-feather="dollar-sign"></i>
                        </div>
                      </div>
                    </div>
                    <h2 class="mt-1 mb-3">Rp <?php echo number_format($keuntungan_bersih, 0, ',', '.'); ?></h2>
                    <div class="mb-0">
                      <span class="text-muted">Periode: <?php echo date('F Y'); ?></span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-xl-6 col-xxl-6">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col mt-0">
                        <h5 class="card-title">Total Stok Terjual</h5>
                      </div>
                      <div class="col-auto">
                        <div class="stat text-primary">
                          <i class="align-middle" data-feather="box"></i>
                        </div>
                      </div>
                    </div>
                    <h2 class="mt-1 mb-3"><?php echo number_format($total_stok_terjual, 0, ',', '.'); ?> Unit</h2>
                    <div class="mb-0">
                      <span class="text-muted">Periode: <?php echo date('F Y'); ?></span>
                    </div>
                  </div>
                </div>
              </div>
              

              <div class="col-xl-12 col-xxl-12">
                <div class="card flex-fill w-100">
                  <div class="card-header">
                    <h5 class="card-title mb-0">Penjualan Bulanan</h5>
                  </div>
                  <div class="card-body py-3">
                    <div class="chart chart-sm">
                      <canvas id="chartjs-dashboard-line"></canvas>
                    </div>
                  </div>
                </div>
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // Line chart
                    new Chart(document.getElementById("chartjs-dashboard-line"), {
                        type: "line",
                        data: {
                            labels: <?php echo json_encode($months); ?>,
                            datasets: [{
                                label: "Penjualan",
                                fill: true,
                                backgroundColor: "transparent",
                                borderColor: "#47bac1",
                                data: <?php echo json_encode($sales); ?>
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            tooltips: {
                                intersect: false
                            },
                            hover: {
                                intersect: true
                            },
                            plugins: {
                                filler: {
                                    propagate: false
                                }
                            },
                            scales: {
                                xAxes: [{
                                    reverse: true,
                                    gridLines: {
                                        color: "rgba(0,0,0,0.05)"
                                    }
                                }],
                                yAxes: [{
                                    ticks: {
                                        stepSize: 1000000,
                                        callback: function(value) {
                                            return 'Rp ' + value.toLocaleString('id-ID');
                                        }
                                    },
                                    display: true,
                                    borderDash: [5, 5],
                                    gridLines: {
                                        color: "rgba(0,0,0,0)",
                                        fontColor: "#fff"
                                    }
                                }]
                            }
                        }
                    });
                });
                </script>
              </div>
            </div>
            
            <div class="row">
              <div class="col-12 col-md-6 col-xxl-3 d-flex order-2 order-xxl-3">
                <div class="card flex-fill w-100">
                  <div class="card-header">
                    <h5 class="card-title mb-0">Grafik Terjual</h5>
                  </div>
                  <div class="card-body d-flex">
                    <div class="align-self-center w-100">
                      <div class="py-3">
                        <div class="chart">
                          <canvas id="grafik_bulat"></canvas>
                        </div>
                      </div>
                      <table class="table mb-0">
                        <tbody>
                          <?php foreach ($barang as $index => $item): ?>
                          <tr>
                             <td><?php echo htmlspecialchars($item); ?></td>
                            <td class="text-end"><?php echo $jumlahTerjual[$index]; ?></td>
                          </tr>
                          
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // Pie chart
                    new Chart(document.getElementById("grafik_bulat"), {
                        type: "pie",
                        data: {
                            labels: <?php echo json_encode($barang); ?>,
                            datasets: [{
                                data: <?php echo json_encode($jumlahTerjual); ?>,
                                backgroundColor: [
                                    "#47bac1",
                                    "#5b7dff",
                                    "#fcc100",
                                    "#ff3e3e",
                                    "#35c481",
                                    "#8b75d7",
                                    "#f67e7d",
                                    "#2ec7c9"
                                ],
                                borderWidth: 5
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            }
                        }
                    });
                });
                </script>
              </div>
              
              <div class="col-12 col-md-6 col-xxl-3 d-flex order-1 order-xxl-1">
                <div class="card flex-fill">
                  <div class="card-header">
                    <h5 class="card-title mb-0">Calendar</h5>
                  </div>
                  <div class="card-body d-flex">
                    <div class="align-self-center w-100">
                      <div class="chart">
                        <div id="datetimepicker-dashboard"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <script>
      document.addEventListener("DOMContentLoaded", function () {
        var date = new Date(Date.now() - 5 * 24 * 60 * 60 * 1000);
        var defaultDate =
          date.getUTCFullYear() +
          "-" +
          (date.getUTCMonth() + 1) +
          "-" +
          date.getUTCDate();

        var dailySales = <?php echo json_encode($daily_sales); ?>;
        
        document.getElementById("datetimepicker-dashboard").flatpickr({
          inline: true,
          prevArrow: '<span title="Previous month">&laquo;</span>',
          nextArrow: '<span title="Next month">&raquo;</span>',
          defaultDate: defaultDate,
          onDayCreate: function(dObj, dStr, fp, dayElem) {
            var date = dayElem.dateObj.toISOString().split('T')[0];
            if(dailySales[date]) {
              var badge = document.createElement('span');
              badge.className = 'badge bg-success';
              badge.style.position = 'absolute';
              badge.style.bottom = '3px';
              badge.style.right = '3px';
              badge.style.fontSize = '8px';
              badge.innerHTML = dailySales[date];
              dayElem.appendChild(badge);
            }
          }
        });
      });
    </script>

          </div>
        </main>

    <?php include '../footer/footer.php';?>