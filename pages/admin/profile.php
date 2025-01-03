     <?php include 'header.php';?>

 <main class="content">
          <div class="container-fluid p-0">
            <div class="mb-3">
              <h1 class="h3 d-inline align-middle">Profile</h1>
              <a
                class="badge bg-dark text-white ms-2"
                href="upgrade-to-pro.html"
              >
                Get more page examples
              </a>
            </div>
            <div class="row">
              <div class="col-md-11 col-xl-12">
                <div class="card mb-3">
                  <div class="card-header">
                    <h5 class="card-title mb-0">Profile Details</h5>
                  </div>
                  <div class="card-body text-center">
                    <img
                      src="../<?php echo htmlspecialchars($_SESSION['photo']); ?>"
                      alt=""
                      class="rounded-circle mb-2 center-crop"
                      width="128"
                      height="128"
                    />
                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($_SESSION['fullname']); ?></h5>
                    <div class="text-muted mb-2"><?php echo htmlspecialchars($_SESSION['username']); ?></div>

                    <div>
                      <a class="btn btn-primary btn-sm" href="#">Follow</a>
                      <a class="btn btn-primary btn-sm" href="#"
                        ><span data-feather="message-square"></span> Message</a
                      >
                    </div>
                  </div>
                  <hr class="my-0" />
                  <div class="card-body">
                    <h5 class="h6 card-title">Skills</h5>
                    <a href="#" class="badge bg-primary me-1 my-1">HTML</a>
                    <a href="#" class="badge bg-primary me-1 my-1"
                      >JavaScript</a
                    >
                    <a href="#" class="badge bg-primary me-1 my-1">Sass</a>
                    <a href="#" class="badge bg-primary me-1 my-1">Angular</a>
                    <a href="#" class="badge bg-primary me-1 my-1">Vue</a>
                    <a href="#" class="badge bg-primary me-1 my-1">React</a>
                    <a href="#" class="badge bg-primary me-1 my-1">Redux</a>
                    <a href="#" class="badge bg-primary me-1 my-1">UI</a>
                    <a href="#" class="badge bg-primary me-1 my-1">UX</a>
                  </div>
                  <hr class="my-0" />
                  <div class="card-body">
                    <h5 class="h6 card-title">About</h5>
                    <ul class="list-unstyled mb-0">
                      <li class="mb-1">
                        <span
                          data-feather="mail"
                          class="feather-sm me-1"
                        ></span>
                        <?php echo htmlspecialchars($_SESSION['email']); ?>
                      </li>

                      <li class="mb-1">
                        <span
                          data-feather="phone"
                          class="feather-sm me-1"
                        ></span>
                        <?php echo htmlspecialchars($_SESSION['phone']); ?>
                      </li>
                      <li class="mb-1">
                        <span
                          data-feather="calendar"
                          class="feather-sm me-1"
                        ></span>
                        <?php echo htmlspecialchars($_SESSION['birthdate']); ?>
                      </li>
                    </ul>
                  </div>
                  <hr class="my-0" />
                  <div class="card-body">
                    <h5 class="h6 card-title">Elsewhere</h5>
                    <ul class="list-unstyled mb-0">
                      <li class="mb-1"><a href="#">staciehall.co</a></li>
                      <li class="mb-1"><a href="#">Twitter</a></li>
                      <li class="mb-1"><a href="#">Facebook</a></li>
                      <li class="mb-1"><a href="#">Instagram</a></li>
                      <li class="mb-1"><a href="#">LinkedIn</a></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>

          <?php include 'footer.php';?>