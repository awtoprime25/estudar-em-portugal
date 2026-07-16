<?php
// ============================================================
//  Helper de BD do blog gerado por IA (cron-gerar-blog.php).
//  Reaproveita a ligação db() já existente em includes/db-helper.php —
//  este blog vive na MESMA BD do resto do site, sem ligação separada.
// ============================================================

require_once __DIR__ . '/db-helper.php';

if (!function_exists('blog_get_by_slug')) {

    /** 1 post por slug (apenas publicados). */
    function blog_get_by_slug(string $slug): ?array {
        $d = db(); if (!$d) return null;
        $stmt = $d->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published' LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /** Lista paginada para blog.php. $opts: category, limit, offset. */
    function blog_list(array $opts = []): array {
        $d = db(); if (!$d) return [];
        $limit  = max(1, (int) ($opts['limit']  ?? 12));
        $offset = max(0, (int) ($opts['offset'] ?? 0));
        $cat    = trim((string) ($opts['category'] ?? ''));

        $sql = "SELECT slug, title, h1_html, excerpt, category, category_label, category_icon,
                       hero_image, reading_minutes, published_at
                FROM blog_posts WHERE status = 'published'";
        $types = ''; $params = [];
        if ($cat !== '') { $sql .= " AND category = ?"; $types .= 's'; $params[] = $cat; }
        $sql .= " ORDER BY published_at DESC, id DESC LIMIT ? OFFSET ?";
        $types .= 'ii'; $params[] = $limit; $params[] = $offset;

        $stmt = $d->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /** Total de posts publicados (para paginação/contadores de filtro). */
    function blog_count(string $category = ''): int {
        $d = db(); if (!$d) return 0;
        if ($category !== '') {
            $stmt = $d->prepare("SELECT COUNT(*) c FROM blog_posts WHERE status='published' AND category=?");
            $stmt->bind_param('s', $category);
        } else {
            $stmt = $d->prepare("SELECT COUNT(*) c FROM blog_posts WHERE status='published'");
        }
        if (!$stmt) return 0;
        $stmt->execute();
        $n = (int) ($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        $stmt->close();
        return $n;
    }

    /** Posts relacionados (mesma categoria primeiro, depois aleatórios). */
    function blog_related(int $excludeId, string $category, int $limit = 4): array {
        $d = db(); if (!$d) return [];
        $stmt = $d->prepare(
            "SELECT slug, title, hero_image, category_label FROM blog_posts
             WHERE status='published' AND id <> ?
             ORDER BY (category = ?) DESC, RAND() LIMIT ?"
        );
        if (!$stmt) return [];
        $stmt->bind_param('isi', $excludeId, $category, $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}
