<?php
session_start();
require_once 'connect.php';


$featuredStmt = $pdo->prepare("
    SELECT a.*, c.name as category_name, c.slug as category_slug 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    WHERE a.featured = 1 AND a.status = 'published' 
    ORDER BY a.created_at DESC 
    LIMIT 1
");
$featuredStmt->execute();
$featuredArticle = $featuredStmt->fetch();


$articlesStmt = $pdo->prepare("
    SELECT a.*, c.name as category_name, c.slug as category_slug 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    WHERE a.status = 'published' AND a.featured = 0
    ORDER BY a.created_at DESC 
    LIMIT 12
");
$articlesStmt->execute();
$articles = $articlesStmt->fetchAll();


$categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $categoriesStmt->fetchAll();


$articlesByCategory = [];
foreach ($articles as $article) {
    if ($article['category_slug']) {
        $articlesByCategory[$article['category_slug']][] = $article;
    }
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novine - Dnevne vijesti</title>
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
            text-align: center;
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
        
        main {
            padding: 40px 0;
        }
        
        .featured-article {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 50px;
        }
        
        .featured-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        
        .featured-content {
            padding: 30px;
        }
        
        .featured-category {
            color: #2c5aa0;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .featured-title {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        
        .featured-excerpt {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .featured-meta {
            font-size: 12px;
            color: #999;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2c5aa0;
            color: #2c5aa0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
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
            padding: 20px;
        }
        
        .article-category {
            color: #2c5aa0;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .article-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
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
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .article-meta {
            font-size: 11px;
            color: #999;
        }
        
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
        }
        
        .category-section {
            margin-bottom: 50px;
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
            
            .featured-title {
                font-size: 28px;
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
                <?php foreach (
                    isset(
                        $categories
                    ) ? $categories : [] as $category): ?>
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
    
    <main>
        <div class="container">
            <?php if ($featuredArticle): ?>
                <article class="featured-article">
                    <?php if ($featuredArticle['image_url']): ?>
                        <img src="<?= htmlspecialchars($featuredArticle['image_url']) ?>" alt="<?= htmlspecialchars($featuredArticle['title']) ?>" class="featured-image">
                    <?php endif; ?>
                    <div class="featured-content">
                        <?php if ($featuredArticle['category_name']): ?>
                            <div class="featured-category"><?= htmlspecialchars($featuredArticle['category_name']) ?></div>
                        <?php endif; ?>
                        <h1 class="featured-title">
                            <a href="clanak.php?id=<?= $featuredArticle['id'] ?>" style="text-decoration: none; color: inherit;">
                                <?= htmlspecialchars($featuredArticle['title']) ?>
                            </a>
                        </h1>
                        <p class="featured-excerpt"><?= htmlspecialchars($featuredArticle['excerpt'] ?: truncateText($featuredArticle['content'], 200)) ?></p>
                        <div class="featured-meta"><?= formatDate($featuredArticle['created_at']) ?> | <?= $featuredArticle['views'] ?> pregleda</div>
                    </div>
                </article>
            <?php endif; ?>
            
            <?php foreach ($articlesByCategory as $categorySlug => $categoryArticles): ?>
                <?php if (!empty($categoryArticles)): ?>
                    <section class="category-section">
                        <h2 class="section-title"><?= htmlspecialchars($categoryArticles[0]['category_name']) ?></h2>
                        <div class="articles-grid">
                            <?php foreach (array_slice($categoryArticles, 0, 3) as $article): ?>
                                <article class="article-card">
                                    <?php if ($article['image_url']): ?>
                                        <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="article-image">
                                    <?php endif; ?>
                                    <div class="article-content">
                                        <div class="article-category"><?= htmlspecialchars($article['category_name']) ?></div>
                                        <h3 class="article-title">
                                            <a href="clanak.php?id=<?= $article['id'] ?>">
                                                <?= htmlspecialchars($article['title']) ?>
                                            </a>
                                        </h3>
                                        <p class="article-excerpt"><?= htmlspecialchars(truncateText($article['excerpt'] ?: $article['content'], 120)) ?></p>
                                        <div class="article-meta"><?= formatDate($article['created_at']) ?></div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> El Confidential. Sva prava zadržana.</p>
        </div>
    </footer>
</body>
</html>