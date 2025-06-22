<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

require_once 'connect.php';

$message = '';
$error = '';

// Handle actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = (int)($_GET['id'] ?? 0);
    
    if ($action === 'delete' && $id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Članak je uspješno obrisan!';
        } catch (PDOException $e) {
            $error = 'Greška pri brisanju članka: ' . $e->getMessage();
        }
    }
    
    if ($action === 'toggle_featured' && $id) {
        try {
            $stmt = $pdo->prepare("UPDATE articles SET featured = NOT featured WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Status istaknutog članka je promjenjen!';
        } catch (PDOException $e) {
            $error = 'Greška pri promjeni statusa: ' . $e->getMessage();
        }
    }
    
    if ($action === 'toggle_status' && $id) {
        try {
            $stmt = $pdo->prepare("UPDATE articles SET status = CASE WHEN status = 'published' THEN 'draft' ELSE 'published' END WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Status članka je promjenjen!';
        } catch (PDOException $e) {
            $error = 'Greška pri promjeni statusa: ' . $e->getMessage();
        }
    }
}

// Handle editing
if (isset($_POST['edit_article'])) {
    $id = (int)$_POST['id'];
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $author = trim($_POST['author'] ?? 'Admin');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'published';
    
    if (!$title || !$content) {
        $error = 'Naslov i sadržaj su obavezni!';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE articles 
                SET title = ?, content = ?, excerpt = ?, image_url = ?, 
                    category_id = ?, author = ?, featured = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$title, $content, $excerpt, $image_url, $category_id ?: null, $author, $featured, $status, $id]);
            $message = 'Članak je uspješno ažuriran!';
        } catch (PDOException $e) {
            $error = 'Greška pri ažuriranju članka: ' . $e->getMessage();
        }
    }
}

// Get editing article
$editingArticle = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editStmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $editStmt->execute([$editId]);
    $editingArticle = $editStmt->fetch(PDO::FETCH_ASSOC);
}

