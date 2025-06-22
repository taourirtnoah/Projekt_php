<?php
require_once 'connect.php';

$articleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$articleId) {
    header('Location: index.php');
    exit;
}


$stmt = $pdo->prepare("
    SELECT a.*, c.name as category_name, c.slug as category_slug 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    WHERE a.id = ? AND a.status = 'published'
");
$stmt->execute([$articleId]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: index.php');
    exit;
}


$updateViews = $pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?");
$updateViews->execute([$articleId]);


$relatedStmt = $pdo->prepare("
    SELECT a.*, c.name as category_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    WHERE a.category_id = ? AND a.id != ? AND a.status = 'published' 
    ORDER BY a.created_at DESC LIMIT 3
");
$relatedStmt->execute([$article['category_id'], $articleId]);
$relatedArticles = $relatedStmt->fetchAll();


$categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $categoriesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']) ?> - El Confidencial</title>
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
            width: unset;
            text-align: center;
            box-sizing: border-box;
        }
        
        .admin-links a:hover {
            background: #c82333;
            color: white;
        }
        
        main {
            padding: 40px 0;
        }
        
        .article-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }
        
        .article-main {
            background: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .article-category {
            color: #2c5aa0;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }
        
        .article-title {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 20px;
            line-height: 1.2;
            color: #333;
        }
        
        .article-meta {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .article-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .article-content {
            font-size: 18px;
            line-height: 1.8;
            color: #333;
        }
        
        .article-content p {
            margin-bottom: 20px;
        }
        
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .sidebar-section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #2c5aa0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .related-article {
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .related-article:last-child {
            border-bottom: none;
        }
        
        .related-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .related-title a {
            text-decoration: none;
            color: #333;
        }
        
        .related-title a:hover {
            color: #2c5aa0;
        }
        
        .related-meta {
            font-size: 12px;
            color: #999;
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
        
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
        }
        
        @media (max-width: 968px) {
            .article-container {
                grid-template-columns: 1fr;
            }
            
            .article-main {
                padding: 25px;
            }
            
            .article-title {
                font-size: 32px;
            }
            
            .article-content {
                font-size: 16px;
            }
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
            
            .article-title {
                font-size: 28px;
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
                <?php foreach ($categories as $category): ?>
                    <li><a href="kategorija.php?kategorija=<?= $category['slug'] ?>"><?= htmlspecialchars($category['name']) ?></a></li>
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
                <a href="index.php">Početna</a> / 
                <?php if ($article['category_name']): ?>
                    <a href="kategorija.php?kategorija=<?= $article['category_slug'] ?>"><?= htmlspecialchars($article['category_name']) ?></a> / 
                <?php endif; ?>
                <?= htmlspecialchars($article['title']) ?>
            </div>
        </div>
    </div>
    
    <main>
        <div class="container">
            <div class="article-container">
                <article class="article-main">
                    <?php if ($article['category_name']): ?>
                        <div class="article-category"><?= htmlspecialchars($article['category_name']) ?></div>
                    <?php endif; ?>
                    
                    <h1 class="article-title"><?= htmlspecialchars($article['title']) ?></h1>
                    
                    <div class="article-meta">
                        <?= formatDate($article['created_at']) ?> | Autor: <?= htmlspecialchars($article['author']) ?> | <?= $article['views'] + 1 ?> pregleda
                    </div>
                    
                    <?php if ($article['image_url']): ?>
                        <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="article-image">
                    <?php endif; ?>
                    
                    <div class="article-content">
                        <?= nl2br(htmlspecialchars($article['content'])) ?>
                    </div>
                </article>
                
                <aside class="sidebar">
                    <?php if (!empty($relatedArticles)): ?>
                        <div class="sidebar-section">
                            <h2 class="sidebar-title">Povezani članci</h2>
                            <?php foreach ($relatedArticles as $related): ?>
                                <div class="related-article">
                                    <h3 class="related-title">
                                        <a href="clanak.php?id=<?= $related['id'] ?>">
                                            <?= htmlspecialchars($related['title']) ?>
                                        </a>
                                    </h3>
                                    <div class="related-meta">
                                        <?= formatDate($related['created_at']) ?> | <?= htmlspecialchars($related['category_name']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="sidebar-section">
                        <h2 class="sidebar-title">Kategorije</h2>
                        <?php foreach ($categories as $category): ?>
                            <div class="related-article">
                                <h3 class="related-title">
                                    <a href="kategorija.php?kategorija=<?= $category['slug'] ?>">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </a>
                                </h3>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </aside>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> El Confidential. Sva prava zadržana.</p>
        </div>
    </footer>
</body>
</html>