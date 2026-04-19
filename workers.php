<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

// Handle delete worker
if(isset($_GET['delete_worker'])){
    $worker_id = intval($_GET['delete_worker']);
    try {
        $pdo->prepare("DELETE FROM workers WHERE id = ?")->execute([$worker_id]);
        $success = "Worker deleted successfully!";
    } catch(Exception $e){
        $error = "Cannot delete worker — they may have jobs assigned!";
    }
}

// Fetch all workers
$workers = $pdo->query("SELECT * FROM workers ORDER BY worker_name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workers | JRS OfficeTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <nav class="bg-indigo-600 text-white px-6 py-4 flex justify-between items-center shadow">
        <h1 class="text-xl font-bold">JRS OfficeTrack</h1>
        <div class="flex items-center gap-4">
            <span class="text-sm">Welcome, <?= $_SESSION['admin'] ?></span>
            <a href="logout.php" class="bg-white text-indigo-600 text-sm font-semibold px-4 py-1 rounded-lg hover:bg-gray-100">Logout</a>
        </div>
    </nav>

    <div class="flex">
        <aside class="w-64 bg-white shadow-md min-h-screen p-6">
            <nav class="space-y-2">
                <a href="dashboard.php" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">Dashboard</a>
                <a href="job_entry.php" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">Job Entry</a>
                <a href="ledger.php" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">Ledger</a>
                <a href="workers.php" class="block px-4 py-2 rounded-lg bg-indigo-50 text-indigo-600 font-semibold">Workers</a>
                <a href="reports.php" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">Reports</a>
            </nav>
        </aside>

        <main class="flex-1 p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Worker Records</h2>

            <?php if(isset($success)): ?>
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6">&#10003; <?= $success ?></div>
            <?php endif; ?>
            <?php if(isset($error)): ?>
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-6">&#10007; <?= $error ?></div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-indigo-500">
                    <p class="text-sm text-gray-500">Total Workers</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-1"><?= count($workers) ?></p>
                </div>
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-green-500">
                    <p class="text-sm text-gray-500">Total Jobs Done</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">
                        <?= array_sum(array_column($workers, 'total_jobs')) ?>
                    </p>
                </div>
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-yellow-500">
                    <p class="text-sm text-gray-500">Total Paid Out</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-1">
                        &#2547;<?= number_format(array_sum(array_column($workers, 'total_paid')), 2) ?>
                    </p>
                </div>
            </div>

            <!-- Workers Table -->
            <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="text-lg font-bold text-gray-700 mb-4">All Workers</h3>
                <?php if(count($workers) > 0): ?>
                <table class="w-full text-sm text-left">
                    <thead class="bg-indigo-50 text-indigo-600">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Worker Name</th>
                            <th class="px-4 py-3">Service Type</th>
                            <th class="px-4 py-3">Total Jobs</th>
                            <th class="px-4 py-3">Total Paid Out</th>
                            <th class="px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($workers as $i => $w): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3"><?= $i + 1 ?></td>
                            <td class="px-4 py-3 font-semibold"><?= htmlspecialchars($w['worker_name']) ?></td>
                            <td class="px-4 py-3">
                                <span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full text-xs font-semibold">
                                    <?= $w['service_type'] ?>
                                </span>
                            </td>
                            <td class="px-4 py-3"><?= $w['total_jobs'] ?> jobs</td>
                            <td class="px-4 py-3 font-semibold text-red-600">&#2547;<?= number_format($w['total_paid'], 2) ?></td>
                            <td class="px-4 py-3">
                                <a href="?delete_worker=<?= $w['id'] ?>"
                                    onclick="return confirm('Delete <?= htmlspecialchars($w['worker_name']) ?>? This cannot be undone!')"
                                    class="bg-red-100 text-red-600 hover:bg-red-200 px-3 py-1 rounded-lg text-xs font-semibold">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-gray-400 text-sm">No workers added yet. Add workers from the Job Entry page.</p>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <footer class="text-center text-xs text-gray-400 py-4">Built by JRSphere</footer>

</body>
</html>
