<?php
require_once __DIR__ . '/../includes/auth.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'checks_form';

if ($page === 'logout') {
    logout();
    header('Location: ' . BASE_URL . 'index.php?page=login');
    exit;
}

if ($page === 'login') {
    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
        if ($username === '' || $password === '') {
            $error = 'Please enter username and password.';
        } else {
            if (attempt_login($username, $password, $error)) {
                header('Location: ' . BASE_URL . 'index.php?page=checks_form');
                exit;
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Login - Check Sheet</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL); ?>assets/adminty/files/bower_components/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL); ?>assets/adminty/files/assets/css/style.css">
    </head>
    <body class="fix-menu">
    <div class="container" style="margin-top: 10vh;">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header text-center">
                        <h5>Check Sheet Login</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="post" action="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=login'); ?>">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" name="username" id="username" class="form-control" required autofocus>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block mt-3">Login</button>
                        </form>
                    </div>
                    <div class="card-footer text-center text-muted">
                        Default: admin / admin123
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo htmlspecialchars(BASE_URL); ?>assets/adminty/files/bower_components/jquery/js/jquery.min.js"></script>
    <script src="<?php echo htmlspecialchars(BASE_URL); ?>assets/adminty/files/bower_components/bootstrap/js/bootstrap.min.js"></script>
    </body>
    </html>
    <?php
    exit;
}

require_login();

require_once __DIR__ . '/../includes/header.php';

$allowedPages = [
    'checks_form'  => __DIR__ . '/../modules/checks_form.php',
    'checks_list'  => __DIR__ . '/../modules/checks_list.php',
    'areas'        => __DIR__ . '/../modules/areas.php',
    'check_sheets' => __DIR__ . '/../modules/check_sheets.php',
    'users'        => __DIR__ . '/../modules/users.php',
];

if (isset($allowedPages[$page])) {
    require $allowedPages[$page];
} else {
    echo '<div class="alert alert-warning">Page not found.</div>';
}

require_once __DIR__ . '/../includes/footer.php';
