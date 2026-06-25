<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: auth.php'); exit; }

require_once 'config/db.php';
$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$favorites = $stmt->fetchAll();

// Функция для получения данных из нужных таблиц
function getDetails($pdo, $type, $id) {
    // Красивые названия и эмодзи для разных типов
    $labels = [
        'hero'     => ['label' => 'Герой', 'icon' => '🐉'],
        'item'     => ['label' => 'Предмет', 'icon' => '💎'],
        'mechanic' => ['label' => 'Игровая механика', 'icon' => '⚡'],
        'synergy'  => ['label' => 'Синергия', 'icon' => '🤝'],
        'newbie'   => ['label' => 'Для новичков', 'icon' => '🎓'],
        'tactic'   => ['label' => 'Тактика и роли', 'icon' => '🛡️'],
        'role'     => ['label' => 'Функциональная роль', 'icon' => '⚔️']
    ];

    // Если типа нет в списке выше, ставим дефолтное значение
    $ui = $labels[$type] ?? ['label' => 'Справочник', 'icon' => '📘'];

    try {
        if ($type === 'hero') {
            $stmt = $pdo->prepare("SELECT name as title, attribute as category FROM heroes WHERE id = ?");
        } elseif ($type === 'item') {
            $stmt = $pdo->prepare("SELECT name as title, type as category FROM items WHERE id = ?");
        } else {
            // ВСЕ ОСТАЛЬНЫЕ (mechanic, synergy, newbie, роли) ищем в таблице game_mechanic!
            $stmt = $pdo->prepare("SELECT title, category FROM game_mechanic WHERE id_game_mechanic = ?");
        }
        
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        
        if ($res) {
            return [
                'title' => $res['title'],
                'category' => $res['category'],
                'icon' => $ui['icon'],
                'label' => $ui['label']
            ];
        }
        return null;
    } catch (PDOException $e) {
        return null;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Избранное | Игровой справочник</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="main-wrapper">
        <h1 class="page-title"><i class="fas fa-bookmark" style="color: #fbbf24;"></i> Избранное</h1>
        
        <div class="favorites-grid">
            <?php if (empty($favorites)): ?>
                <p style="color: #8f98a0;">Ваш список избранного пуст.</p>
            <?php else: ?>
                <?php foreach ($favorites as $fav): 
                    $data = getDetails($pdo, $fav['item_type'], $fav['item_id']);
                    if (!$data) continue;
                ?>
                    <a href="<?= htmlspecialchars($fav['item_type']) ?>.php?id=<?= $fav['item_id'] ?>&from=favorites" class="fav-card">
                        <span class="type-badge"><?= $data['icon'] ?> <?= $data['label'] ?></span>
                        <h3><?= htmlspecialchars($data['title']) ?></h3>
                        <p class="category-text"><?= htmlspecialchars($data['category']) ?></p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/script.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const favLink = document.querySelector('.nav-btn[href="favorites.php"]');
            if (favLink) {
                favLink.style.background = 'rgba(251,191,36,0.15)';
                favLink.style.color = '#fff';
                // Если у тебя активная вкладка выделяется рамкой снизу:
                // favLink.style.borderBottom = '2px solid #fbbf24'; 
            }
        });
    </script>
</body>
</html>