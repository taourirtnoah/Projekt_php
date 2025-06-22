<?php
require_once 'connect.php';

$categorySlug = isset($_GET['kategorija']) ? $_GET['kategorija'] : '';

if (!$categorySlug) {
    header('Location: index.php');
    exit;
}

// Get category
$categoryStmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
$categoryStmt->execute([$categorySlug]);
$category = $categoryStmt->fetch();

if (!$category) {
    header('Location: index.php');
    exit;
}

// Get articles for this category
$articlesStmt = $pdo->prepare("
    SELECT a.*, c.name as category_name, c.slug as category_slug 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    WHERE c.slug = ? AND a.status = 'published'
    ORDER BY a.created_at DESC
");
$articlesStmt->execute([$categorySlug]);
$articles = $articlesStmt->fetchAll();

// Get all categories for navigation
$categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $categoriesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($category['name']) ?> - El Confidencial</title>
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
        
        .nav-links a:hover,
        .nav-links a.active {
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
        
        main {
            padding: 40px 0;
        }
        
        .category-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .category-title {
            font-size: 48px;
            font-weight: bold;
            color: #2c5aa0;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        
        .category-count {
            font-size: 16px;
            color: #666;
        }
        
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .article-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .article-card:hover {
            transform: translateY(-5px);
        }
        
        .article-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .article-content {
            padding: 25px;
        }
        
        .article-category {
            color: #2c5aa0;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .article-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        
        .article-title a {
            text-decoration: none;
            color: #333;
        }
        
        .article-title a:hover {
            color: #2c5aa0;
        }
        
        .article-excerpt {
            font-size: 15px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .article-meta {
            font-size: 12px;
            color: #999;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .breadcrumb {
            background: white;
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .breadcrumb-links {
            font-size: 14px;
            color: #666;
        }
        
        .breadcrumb-links a {
            color: #2c5aa0;
            text-decoration: none;
        }
        
        .breadcrumb-links a:hover {
            text-decoration: underline;
        }
        
        .no-articles {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-articles h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
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
            
            .category-title {
                font-size: 36px;
            }
            
            .articles-grid {
                grid-template-columns: 1fr;
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
                <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="kategorija.php?kategorija=<?= $cat['slug'] ?>" 
                           <?= $cat['slug'] === $categorySlug ? 'class="active"' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                
                <div class="admin-links">
                    <a href="unos.php">+ članak</a>
                    <a href="administracija.php">Administracija</a>
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                        <a href="logout.php">Odjava</a>
                    <?php else: ?>
                        <a href="login.php">Prijava</a>
                    <?php endif; ?>
                </div>
            </ul>
        </div>
    </nav>
    
    <div class="breadcrumb">
        <div class="container">
            <div class="breadcrumb-links">
                <a href="index.php">Početna</a> / <?= htmlspecialchars($category['name']) ?>
            </div>
        </div>
    </div>
    
    <main>
        <div class="container">
            <div class="category-header">
                <h1 class="category-title"><?= htmlspecialchars($category['name']) ?></h1>
                <div class="category-count"><?= count($articles) ?> <?= count($articles) === 1 ? 'članak' : 'članaka' ?></div>
            </div>
            
            <?php if (!empty($articles)): ?>
                <div class="articles-grid">
                    <?php foreach ($articles as $article): ?>
                        <article class="article-card">
                            <?php if ($article['image_url']): ?>
                                <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="article-image">
                            <?php endif; ?>
                            <div class="article-content">
                                <div class="article-category"><?= htmlspecialchars($article['category_name']) ?></div>
                                <h2 class="article-title">
                                    <a href="clanak.php?id=<?= $article['id'] ?>">
                                        <?= htmlspecialchars($article['title']) ?>
                                    </a>
                                </h2>
                                <p class="article-excerpt">
                                    <?= htmlspecialchars(truncateText($article['excerpt'] ?: $article['content'], 150)) ?>
                                </p>
                                <div class="article-meta">
                                    <span><?= formatDate($article['created_at']) ?></span>
                                    <span><?= $article['views'] ?> pregleda</span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-articles">
                    <h2>Nema članaka u ovoj kategoriji</h2>
                    <p>Trenutno nema objavljenih članaka u kategoriji "<?= htmlspecialchars($category['name']) ?>".</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> El Confidential. Sva prava zadržana.</p>
        </div>
    </footer>
</body>
</html>