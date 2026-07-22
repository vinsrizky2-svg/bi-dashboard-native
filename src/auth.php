<?php
function require_login(): void {
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function require_role(array $roles = []): void {
    require_login();
    if (!in_array($_SESSION['user']['role'] ?? '', $roles)) {
        http_response_code(403);
        echo '<h2>Akses ditolak</h2>';
        exit;
    }
}

function current_user(): array {
    return $_SESSION['user'] ?? [];
}
