<?php
$admin_name = $_SESSION['admin'] ?? 'Admin';

// Get admin full name from database
if (isset($pdo)) {
    $admin_data = $pdo->query("SELECT full_name FROM admin WHERE id = 1")->fetch();
    if ($admin_data && $admin_data['full_name']) {
        $admin_name = $admin_data['full_name'];
    }
    $notif_count = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")->fetchColumn();
}
?>

<nav class="fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-6 py-3 bg-indigo-600 shadow-lg" style="height:64px;">

    <!-- Left: App name only -->
    <div>
        <h1 class="text-white text-xl font-bold tracking-wide">JRS OfficeTrack</h1>
        <p class="text-indigo-200 text-xs"><?= date('l, F j, Y') ?></p>
    </div>

    <!-- Right: Notifications + Admin -->
    <div class="flex items-center gap-5">

        <!-- Notification Bell -->
        <div class="relative cursor-pointer" onclick="toggleNotifications()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <?php if (!empty($notif_count) && $notif_count > 0): ?>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center font-bold">
                <?= $notif_count ?>
            </span>
            <?php endif; ?>
        </div>

        <!-- Admin Info -->
        <a href="settings.php" class="flex items-center gap-2 bg-white bg-opacity-10 hover:bg-opacity-20 px-3 py-1.5 rounded-xl transition">
            <div class="h-7 w-7 bg-indigo-300 rounded-full flex items-center justify-center text-indigo-800 font-bold text-sm">
                <?= strtoupper(substr($admin_name, 0, 1)) ?>
            </div>
            <span class="text-white text-sm font-medium"><?= htmlspecialchars($admin_name) ?></span>
        </a>

    </div>
</nav>

<!-- Notification Panel -->
<div id="notif-panel" class="hidden fixed top-16 right-4 w-80 bg-white rounded-2xl shadow-2xl z-50 border border-gray-100 overflow-hidden">
    <div class="bg-indigo-600 text-white px-4 py-3 flex justify-between items-center">
        <span class="font-bold text-sm">Notifications</span>
        <a href="mark_read.php" class="text-xs text-indigo-200 hover:text-white">Mark all read</a>
    </div>
    <div class="max-h-72 overflow-y-auto" id="notif-list">
        <?php if (isset($pdo)):
            $notifs = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10")->fetchAll();
            if (count($notifs) > 0):
                foreach ($notifs as $n): ?>
                <div class="px-4 py-3 border-b hover:bg-gray-50 transition <?= $n['is_read'] ? 'opacity-50' : '' ?>">
                    <div class="flex items-start gap-2">
                        <span class="text-lg mt-0.5">
                            <?= $n['type'] === 'success' ? '✅' : ($n['type'] === 'warning' ? '⚠️' : ($n['type'] === 'danger' ? '❌' : 'ℹ️')) ?>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($n['title']) ?></p>
                            <p class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($n['message']) ?></p>
                            <p class="text-xs text-gray-400 mt-1"><?= date('M d, h:i A', strtotime($n['created_at'])) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach;
            else: ?>
                <div class="px-4 py-8 text-center text-gray-400 text-sm">
                    <p class="text-3xl mb-2">🔔</p>
                    <p>No notifications yet</p>
                </div>
            <?php endif;
        endif; ?>
    </div>
</div>

<script>
function toggleNotifications() {
    document.getElementById('notif-panel').classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
    const panel = document.getElementById('notif-panel');
    if (!e.target.closest('[onclick="toggleNotifications()"]') && !panel.classList.contains('hidden')) {
        panel.classList.add('hidden');
    }
});
</script>