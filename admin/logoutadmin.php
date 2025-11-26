<?php
declare(strict_types=1);

    session_start();
    session_destroy();

    echo '<script>alert("Anda Telah Logout");window.location="../login.php";</script>';
?>