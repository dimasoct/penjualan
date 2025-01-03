<?php
// Mulai session
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['fullname'])) {
    header("Location: ../../index.php");
    exit;
}

// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);


$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />
    <meta
      name="description"
      content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5"
    />
    <meta name="author" content="AdminKit" />
    <meta
      name="keywords"
      content="adminkit, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web"
    />
    <meta charset="UTF-8">


    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link rel="shortcut icon" href="img/icons/icon-48x48.png" />

    <link rel="canonical" href="https://demo-basic.adminkit.io/" />

    <title>Koperasi</title>

    <link href="../../../css/app.css" rel="stylesheet" />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap"
      rel="stylesheet"
    />
    <style>
      .center-crop {
    object-fit: cover;
    }
   .center {
        text-align: center;
    }
 .pagination {
    display: flex;
    justify-content: flex-start; /* Atur perataan tombol */
    gap: 5px; /* Jarak antar tombol */
    list-style: none; /* Menghilangkan titik */
    padding: 0; /* Menghilangkan padding default */
    margin: 0; /* Menghilangkan margin default */
}    
    </style>
  </head>
  <body>
    <div class="wrapper">
      <nav id="sidebar" class="sidebar js-sidebar">
        <div class="sidebar-content js-simplebar">
          <a class="sidebar-brand" href="index.html">
            <span class="align-middle">Inventaris Penjualan</span>
          </a>
          <ul class="sidebar-nav">
            <li class="sidebar-header">Pages</li>
            <li class="sidebar-item <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
              <a class="sidebar-link" href="../dashboard/dashboard.php">
                <i class="align-middle" data-feather="home"></i>
                <span class="align-middle">Dashboard</span>
              </a>
            </li>
           <?php
            $active_pages_analisis = ['analisis.php']; // Daftar halaman yang ingin dicek
            $current_page_analisis = basename($_SERVER['PHP_SELF']); // Mendapatkan nama file halaman saat ini
            ?>
            <li class="sidebar-header mt-0">Analisis</li>
            <li class="sidebar-item <?php echo in_array($current_page_analisis, $active_pages_analisis) ? 'active' : ''; ?>">
              <a class="sidebar-link" href="../analisis/analisis.php">
                <i class="align-middle" data-feather="bar-chart-2"></i>
                <span class="align-middle">Analisis</span>
              </a>
            </li>
           <?php
            $active_pages_analisisperbarang = ['analisisperbarang.php']; // Daftar halaman yang ingin dicek
            $current_page_analisisperbarang = basename($_SERVER['PHP_SELF']); // Mendapatkan nama file halaman saat ini
            ?>
            <li class="sidebar-item <?php echo in_array($current_page_analisisperbarang, $active_pages_analisisperbarang) ? 'active' : ''; ?>">
              <a class="sidebar-link" href="../analisis/analisisperbarang.php">
                <i class="align-middle" data-feather="pie-chart"></i>
                <span class="align-middle">Analisis Per Barang</span>
              </a>
            </li>
           <?php
            $active_pages_barang = ['barang.php', 'edit_barang.php', 'add_barang.php']; // Daftar halaman yang ingin dicek
            $current_page_barang = basename($_SERVER['PHP_SELF']); // Mendapatkan nama file halaman saat ini
            ?>
            <li class="sidebar-header mt-0">Managemen Barang</li>
            <li class="sidebar-item <?php echo in_array($current_page_barang, $active_pages_barang) ? 'active' : ''; ?>">
              <a class="sidebar-link" href="../barang/barang.php">
                <i class="align-middle" data-feather="hard-drive"></i>
                <span class="align-middle">Barang</span>
              </a>
            </li>
            <?php
            $active_pages_katbarang = ['kategori.php', 'edit_kategori.php', 'add_kategori.php']; // Daftar halaman yang ingin dicek
            $current_page_katbarang = basename($_SERVER['PHP_SELF']); // Mendapatkan nama file halaman saat ini
            ?>
            <li class="sidebar-item <?php echo in_array($current_page_katbarang, $active_pages_katbarang) ? 'active' : ''; ?>">
              <a class="sidebar-link" href="../kategori/kategori.php">
                <i class="align-middle" data-feather="grid"></i>
                <span class="align-middle">Kategori Barang</span>
              </a>
            </li>
            <?php
            $active_pages_tersedia = ['stok.php']; // Add all filenames you want to check
            $current_page_tersedia = basename($_SERVER['PHP_SELF']);
            ?>
            <li class="sidebar-item <?php echo in_array($current_page_tersedia, $active_pages_tersedia) ? 'active' : ''; ?>">
              <a class="sidebar-link" href="../stok/stok.php">
                <i class="align-middle" data-feather="database"></i>
                <span class="align-middle">Stok tersedia</span>
              </a>
            </li>
            


            <li class="sidebar-header mt-0">Transaksi</li>
            <?php
            $active_pages_katbarang = ['stok_masuk.php', 'edit_stok_masuk.php', 'add_stok_masuk.php']; // Daftar halaman yang ingin dicek
            $current_page_katbarang = basename($_SERVER['PHP_SELF']); // Mendapatkan nama file halaman saat ini
            ?>
            <li class="sidebar-item <?php echo in_array($current_page_katbarang, $active_pages_katbarang) ? 'active' : ''; ?>">
              <a class="sidebar-link" href="../stok_masuk/stok_masuk.php">
                <i class="align-middle" data-feather="arrow-down"></i>
                <span class="align-middle">Stok Masuk</span>
              </a>
            </li>
            <?php
            $active_pages_stock_keluar = ['stok_keluar.php', 'edit_stok_keluar.php', 'add_stok_keluar.php']; // Add all filenames you want to check
            $current_page_stock_keluar = basename($_SERVER['PHP_SELF']);
            ?>
            <li class="sidebar-item <?php echo in_array($current_page_stock_keluar, $active_pages_stock_keluar) ? 'active' : ''; ?>">
              <a class="sidebar-link" href="../stok_keluar/stok_keluar.php">
                <i class="align-middle" data-feather="arrow-up"></i>
                <span class="align-middle">Stok Keluar</span>
              </a>
            </li>
            
            <?php
            $active_pages_operasional = ['operasional.php', 'edit_operasional.php', 'add_operasional.php']; // Add all filenames you want to check
            $current_page_operasional = basename($_SERVER['PHP_SELF']);
            ?>
            <li class="sidebar-item <?php echo in_array($current_page_operasional, $active_pages_operasional) ? 'active' : ''; ?>">
              <a class="sidebar-link" href="../operasional/operasional.php">
                <i class="align-middle" data-feather="airplay"></i>
                <span class="align-middle">Biaya Operasional</span>
              </a>
            </li>
            <?php
            $active_pages_users = ['users.php', 'edit_user.php', 'add_user.php']; // Add all filenames you want to check
            $current_page_users = basename($_SERVER['PHP_SELF']);
            ?>
            <li class="sidebar-header">Tools</li>
            <li class="sidebar-item <?php echo in_array($current_page_users, $active_pages_users) ? 'active' : ''; ?>">
                <a class="sidebar-link" href="../users/users.php">
                    <i class="align-middle" data-feather="users"></i>
                    <span class="align-middle">Users</span>
                </a>
            </li>
            <li class="sidebar-item <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
              <a class="sidebar-link" href="../profile/profile.php">
                <i class="align-middle" data-feather="user"></i>
                <span class="align-middle">Profile</span>
              </a>
            </li>
            <li class="sidebar-item mb-3">
              <a class="sidebar-link" href="../../../logout.php">
                <i class="align-middle" data-feather="log-out"></i>
                <span class="align-middle">Log-Out</span>
              </a>
            </li>
          </ul>
        </div>
                
        
      </nav>
      <div class="main">
        <nav class="navbar navbar-expand navbar-light navbar-bg">
          <a class="sidebar-toggle js-sidebar-toggle">
            <i class="hamburger align-self-center"></i>
          </a>

          <div class="navbar-collapse collapse">
            <ul class="navbar-nav navbar-align">
              <li class="nav-item dropdown">
                <a
                  class="nav-icon dropdown-toggle"
                  href="#"
                  id="alertsDropdown"
                  data-bs-toggle="dropdown"
                >
                  <div class="position-relative">
                    <i class="align-middle" data-feather="bell"></i>
                    <span class="indicator">4</span>
                  </div>
                </a>
                <div
                  class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0"
                  aria-labelledby="alertsDropdown"
                >
                  <div class="dropdown-menu-header">4 New Notifications</div>
                  <div class="list-group">
                    <a href="#" class="list-group-item">
                      <div class="row g-0 align-items-center">
                        <div class="col-2">
                          <i
                            class="text-danger"
                            data-feather="alert-circle"
                          ></i>
                        </div>
                        <div class="col-10">
                          <div class="text-dark">Update completed</div>
                          <div class="text-muted small mt-1">
                            Restart server 12 to complete the update.
                          </div>
                          <div class="text-muted small mt-1">30m ago</div>
                        </div>
                      </div>
                    </a>
                    <a href="#" class="list-group-item">
                      <div class="row g-0 align-items-center">
                        <div class="col-2">
                          <i class="text-warning" data-feather="bell"></i>
                        </div>
                        <div class="col-10">
                          <div class="text-dark">Lorem ipsum</div>
                          <div class="text-muted small mt-1">
                            Aliquam ex eros, imperdiet vulputate hendrerit et.
                          </div>
                          <div class="text-muted small mt-1">2h ago</div>
                        </div>
                      </div>
                    </a>
                    <a href="#" class="list-group-item">
                      <div class="row g-0 align-items-center">
                        <div class="col-2">
                          <i class="text-primary" data-feather="home"></i>
                        </div>
                        <div class="col-10">
                          <div class="text-dark">Login from 192.186.1.8</div>
                          <div class="text-muted small mt-1">5h ago</div>
                        </div>
                      </div>
                    </a>
                    <a href="#" class="list-group-item">
                      <div class="row g-0 align-items-center">
                        <div class="col-2">
                          <i class="text-success" data-feather="user-plus"></i>
                        </div>
                        <div class="col-10">
                          <div class="text-dark">New connection</div>
                          <div class="text-muted small mt-1">
                            Christina accepted your request.
                          </div>
                          <div class="text-muted small mt-1">14h ago</div>
                        </div>
                      </div>
                    </a>
                  </div>
                  <div class="dropdown-menu-footer">
                    <a href="#" class="text-muted">Show all notifications</a>
                  </div>
                </div>
              </li>
              <li class="nav-item dropdown">
                <a
                  class="nav-icon dropdown-toggle"
                  href="#"
                  id="messagesDropdown"
                  data-bs-toggle="dropdown"
                >
                  <div class="position-relative">
                    <i class="align-middle" data-feather="message-square"></i>
                  </div>
                </a>
                <div
                  class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0"
                  aria-labelledby="messagesDropdown"
                >
                  <div class="dropdown-menu-header">
                    <div class="position-relative">4 New Messages</div>
                  </div>
                  <div class="list-group">
                    <a href="#" class="list-group-item">
                      <div class="row g-0 align-items-center">
                        <div class="col-2">
                          <img
                            src="img/avatars/avatar-5.jpg"
                            class="avatar img-fluid rounded-circle"
                            alt="Vanessa Tucker"
                          />
                        </div>
                        <div class="col-10 ps-2">
                          <div class="text-dark">Vanessa Tucker</div>
                          <div class="text-muted small mt-1">
                            Nam pretium turpis et arcu. Duis arcu tortor.
                          </div>
                          <div class="text-muted small mt-1">15m ago</div>
                        </div>
                      </div>
                    </a>
                    <a href="#" class="list-group-item">
                      <div class="row g-0 align-items-center">
                        <div class="col-2">
                          <img
                            src="img/avatars/avatar-2.jpg"
                            class="avatar img-fluid rounded-circle"
                            alt="William Harris"
                          />
                        </div>
                        <div class="col-10 ps-2">
                          <div class="text-dark">William Harris</div>
                          <div class="text-muted small mt-1">
                            Curabitur ligula sapien euismod vitae.
                          </div>
                          <div class="text-muted small mt-1">2h ago</div>
                        </div>
                      </div>
                    </a>
                    <a href="#" class="list-group-item">
                      <div class="row g-0 align-items-center">
                        <div class="col-2">
                          <img
                            src="img/avatars/avatar-4.jpg"
                            class="avatar img-fluid rounded-circle"
                            alt="Christina Mason"
                          />
                        </div>
                        <div class="col-10 ps-2">
                          <div class="text-dark">Christina Mason</div>
                          <div class="text-muted small mt-1">
                            Pellentesque auctor neque nec urna.
                          </div>
                          <div class="text-muted small mt-1">4h ago</div>
                        </div>
                      </div>
                    </a>
                    <a href="#" class="list-group-item">
                      <div class="row g-0 align-items-center">
                        <div class="col-2">
                          <img
                            src="img/avatars/avatar-3.jpg"
                            class="avatar img-fluid rounded-circle"
                            alt="Sharon Lessman"
                          />
                        </div>
                        <div class="col-10 ps-2">
                          <div class="text-dark">Sharon Lessman</div>
                          <div class="text-muted small mt-1">
                            Aenean tellus metus, bibendum sed, posuere ac,
                            mattis non.
                          </div>
                          <div class="text-muted small mt-1">5h ago</div>
                        </div>
                      </div>
                    </a>
                  </div>
                  <div class="dropdown-menu-footer">
                    <a href="#" class="text-muted">Show all messages</a>
                  </div>
                </div>
              </li>
              <li class="nav-item dropdown">
                <a
                  class="nav-icon dropdown-toggle d-inline-block d-sm-none"
                  href="#"
                  data-bs-toggle="dropdown"
                >
                  <i class="align-middle" data-feather="settings"></i>
                </a>

                <a
                  class="nav-link dropdown-toggle d-none d-sm-inline-block"
                  href="#"
                  data-bs-toggle="dropdown"
                >
                  <img
                    src="../../<?php echo htmlspecialchars($_SESSION['photo']); ?>"
                    class="avatar img-fluid rounded me-1 center-crop"
                    alt=""
                  />
                  <span class="text-dark"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item" href="../../profile/profile.php"
                    ><i class="align-middle me-1" data-feather="user"></i>
                    Profile</a
                  >
            
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="index.html"
                    ><i class="align-middle me-1" data-feather="settings"></i>
                    Settings & Privacy</a
                  >
                  <a class="dropdown-item" href="#"
                    ><i
                      class="align-middle me-1"
                      data-feather="help-circle"
                    ></i>
                    Help Center</a
                  >
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="../../../logout.php"
                    ><i
                      class="align-middle me-1"
                      data-feather="help-circle"
                    ></i>
                    Log-Out</a
                  >
                
              </li>
            </ul>
          </div>
        </nav>