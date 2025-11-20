<?php
$jsonUrl = '';
$data = @file_get_contents($jsonUrl);

$sources = [];
if ($data !== false) {
    $decoded_data = json_decode($data, true);
    $sources = $decoded_data['sources'] ?? [];
}

$years = [];
$all_statuses = [];
foreach($sources as $s) { 
    $y = substr($s['addedDate'], 0, 4); 
    $years[$y] = true;
    foreach($s['status'] as $status) {
        $all_statuses[$status] = true;
    }
}
ksort($years);
$unique_statuses = array_keys($all_statuses);
sort($unique_statuses);

$totalGames = array_sum(array_column($sources, 'gamesCount'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hydra + Apollo Resources Viewer - Enhanced</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body { 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
    color: #e2e8f0; 
    min-height: 100vh;
}

.header {
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(71, 85, 105, 0.3);
    position: sticky;
    top: 0;
    z-index: 100;
    padding: 1.5rem 1rem;
}

.header-content {
    max-width: 1400px;
    margin: 0 auto;
}

h1 { 
    background: linear-gradient(to right, #60a5fa, #a78bfa);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.header-links {
    color: #94a3b8;
    font-size: 0.95rem;
}

.header-links a {
    color: #60a5fa;
    text-decoration: none;
    transition: color 0.2s;
}

.header-links a:hover {
    color: #93c5fd;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 1.5rem 1rem;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: rgba(30, 41, 59, 0.5);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(71, 85, 105, 0.3);
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-icon.blue { background: rgba(96, 165, 250, 0.2); }
.stat-icon.purple { background: rgba(167, 139, 250, 0.2); }
.stat-icon.green { background: rgba(74, 222, 128, 0.2); }

.stat-label {
    color: #94a3b8;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.stat-value {
    color: #fff;
    font-size: 1.75rem;
    font-weight: 700;
}

/* Controls Panel */
.controls {
    background: rgba(30, 41, 59, 0.5);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(71, 85, 105, 0.3);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.search-bar {
    position: relative;
    margin-bottom: 1.5rem;
}

.search-bar input {
    width: 100%;
    padding: 0.875rem 1rem 0.875rem 2.75rem;
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(71, 85, 105, 0.5);
    border-radius: 10px;
    color: #fff;
    font-size: 1rem;
    transition: all 0.2s;
}

.search-bar input:focus {
    outline: none;
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 1.25rem;
}

.control-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
    align-items: center;
}

.control-group {
    flex: 1;
    min-width: 200px;
}

.control-label {
    display: block;
    color: #94a3b8;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
}

select {
    width: 100%;
    padding: 0.75rem 1rem;
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(71, 85, 105, 0.5);
    border-radius: 8px;
    color: #fff;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s;
}

select:focus {
    outline: none;
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
}

.view-toggle {
    display: flex;
    gap: 0.5rem;
}

.view-btn {
    padding: 0.75rem 1rem;
    background: rgba(51, 65, 85, 0.8);
    border: 1px solid rgba(71, 85, 105, 0.5);
    border-radius: 8px;
    color: #94a3b8;
    cursor: pointer;
    transition: all 0.2s;
}

.view-btn:hover {
    background: rgba(71, 85, 105, 0.8);
    color: #e2e8f0;
}

.view-btn.active {
    background: #3b82f6;
    color: #fff;
    border-color: #3b82f6;
}

/* Filter Pills */
.filter-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.pill {
    padding: 0.5rem 1rem;
    background: rgba(51, 65, 85, 0.8);
    border: 1px solid rgba(71, 85, 105, 0.5);
    border-radius: 20px;
    color: #94a3b8;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
    user-select: none;
}

.pill:hover {
    background: rgba(71, 85, 105, 0.8);
    color: #e2e8f0;
}

.pill.selected {
    background: #3b82f6;
    color: #fff;
    border-color: #3b82f6;
}

.pill.year.selected {
    background: #a855f7;
    border-color: #a855f7;
}

/* Resources Grid */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.grid.list-view {
    grid-template-columns: 1fr;
}

.card {
    background: rgba(30, 41, 59, 0.5);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(71, 85, 105, 0.3);
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(to bottom, #60a5fa, #a78bfa);
    opacity: 0;
    transition: opacity 0.3s;
}

.card:hover {
    transform: translateY(-4px);
    border-color: rgba(96, 165, 250, 0.5);
    box-shadow: 0 10px 30px rgba(96, 165, 250, 0.2);
}

.card:hover::before {
    opacity: 1;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
}

.card h2 {
    color: #fff;
    font-size: 1.25rem;
    font-weight: 600;
    flex: 1;
}

.favorite-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.5rem;
    padding: 0;
    transition: all 0.2s;
    filter: grayscale(1);
}

.favorite-btn:hover {
    filter: grayscale(0);
    transform: scale(1.1);
}

.favorite-btn.active {
    filter: grayscale(0);
}

.card-description {
    color: #cbd5e1;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.card-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #94a3b8;
}

.meta-label {
    font-weight: 600;
}

.meta-value {
    color: #e2e8f0;
}

.status-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.status-tag {
    padding: 0.25rem 0.75rem;
    background: rgba(96, 165, 250, 0.2);
    color: #93c5fd;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.card-actions {
    display: flex;
    gap: 0.75rem;
}

.btn {
    flex: 1;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-primary {
    background: #3b82f6;
    color: #fff;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.btn-secondary {
    background: rgba(51, 65, 85, 0.8);
    color: #e2e8f0;
}

.btn-secondary:hover {
    background: rgba(71, 85, 105, 0.9);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: rgba(30, 41, 59, 0.5);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(71, 85, 105, 0.3);
    border-radius: 12px;
}

.empty-state-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state-text {
    color: #94a3b8;
    font-size: 1.125rem;
}

.notification {
    position: fixed;
    top: 100px;
    right: 20px;
    background: #10b981;
    color: #fff;
    padding: 1rem 1.5rem;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
    font-weight: 600;
    z-index: 1000;
    opacity: 0;
    transform: translateX(400px);
    transition: all 0.3s ease;
}

.notification.show {
    opacity: 1;
    transform: translateX(0);
}

@media (max-width: 768px) {
    h1 { font-size: 1.75rem; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .grid { grid-template-columns: 1fr; }
    .control-row { flex-direction: column; }
    .control-group { width: 100%; min-width: 0; }
}
</style>
</head>
<body>

<div class="header">
    <div class="header-content">
        <h1>Hydra + Apollo Resource Library</h1>
        <div class="header-links">
            Browse and manage game resources • 
            <a href="https://apollosource.com" target="_blank" rel="noopener">ApolloSource</a> • 
            <a href="https://hydralauncher.gg/" target="_blank" rel="noopener">Hydra Launcher</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">📦</div>
            <div>
                <div class="stat-label">Total Resources</div>
                <div class="stat-value"><?=count($sources)?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple">🎮</div>
            <div>
                <div class="stat-label">Total Games</div>
                <div class="stat-value"><?=$totalGames?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">✓</div>
            <div>
                <div class="stat-label">Filtered Results</div>
                <div class="stat-value" id="filteredCount"><?=count($sources)?></div>
            </div>
        </div>
    </div>

    <div class="controls">
        <div class="search-bar">
            <span class="search-icon">🔍</span>
            <input type="text" id="search" placeholder="Search resources by title or description...">
        </div>

        <div class="control-row">
            <div class="control-group">
                <label class="control-label">Sort By</label>
                <select id="sortBy">
                    <option value="dateDesc">Newest First</option>
                    <option value="dateAsc">Oldest First</option>
                    <option value="gamesDesc">Most Games</option>
                    <option value="gamesAsc">Fewest Games</option>
                    <option value="nameAsc">Name (A-Z)</option>
                    <option value="nameDesc">Name (Z-A)</option>
                </select>
            </div>
            <div class="view-toggle">
                <button class="view-btn active" data-view="grid">⊞ Grid</button>
                <button class="view-btn" data-view="list">☰ List</button>
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label class="control-label">Status</label>
            <div class="filter-pills" id="statusFilters">
                <?php foreach($unique_statuses as $s): ?>
                <div class="pill" data-status="<?=htmlspecialchars($s)?>"><?=htmlspecialchars($s)?></div>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <label class="control-label">Year Added</label>
            <div class="filter-pills" id="yearFilters">
                <?php foreach(array_keys($years) as $y): ?>
                <div class="pill year" data-year="<?=$y?>"><?=$y?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="grid" id="grid">
        <?php foreach($sources as $s): ?>
        <div class="card" 
             data-title="<?=htmlspecialchars(strtolower($s['title']))?>"
             data-desc="<?=htmlspecialchars(strtolower($s['description']))?>"
             data-status="<?=htmlspecialchars(implode(',',$s['status']))?>" 
             data-year="<?=substr($s['addedDate'],0,4)?>"
             data-date="<?=$s['addedDate']?>"
             data-games="<?=$s['gamesCount']?>"
             data-url="<?=htmlspecialchars($s['url'])?>">
            
            <div class="card-header">
                <h2><?=htmlspecialchars($s['title'])?></h2>
                <button class="favorite-btn" onclick="toggleFavorite(this, '<?=htmlspecialchars($s['url'], ENT_QUOTES)?>')">⭐</button>
            </div>

            <div class="card-description"><?=htmlspecialchars($s['description'])?></div>

            <div class="card-meta">
                <div class="meta-item">
                    <span>🎮</span>
                    <span class="meta-label">Games:</span>
                    <span class="meta-value"><?=$s['gamesCount']?></span>
                </div>
                <div class="meta-item">
                    <span>📅</span>
                    <span class="meta-label">Added:</span>
                    <span class="meta-value"><?=$s['addedDate']?></span>
                </div>
            </div>

            <div class="status-tags">
                <?php foreach($s['status'] as $status): ?>
                <span class="status-tag"><?=htmlspecialchars($status)?></span>
                <?php endforeach; ?>
            </div>

            <div class="card-actions">
                <button class="btn btn-primary" onclick="copyUrl('<?=htmlspecialchars($s['url'], ENT_QUOTES)?>')">
                    🔗 Copy URL
                </button>
                <a href="<?=htmlspecialchars($s['url'])?>" target="_blank" class="btn btn-secondary" style="text-decoration: none;">
                    ↗ Open
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="notification" id="notification">URL Copied!</div>

<script>
let selectedStatuses = [];
let selectedYears = [];
let favorites = JSON.parse(localStorage.getItem('hydra-favorites') || '[]');

const searchInput = document.getElementById('search');
const sortSelect = document.getElementById('sortBy');
const grid = document.getElementById('grid');
const cards = Array.from(grid.children);

// Initialize favorites
favorites.forEach(url => {
    const card = document.querySelector(`[data-url="${url}"]`);
    if (card) {
        card.querySelector('.favorite-btn').classList.add('active');
    }
});

// Search
searchInput.addEventListener('input', filterAndSort);

// Sort
sortSelect.addEventListener('change', filterAndSort);

// View toggle
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        grid.classList.toggle('list-view', btn.dataset.view === 'list');
    });
});

// Status filters
document.querySelectorAll('#statusFilters .pill').forEach(pill => {
    pill.addEventListener('click', () => {
        const status = pill.dataset.status;
        pill.classList.toggle('selected');
        if (selectedStatuses.includes(status)) {
            selectedStatuses = selectedStatuses.filter(s => s !== status);
        } else {
            selectedStatuses.push(status);
        }
        filterAndSort();
    });
});

// Year filters
document.querySelectorAll('#yearFilters .pill').forEach(pill => {
    pill.addEventListener('click', () => {
        const year = pill.dataset.year;
        pill.classList.toggle('selected');
        if (selectedYears.includes(year)) {
            selectedYears = selectedYears.filter(y => y !== year);
        } else {
            selectedYears.push(year);
        }
        filterAndSort();
    });
});

function filterAndSort() {
    const searchTerm = searchInput.value.toLowerCase();
    const sortBy = sortSelect.value;

    let filtered = cards.filter(card => {
        const title = card.dataset.title;
        const desc = card.dataset.desc;
        const matchSearch = !searchTerm || title.includes(searchTerm) || desc.includes(searchTerm);

        const cardStatuses = card.dataset.status.split(',');
        const matchStatus = selectedStatuses.length === 0 || selectedStatuses.some(s => cardStatuses.includes(s));

        const cardYear = card.dataset.year;
        const matchYear = selectedYears.length === 0 || selectedYears.includes(cardYear);

        return matchSearch && matchStatus && matchYear;
    });

    // Sort
    filtered.sort((a, b) => {
        switch(sortBy) {
            case 'dateDesc': return b.dataset.date.localeCompare(a.dataset.date);
            case 'dateAsc': return a.dataset.date.localeCompare(b.dataset.date);
            case 'gamesDesc': return parseInt(b.dataset.games) - parseInt(a.dataset.games);
            case 'gamesAsc': return parseInt(a.dataset.games) - parseInt(b.dataset.games);
            case 'nameAsc': return a.dataset.title.localeCompare(b.dataset.title);
            case 'nameDesc': return b.dataset.title.localeCompare(a.dataset.title);
            default: return 0;
        }
    });

    cards.forEach(card => card.style.display = 'none');
    filtered.forEach(card => card.style.display = 'block');

    document.getElementById('filteredCount').textContent = filtered.length;

    if (filtered.length === 0) {
        if (!document.getElementById('emptyState')) {
            const empty = document.createElement('div');
            empty.id = 'emptyState';
            empty.className = 'empty-state';
            empty.innerHTML = '<div class="empty-state-icon">🔍</div><div class="empty-state-text">No resources found matching your filters</div>';
            grid.appendChild(empty);
        }
    } else {
        const empty = document.getElementById('emptyState');
        if (empty) empty.remove();
    }

    filtered.forEach(card => grid.appendChild(card));
}

function copyUrl(url) {
    const textarea = document.createElement('textarea');
    textarea.value = url;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);

    const notification = document.getElementById('notification');
    notification.classList.add('show');
    setTimeout(() => notification.classList.remove('show'), 2000);
}

function toggleFavorite(btn, url) {
    btn.classList.toggle('active');
    if (favorites.includes(url)) {
        favorites = favorites.filter(f => f !== url);
    } else {
        favorites.push(url);
    }
    localStorage.setItem('hydra-favorites', JSON.stringify(favorites));
}

filterAndSort();
</script>
</body>
</html>
