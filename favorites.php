<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: auth.php'); exit; }

require_once 'config/db.php';
require_once 'config/heroes_data.php';
require_once 'config/items_data.php';
$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['user_id']]);
$favorites = $stmt->fetchAll();

// Мета по типам разделов (название + эмодзи + страница детали)
$typeMeta = [
    'hero'     => ['label' => 'Герои',              'icon' => '🐉', 'page' => 'hero.php'],
    'item'     => ['label' => 'Предметы',           'icon' => '💎', 'page' => 'item.php'],
    'mechanic' => ['label' => 'Игровые механики',   'icon' => '⚡', 'page' => 'mechanic.php'],
    'tactic'   => ['label' => 'Функциональные роли','icon' => '🛡️', 'page' => 'tactic.php'],
    'object'   => ['label' => 'Адаптация',           'icon' => '🧩', 'page' => 'object.php'],
    'synergy'  => ['label' => 'Синергия героев',     'icon' => '🤝', 'page' => 'synergy.php'],
    'newbie'   => ['label' => 'Для новичков',        'icon' => '🎓', 'page' => 'newbie.php'],
    'setting'  => ['label' => 'Настройки игры',      'icon' => '⚙️', 'page' => 'setting.php'],
];

// Цвета атрибутов героев — 1 в 1 как в разделе «Герои»
$attrColors = [
    'strength'     => ['bg' => 'rgba(239,68,68,0.2)',  'color' => '#fca5a5', 'border' => '#ef4444'],
    'agility'      => ['bg' => 'rgba(16,185,129,0.2)', 'color' => '#6ee7b7', 'border' => '#10b981'],
    'intelligence' => ['bg' => 'rgba(59,130,246,0.2)', 'color' => '#93c5fd', 'border' => '#3b82f6'],
    'universal'    => ['bg' => 'rgba(245,158,11,0.2)', 'color' => '#fcd34d', 'border' => '#f59e0b'],
];

// Цвета категорий предметов — как в разделе «Предметы»
$itemCatColors = [
    'Артефакт'   => ['bg' => 'rgba(245,158,11,0.2)', 'color' => '#fcd34d', 'border' => '#f59e0b'],
    'Оружие'     => ['bg' => 'rgba(239,68,68,0.2)',  'color' => '#fca5a5', 'border' => '#ef4444'],
    'Расходники' => ['bg' => 'rgba(16,185,129,0.2)', 'color' => '#6ee7b7', 'border' => '#10b981'],
    'Поддержка'  => ['bg' => 'rgba(59,130,246,0.2)', 'color' => '#93c5fd', 'border' => '#3b82f6'],
];

// Готовим карточки
$cards = [];
foreach ($favorites as $fav) {
    $type = $fav['item_type'];
    $idv  = (int)$fav['item_id'];
    $meta = $typeMeta[$type] ?? ['label' => 'Справочник', 'icon' => '📘', 'page' => $type . '.php'];

    $title = null;
    $catLabel = '';
    $catStyle = '';
    $accent = '#8b5cf6';

    if ($type === 'hero') {
        $h = $heroesData[$idv] ?? null;
        if (!$h) continue;
        $title = explode(' — ', $h['name'])[0];
        $catLabel = $h['attr_name'];
        $c = $attrColors[$h['attr']] ?? null;
        if ($c) {
            $catStyle = "background: {$c['bg']}; color: {$c['color']}; border: 1px solid {$c['border']};";
            $accent = $c['border'];
        }
    } elseif ($type === 'item') {
        $it = $itemsData[$idv] ?? null;
        if (!$it) continue;
        $title = $it['name'];
        $catLabel = $it['category'];
        $c = $itemCatColors[$it['category']] ?? ['bg' => 'rgba(6,182,212,0.2)', 'color' => '#67e8f9', 'border' => '#06b6d4'];
        $catStyle = "background: {$c['bg']}; color: {$c['color']}; border: 1px solid {$c['border']};";
        $accent = $c['border'];
    } else {
        // mechanic и остальные — данные из таблицы game_mechanic
        $q = $pdo->prepare("SELECT title, category FROM game_mechanic WHERE id_game_mechanic = ?");
        $q->execute([$idv]);
        $res = $q->fetch();
        if (!$res) continue;
        $title = $res['title'];
        $catLabel = $res['category'];
        $catStyle = "background: rgba(139,92,246,0.2); color: #c4b5fd; border: 1px solid #8b5cf6;";
        $accent = '#8b5cf6';
    }

    $cards[] = [
        'id' => $idv,
        'page' => $meta['page'],
        'sectionLabel' => $meta['label'],
        'sectionIcon' => $meta['icon'],
        'title' => $title,
        'catLabel' => $catLabel,
        'catStyle' => $catStyle,
        'accent' => $accent,
    ];
}

$pageTitle = 'Избранное';
$currentPage = 'favorites';

require_once 'includes/header.php';
?>

<style>
.favorites-page .page-title { border-left: 4px solid #fbbf24; }
.favorites-page .nav-btn.active-page { background: rgba(251,191,36,0.15); color: #fff; }

.fav-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
    margin-top: 10px;
    padding-bottom: 40px;
}
.fav-card {
    --accent: #8b5cf6;
    position: relative;
    background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%);
    border: 1px solid #36414d;
    border-left: 4px solid var(--accent);
    border-radius: 14px;
    padding: 26px 24px;
    min-height: 175px;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    gap: 14px;
    transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
}
.fav-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 30px rgba(0,0,0,0.4);
    border-color: var(--accent);
}
.fav-section {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    align-self: flex-start;
    background: rgba(255,255,255,0.06);
    border: 1px solid #3a4a5c;
    color: #cbd5e1;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}
.fav-name {
    color: #fff;
    font-size: 24px;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
}
.fav-cat {
    display: inline-flex;
    align-self: flex-start;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}
.fav-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 70px 20px;
    color: #8f98a0;
}
.fav-empty i { font-size: 64px; margin-bottom: 18px; color: #4a5568; display: block; }
.fav-empty p { font-size: 16px; max-width: 460px; margin: 0 auto; }
</style>

<div class="page-header">
    <h1 class="page-title">🔖 Избранное</h1>
</div>

<div class="fav-grid">
    <?php if (empty($cards)): ?>
        <div class="fav-empty">
            <i class="fas fa-bookmark"></i>
            <p>Ваш список избранного пуст. Добавляйте героев, предметы и механики кнопкой «Добавить в избранное».</p>
        </div>
    <?php else: ?>
        <?php foreach ($cards as $card): ?>
            <a href="<?= htmlspecialchars($card['page']) ?>?id=<?= $card['id'] ?>&from=favorites" class="fav-card" style="--accent: <?= htmlspecialchars($card['accent']) ?>;">
                <span class="fav-section"><?= $card['sectionIcon'] ?> <?= htmlspecialchars($card['sectionLabel']) ?></span>
                <h3 class="fav-name"><?= htmlspecialchars($card['title']) ?></h3>
                <?php if ($card['catLabel'] !== ''): ?>
                    <span class="fav-cat" style="<?= $card['catStyle'] ?>"><?= htmlspecialchars($card['catLabel']) ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>