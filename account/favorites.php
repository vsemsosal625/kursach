<?php
require_once __DIR__ . '/../config/init.php';
requireUser();

require_once __DIR__ . '/../config/heroes_data.php';
require_once __DIR__ . '/../config/items_data.php';
$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['user_id']]);
$favorites = $stmt->fetchAll();

// Категории, которые на самом деле относятся к «Функциональным ролям» (хранятся как mechanic)
$tacticsCategories = ['Основа (1-3 позиция)', 'Поддержка (4-5 позиция)'];

// Цвета атрибутов героев (1 в 1 как в разделе «Герои»)
$attrColors = [
    'strength'     => ['bg' => 'rgba(239,68,68,0.2)',  'color' => '#fca5a5', 'border' => '#ef4444'],
    'agility'      => ['bg' => 'rgba(16,185,129,0.2)', 'color' => '#6ee7b7', 'border' => '#10b981'],
    'intelligence' => ['bg' => 'rgba(59,130,246,0.2)', 'color' => '#93c5fd', 'border' => '#3b82f6'],
    'universal'    => ['bg' => 'rgba(245,158,11,0.2)', 'color' => '#fcd34d', 'border' => '#f59e0b'],
];

// Цвета категорий предметов (как в разделе «Предметы»)
$itemCatColors = [
    'Артефакт'   => ['bg' => 'rgba(245,158,11,0.2)', 'color' => '#fcd34d', 'border' => '#f59e0b'],
    'Оружие'     => ['bg' => 'rgba(239,68,68,0.2)',  'color' => '#fca5a5', 'border' => '#ef4444'],
    'Расходники' => ['bg' => 'rgba(16,185,129,0.2)', 'color' => '#6ee7b7', 'border' => '#10b981'],
    'Поддержка'  => ['bg' => 'rgba(59,130,246,0.2)', 'color' => '#93c5fd', 'border' => '#3b82f6'],
];

// Стиль бейджа категории по цвету акцента раздела
function favCatStyle($accent) {
    $map = [
        '#8b5cf6' => ['rgba(139,92,246,0.2)', '#c4b5fd'],
        '#f59e0b' => ['rgba(245,158,11,0.2)', '#fcd34d'],
        '#10b981' => ['rgba(16,185,129,0.2)', '#6ee7b7'],
        '#3b82f6' => ['rgba(59,130,246,0.2)', '#93c5fd'],
        '#06b6d4' => ['rgba(6,182,212,0.2)',  '#67e8f9'],
    ];
    $pair = $map[$accent] ?? ['rgba(148,163,184,0.2)', '#cbd5e1'];
    return "background: {$pair[0]}; color: {$pair[1]}; border: 1px solid {$accent};";
}

$cards = [];
foreach ($favorites as $fav) {
    $type = $fav['item_type'];
    $idv  = (int)$fav['item_id'];

    $sectionLabel = ''; $sectionIcon = ''; $page = '';
    $title = ''; $catLabel = ''; $catStyle = ''; $accent = '#8b5cf6';

    if ($type === 'hero') {
        $h = $heroesData[$idv] ?? null;
        if (!$h) continue;
        $sectionLabel = 'Герои'; $sectionIcon = '🐉'; $page = BASE_URL . '/sections/heroes/hero.php';
        $title = explode(' — ', $h['name'])[0];
        $catLabel = $h['attr_name'];
        $c = $attrColors[$h['attr']] ?? null;
        if ($c) { $catStyle = "background: {$c['bg']}; color: {$c['color']}; border: 1px solid {$c['border']};"; $accent = $c['border']; }
    } elseif ($type === 'item') {
        $it = $itemsData[$idv] ?? null;
        if (!$it) continue;
        $sectionLabel = 'Предметы'; $sectionIcon = '💎'; $page = BASE_URL . '/sections/items/item.php';
        $title = $it['name'];
        $catLabel = $it['category'];
        $c = $itemCatColors[$it['category']] ?? ['bg' => 'rgba(6,182,212,0.2)', 'color' => '#67e8f9', 'border' => '#06b6d4'];
        $catStyle = "background: {$c['bg']}; color: {$c['color']}; border: 1px solid {$c['border']};"; $accent = $c['border'];
    } else {
        // Всё остальное хранится в таблице game_mechanic
        $q = $pdo->prepare("SELECT title, category FROM game_mechanic WHERE id_game_mechanic = ?");
        $q->execute([$idv]);
        $res = $q->fetch();
        if (!$res) continue;
        $title = $res['title'];
        $catLabel = $res['category'];

        switch ($type) {
            case 'tactic':
                $sectionLabel = 'Функциональные роли'; $sectionIcon = '📋'; $page = BASE_URL . '/sections/roles/tactic.php'; $accent = '#f59e0b';
                break;
            case 'object':
                $sectionLabel = 'Адаптация'; $sectionIcon = '🧩'; $page = BASE_URL . '/sections/adaptation/object.php'; $accent = '#10b981';
                break;
            case 'synergy':
                $sectionLabel = 'Синергия героев'; $sectionIcon = '🤝'; $page = BASE_URL . '/sections/synergy/synergy_detail.php'; $accent = '#10b981';
                break;
            case 'newbie':
                $sectionLabel = 'Для новичков'; $sectionIcon = '🎓'; $page = BASE_URL . '/sections/newbie/newbie_detail.php'; $accent = '#3b82f6';
                break;
            case 'setting':
                $sectionLabel = 'Настройки игры'; $sectionIcon = '⚙️'; $page = BASE_URL . '/sections/settings/setting_detail.php'; $accent = '#8b5cf6';
                break;
            case 'mechanic':
            default:
                // Механики и роли хранятся под item_type='mechanic' — различаем по категории
                if (in_array($res['category'], $tacticsCategories, true)) {
                    $sectionLabel = 'Функциональные роли'; $sectionIcon = '📋'; $page = BASE_URL . '/sections/roles/tactic.php'; $accent = '#f59e0b';
                } else {
                    $sectionLabel = 'Игровые механики'; $sectionIcon = '⚡'; $page = BASE_URL . '/sections/mechanics/mechanic.php'; $accent = '#8b5cf6';
                }
                break;
        }
        $catStyle = favCatStyle($accent);
    }

    $cards[] = [
        'id' => $idv,
        'page' => $page,
        'sectionLabel' => $sectionLabel,
        'sectionIcon' => $sectionIcon,
        'title' => $title,
        'catLabel' => $catLabel,
        'catStyle' => $catStyle,
        'accent' => $accent,
    ];
}

$pageTitle = 'Избранное';
$currentPage = 'favorites';

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.favorites-page .page-header { margin-bottom: 10px; }
.favorites-page .page-title { border-left: 4px solid #fbbf24; }
.favorites-page .nav-btn.active-page { background: rgba(251,191,36,0.15); color: #fff; }

.fav-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
    margin-top: 6px;
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>