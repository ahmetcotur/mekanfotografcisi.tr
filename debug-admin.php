<!DOCTYPE html>
<html>

<head>
    <title>Admin Debug</title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
            background: #f5f5f5;
        }

        .info {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>

<body>
    <h1>Admin Panel Debug Info</h1>

    <div class="info">
        <h2>File Check</h2>
        <p>admin/index.html exists:
            <?= file_exists(__DIR__ . '/admin/index.html') ? '<span class="success">YES</span>' : '<span class="error">NO</span>' ?>
        </p>
        <p>admin/assets exists:
            <?= is_dir(__DIR__ . '/admin/assets') ? '<span class="success">YES</span>' : '<span class="error">NO</span>' ?>
        </p>
        <p>admin-legacy exists:
            <?= is_dir(__DIR__ . '/admin-legacy') ? '<span class="success">YES</span>' : '<span class="error">NO</span>' ?>
        </p>
    </div>

    <div class="info">
        <h2>Request Info</h2>
        <p>Request URI:
            <?= $_SERVER['REQUEST_URI'] ?>
        </p>
        <p>Script Name:
            <?= $_SERVER['SCRIPT_NAME'] ?>
        </p>
        <p>PHP Self:
            <?= $_SERVER['PHP_SELF'] ?>
        </p>
    </div>

    <div class="info">
        <h2>Session Info</h2>
        <p>Session Started:
            <?= session_status() === PHP_SESSION_ACTIVE ? '<span class="success">YES</span>' : '<span class="error">NO</span>' ?>
        </p>
        <p>Admin User ID:
            <?= $_SESSION['admin_user_id'] ?? '<span class="error">NOT SET</span>' ?>
        </p>
    </div>

    <div class="info">
        <h2>Links</h2>
        <p><a href="/admin/">New Admin (React SPA)</a></p>
        <p><a href="/admin-legacy/">Old Admin (PHP)</a></p>
        <p><a href="/login">Login Page</a></p>
    </div>

    <div class="info">
        <h2>Admin Files</h2>
        <?php
        if (is_dir(__DIR__ . '/admin')) {
            echo '<ul>';
            foreach (scandir(__DIR__ . '/admin') as $file) {
                if ($file !== '.' && $file !== '..') {
                    echo '<li>' . htmlspecialchars($file) . '</li>';
                }
            }
            echo '</ul>';
        }
        ?>
    </div>
</body>

</html>