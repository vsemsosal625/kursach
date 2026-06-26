<?php
// config/game_content.php
// Данные героев и предметов хранятся в БД (таблицы `hero` и `item`).
// Этот файл — только логика (без самих данных):
//  - создаёт таблицы, если их нет;
//  - при первом запуске заполняет их значениями по умолчанию (из heroes_data.php / items_data.php);
//  - возвращает данные из БД в прежнем формате.
// Поэтому повторный импорт базы не нужен — данные появятся сами.

if (!function_exists('getDB')) {
    require_once __DIR__ . '/db.php';
}

function gc_heroAttrName($attr) {
    $m = ['strength' => 'Сила', 'agility' => 'Ловкость', 'intelligence' => 'Интеллект', 'universal' => 'Универсальный'];
    return $m[$attr] ?? 'Сила';
}

function gc_ensureTables($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS hero (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, attr VARCHAR(20) NOT NULL, attr_name VARCHAR(40) NOT NULL, attack VARCHAR(40) DEFAULT '', roles VARCHAR(255) DEFAULT '', base_str INT DEFAULT 0, base_agi INT DEFAULT 0, base_int INT DEFAULT 0, gain_str VARCHAR(20) DEFAULT '0', gain_agi VARCHAR(20) DEFAULT '0', gain_int VARCHAR(20) DEFAULT '0', abilities TEXT, image_url TEXT, description TEXT, tips TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS item (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, category VARCHAR(40) NOT NULL, cost VARCHAR(40) DEFAULT '', components TEXT, bonuses TEXT, effects TEXT, strong_against TEXT, image_url TEXT, description TEXT, tips TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function syncAndLoadHeroes($pdo, $defaults) {
    gc_ensureTables($pdo);
    $count = (int)$pdo->query("SELECT COUNT(*) FROM hero")->fetchColumn();
    if ($count === 0 && !empty($defaults)) {
        $stmt = $pdo->prepare("INSERT INTO hero (id, name, attr, attr_name, attack, roles, base_str, base_agi, base_int, gain_str, gain_agi, gain_int, abilities, image_url, description, tips) VALUES (:id, :name, :attr, :attr_name, :attack, :roles, :base_str, :base_agi, :base_int, :gain_str, :gain_agi, :gain_int, :abilities, :image_url, :description, :tips)");
        foreach ($defaults as $id => $h) {
            $abil = isset($h['abilities']) && is_array($h['abilities']) ? implode("\n", $h['abilities']) : (string)($h['abilities'] ?? '');
            $stmt->execute([
                ':id' => $id,
                ':name' => $h['name'] ?? '',
                ':attr' => $h['attr'] ?? 'strength',
                ':attr_name' => gc_heroAttrName($h['attr'] ?? 'strength'),
                ':attack' => $h['attack'] ?? '',
                ':roles' => $h['roles'] ?? '',
                ':base_str' => (int)($h['str'] ?? 0),
                ':base_agi' => (int)($h['agi'] ?? 0),
                ':base_int' => (int)($h['int'] ?? 0),
                ':gain_str' => (string)($h['s_gain'] ?? '0'),
                ':gain_agi' => (string)($h['a_gain'] ?? '0'),
                ':gain_int' => (string)($h['i_gain'] ?? '0'),
                ':abilities' => $abil,
                ':image_url' => $h['image_url'] ?? '',
                ':description' => $h['description'] ?? '',
                ':tips' => $h['tips'] ?? '',
            ]);
        }
    }
    $data = [];
    foreach ($pdo->query("SELECT * FROM hero ORDER BY id")->fetchAll() as $r) {
        $abil = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)($r['abilities'] ?? ''))), fn($x) => $x !== ''));
        $data[(int)$r['id']] = [
            'id' => (int)$r['id'],
            'name' => $r['name'],
            'attr' => $r['attr'],
            'attr_name' => $r['attr_name'],
            'attack' => $r['attack'],
            'roles' => $r['roles'],
            'str' => $r['base_str'],
            'agi' => $r['base_agi'],
            'int' => $r['base_int'],
            's_gain' => $r['gain_str'],
            'a_gain' => $r['gain_agi'],
            'i_gain' => $r['gain_int'],
            'abilities' => $abil,
            'image_url' => $r['image_url'],
            'description' => $r['description'],
            'tips' => $r['tips'],
        ];
    }
    return $data;
}

function syncAndLoadItems($pdo, $defaults) {
    gc_ensureTables($pdo);
    $count = (int)$pdo->query("SELECT COUNT(*) FROM item")->fetchColumn();
    if ($count === 0 && !empty($defaults)) {
        $stmt = $pdo->prepare("INSERT INTO item (id, name, category, cost, components, bonuses, effects, strong_against, image_url, description, tips) VALUES (:id, :name, :category, :cost, :components, :bonuses, :effects, :strong_against, :image_url, :description, :tips)");
        foreach ($defaults as $id => $it) {
            $stmt->execute([
                ':id' => $id,
                ':name' => $it['name'] ?? '',
                ':category' => $it['category'] ?? '',
                ':cost' => $it['cost'] ?? '',
                ':components' => $it['components'] ?? '',
                ':bonuses' => $it['bonuses'] ?? '',
                ':effects' => $it['effects'] ?? '',
                ':strong_against' => $it['strong_against'] ?? '',
                ':image_url' => $it['image_url'] ?? '',
                ':description' => $it['description'] ?? '',
                ':tips' => $it['tips'] ?? '',
            ]);
        }
    }
    $data = [];
    foreach ($pdo->query("SELECT * FROM item ORDER BY id")->fetchAll() as $r) {
        $data[(int)$r['id']] = [
            'id' => (int)$r['id'],
            'name' => $r['name'],
            'category' => $r['category'],
            'cost' => $r['cost'],
            'components' => $r['components'],
            'bonuses' => $r['bonuses'],
            'effects' => $r['effects'],
            'strong_against' => $r['strong_against'],
            'image_url' => $r['image_url'],
            'description' => $r['description'],
            'tips' => $r['tips'],
        ];
    }
    return $data;
}
