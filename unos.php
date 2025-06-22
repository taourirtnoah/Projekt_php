<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

require_once 'connect.php';

$message = '';
$error = '';


$categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $categoriesStmt->fetchAll();

if ($_POST) {
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
        $slug = generateSlug($title);
        
        $slugCheck = $pdo->prepare("SELECT id FROM articles WHERE slug = ?");
        $slugCheck->execute([$slug]);
        if ($slugCheck->fetch()) {
            $slug .= '-' . time();
        }
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO articles (title, slug, content, excerpt, image_url, category_id, author, featured, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $slug, $content, $excerpt, $image_url, $category_id ?: null, $author, $featured, $status]);
            
            $message = 'Članak je uspješno dodan!';
            
            $_POST = [];
        } catch (PDOException $e) {
            $error = 'Greška pri dodavanju članka: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj novi članak - El Confidencial</title>
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
            max-width: 800px;
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
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #2c5aa0;
            text-decoration: none;
            font-family: 'Times New Roman', serif;
        }
        
        .tagline {
            font-size: 12px;
            color: #666;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 5px;
        }
        
        .back-link {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .back-link:hover {
            background: #5a6268;
        }
        
        main {
            padding: 40px 0;
        }
        
        .form-container {
            background: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #2c5aa0;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            font-family: inherit;
        }
        
        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2c5aa0;
            box-shadow: 0 0 0 2px rgba(44, 90, 160, 0.2);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .btn {
            background: #2c5aa0;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #1a3a6b;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        .form-actions {
            text-align: center;
            margin-top: 30px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 25px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
        }
        
        nav {
            background: #343a40;
            color: white;
            padding: 10px 0;
        }
        
        .nav-links {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .nav-links li {
            margin: 0 15px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #2c5aa0;
        }
        
        .admin-links {
            margin-left: auto;
        }
        
        .admin-links a {
            background: #2c5aa0;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            font-size: 14px;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .admin-links a:hover {
            background: #1a3a6b;
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
                <a href="index.php" class="back-link">← Povratak na početnu</a>
            </div>
        </div>
    </header>
    
    <nav>
        <div class="container">
            <ul class="nav-links">
                <li><a href="index.php">Početna</a></li>
                <?php foreach ($categories as $category): ?>
                    <li><a href="kategorija.php?kategorija=<?= $category['slug'] ?>"><?= htmlspecialchars($category['name']) ?></a></li>
                <?php endforeach; ?>
                <div class="admin-links">
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                        <a href="logout.php">Odjava</a>
                    <?php else: ?>
                        <a href="login.php">Prijava</a>
                    <?php endif; ?>
                </div>
            </ul>
        </div>
    </nav>
    
    <main>
        <div class="container">
            <div class="form-container">
                <h1 class="form-title">Dodaj novi članak</h1>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="title">Naslov članka *</label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category_id">Kategorija</label>
                            <select id="category_id" name="category_id">
                                <option value="">Izaberite kategoriju</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" 
                                            <?= (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="author">Autor</label>
                            <input type="text" id="author" name="author" value="<?= htmlspecialchars($_POST['author'] ?? 'Admin') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="excerpt">Kratki opis (excerpt)</label>
                        <textarea id="excerpt" name="excerpt" rows="3"><?= htmlspecialchars($_POST['excerpt'] ?? '') ?></textarea>
                        <div class="help-text">Kratak opis članka koji će se prikazivati na listi članaka</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="image_url">URL slike</label>
                        <input type="url" id="image_url" name="image_url" value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>">
                        <div class="help-text">Unesite URL slike koja će se prikazivati uz članak</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Sadržaj članka *</label>
                        <textarea id="content" name="content" rows="15" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="published" <?= (($_POST['status'] ?? 'published') === 'published') ? 'selected' : '' ?>>Objavljeno</option>
                                <option value="draft" <?= (($_POST['status'] ?? '') === 'draft') ? 'selected' : '' ?>>Skica</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="featured" name="featured" value="1" 
                                       <?= isset($_POST['featured']) ? 'checked' : '' ?>>
                                <label for="featured">Istaknuti članak</label>
                            </div>
                            <div class="help-text">Istaknuti članci se prikazuju na vrhu stranice</div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">Dodaj članak</button>
                        <a href="index.php" class="btn">Odustani</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>