// Get all articles
$stmt = $pdo->query("
    SELECT a.*, c.name as category_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    ORDER BY a.created_at DESC
");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for the form
$categoryStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administracija</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Georgia', serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        header {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 20px 0;
        }
        .header-content {
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px 0;
        }
        .logo {
            font-size: 48px;
            font-weight: bold;
            color: #2c5aa0;
            text-decoration: none;
            font-family: 'Times New Roman', serif;
            display: block;
        }
        .tagline {
            font-size: 14px;
            color: #666;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 5px;
        }
        nav {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 15px 0;
        }
        .nav-links {
            display: flex;
            list-style: none;
            gap: 40px;
        }
        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
            transition: color 0.3s;
        }
        .nav-links a:hover {
            color: #2c5aa0;
        }
        .admin-links {
            margin-left: auto;
            display: flex;
            gap: 20px;
        }
        .admin-links a {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 12px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 36px;
            min-width: 100px;
            text-align: center;
        }
        .admin-links a:hover {
            background: #c82333;
            color: white;
        }
        .btn-primary {
            background: #2c5aa0;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-primary:hover {
            background: #1a3a6b;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-secondary:hover {
            background: #495057;
        }
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        .table th, .table td {
            padding: 12px 16px;
            border-bottom: 1px solid #e0e0e0;
        }
        .table th {
            background: #f8f9fa;
            color: #2c5aa0;
            font-weight: bold;
        }
        .table-striped tbody tr:nth-child(odd) {
            background: #f8f9fa;
        }
        .mb-4 { margin-bottom: 32px; }
        .mt-4 { margin-top: 32px; }
        .form-label { font-weight: bold; color: #2c5aa0; }
        .form-control, textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            font-family: inherit;
            margin-bottom: 15px;
        }
        .form-check-input {
            margin-right: 8px;
        }
        .form-actions {
            text-align: center;
            margin-top: 30px;
        }
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            .nav-links {
                flex-wrap: wrap;
                gap: 20px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div>
                    <a href="index.php" class="logo">El Confidencial</a>
                    <div class="tagline">Dnevni list utjecajnih čitatelja</div>
                </div>
            </div>
        </div>
    </header>
    <nav>
        <div class="container">
            <ul class="nav-links">
                <li><a href="index.php">Početna</a></li>
                <li><a href="unos.php">Dodaj članak</a></li>
                <div class="admin-links">
                    <a href="logout.php">Odjava</a>
                </div>
            </ul>
        </div>
    </nav>
    <main>
        <div class="container mt-4">
            <h1 class="mb-4">Administracija članaka</h1>
            <?php if ($message): ?>
                <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($editingArticle): ?>
                <h2>Uredi članak</h2>
                <form method="post" class="mb-4">
                    <input type="hidden" name="id" value="<?php echo $editingArticle['id']; ?>">
                    <label class="form-label">Naslov</label>
                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($editingArticle['title']); ?>" required>
                    <label class="form-label">Sadržaj</label>
                    <textarea name="content" class="form-control" rows="5" required><?php echo htmlspecialchars($editingArticle['content']); ?></textarea>
                    <label class="form-label">Kratki opis</label>
                    <textarea name="excerpt" class="form-control" rows="2"><?php echo htmlspecialchars($editingArticle['excerpt']); ?></textarea>
                    <label class="form-label">URL slike</label>
                    <input type="text" name="image_url" class="form-control" value="<?php echo htmlspecialchars($editingArticle['image_url']); ?>">
                    <label class="form-label">Kategorija</label>
                    <select name="category_id" class="form-control">
                        <option value="">Odaberi kategoriju</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $editingArticle['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label class="form-label">Autor</label>
                    <input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars($editingArticle['author']); ?>">
                    <label class="form-check">
                        <input type="checkbox" name="featured" class="form-check-input" <?php echo $editingArticle['featured'] ? 'checked' : ''; ?>>
                        Istaknuti članak
                    </label>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="published" <?php echo $editingArticle['status'] === 'published' ? 'selected' : ''; ?>>Objavljeno</option>
                        <option value="draft" <?php echo $editingArticle['status'] === 'draft' ? 'selected' : ''; ?>>Skica</option>
                    </select>
                    <div class="form-actions">
                        <button type="submit" name="edit_article" class="btn-primary">Spremi promjene</button>
                        <a href="administracija.php" class="btn-secondary">Odustani</a>
                    </div>
                </form>
            <?php endif; ?>
            <h2><?php echo $editingArticle ? 'Svi članci' : 'Upravljanje člancima'; ?></h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Naslov</th>
                            <th>Autor</th>
                            <th>Kategorija</th>
                            <th>Status</th>
                            <th>Istaknuto</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (
                            $articles as $article): ?>
                        <tr>
                            <td><?php echo $article['id']; ?></td>
                            <td><?php echo htmlspecialchars($article['title']); ?></td>
                            <td><?php echo htmlspecialchars($article['author']); ?></td>
                            <td><?php echo htmlspecialchars($article['category_name']); ?></td>
                            <td>
                                <form method="get" action="administracija.php" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                                    <button type="submit" class="btn-secondary" style="padding:4px 10px;font-size:12px;">
                                        <?php echo $article['status'] === 'published' ? 'Objavljeno' : 'Skica'; ?>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="get" action="administracija.php" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_featured">
                                    <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                                    <button type="submit" 
                                        class="btn-secondary"
                                        style="padding:4px 10px;font-size:12px; <?php echo $article['featured'] ? 'background:#28a745;color:white;' : 'background:#dc3545;color:white;'; ?>">
                                        <?php echo $article['featured'] ? 'DA' : 'NE'; ?>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <a href="administracija.php?edit=<?php echo $article['id']; ?>" class="btn-primary" style="padding:4px 10px;font-size:12px;">Uredi</a>
                                <a href="administracija.php?action=delete&id=<?php echo $article['id']; ?>" class="btn-secondary" style="padding:4px 10px;font-size:12px;" onclick="return confirm('Jeste li sigurni da želite obrisati članak?');">Obriši</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <footer>
        <div class="container">
            &copy; <?php echo date('Y'); ?> El Confidencial. Sva prava pridržana.
        </div>
    </footer>
</body>
</html>