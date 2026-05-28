<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-white shadow-md min-h-screen p-4 flex flex-col justify-between fixed left-0 top-0 h-full z-40" style="top:64px;">
    <nav class="space-y-1 mt-4">

        <!-- Main -->
        <p class="text-xs font-bold text-gray-400 uppercase px-4 mb-2">Main</p>
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $current=='dashboard.php' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-600' ?> transition">
            <span>📊</span> Dashboard
        </a>
        <a href="job_entry.php" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $current=='job_entry.php' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-600' ?> transition">
            <span>💼</span> Job Entry
        </a>

        <!-- Finance -->
        <p class="text-xs font-bold text-gray-400 uppercase px-4 mb-2 mt-4">Finance</p>
        <a href="ledger.php" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $current=='ledger.php' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-600' ?> transition">
            <span>💰</span> Ledger
        </a>
        <a href="invoices.php" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $current=='invoices.php' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-600' ?> transition">
            <span>🧾</span> Invoices
        </a>
        <a href="reports.php" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $current=='reports.php' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-600' ?> transition">
            <span>📈</span> Reports
        </a>

        <!-- People -->
        <p class="text-xs font-bold text-gray-400 uppercase px-4 mb-2 mt-4">People</p>
        <a href="people.php" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $current=='people.php' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-600' ?> transition">
            <span>👥</span> People
        </a>
        <a href="workers.php" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $current=='workers.php' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-600' ?> transition">
            <span>👷</span> Workers
        </a>

        <!-- Inventory -->
        <p class="text-xs font-bold text-gray-400 uppercase px-4 mb-2 mt-4">Inventory</p>
        <a href="inventory.php" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $current=='inventory.php' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-600' ?> transition">
            <span>📦</span> Inventory
        </a>

        <!-- System -->
        <p class="text-xs font-bold text-gray-400 uppercase px-4 mb-2 mt-4">System</p>
        <a href="settings.php" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $current=='settings.php' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-600' ?> transition">
            <span>⚙️</span> Settings
        </a>
        <a href="about.php" target="_blank" class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition">
            <span>🏢</span> About JRSphere
        </a>
        <a href="logout.php" class="flex items-center gap-3 px-4 py-2 rounded-lg text-red-500 hover:bg-red-50 transition">
            <span>🚪</span> Logout
        </a>

    </nav>

    <div class="text-center text-xs text-gray-400 py-4 border-t mt-4">
        Built by <a href="about.php" target="_blank" class="text-indigo-500 font-semibold">JRSphere&#8482;</a>
    </div>
</aside>
