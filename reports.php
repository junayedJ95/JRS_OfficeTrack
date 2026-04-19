<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

$filter_date  = $_GET['filter_date'] ?? '';
$filter_month = $_GET['filter_month'] ?? '';

$income = 0; $expenses = 0; $net = 0;
$jobs = [];

if($filter_date){
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_payment),0) FROM jobs WHERE job_date = ?");
    $stmt->execute([$filter_date]);
    $income = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(worker_pay),0) FROM jobs WHERE job_date = ?");
    $stmt->execute([$filter_date]);
    $expenses = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT j.*, w.worker_name FROM jobs j LEFT JOIN workers w ON j.worker_id = w.id WHERE j.job_date = ? ORDER BY j.created_at DESC");
    $stmt->execute([$filter_date]);
    $jobs = $stmt->fetchAll();

} elseif($filter_month){
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_payment),0) FROM jobs WHERE DATE_FORMAT(job_date, '%Y-%m') = ?");
    $stmt->execute([$filter_month]);
    $income = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(worker_pay),0) FROM jobs WHERE DATE_FORMAT(job_date, '%Y-%m') = ?");
    $stmt->execute([$filter_month]);
    $expenses = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT j.*, w.worker_name FROM jobs j LEFT JOIN workers w ON j.worker_id = w.id WHERE DATE_FORMAT(j.job_date, '%Y-%m') = ? ORDER BY j.created_at DESC");
    $stmt->execute([$filter_month]);
    $jobs = $stmt->fetchAll();
}

$net = $income - $expenses;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | JRS OfficeTrack</title>
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
                <a href="workers.php" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">Workers</a>
                <a href="reports.php" class="block px-4 py-2 rounded-lg bg-indigo-50 text-indigo-600 font-semibold">Reports</a>
            </nav>
        </aside>

        <main class="flex-1 p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Financial Reports</h2>

            <!-- Filter -->
            <div class="bg-white rounded-2xl shadow p-6 mb-8">
                <h3 class="text-lg font-bold text-gray-700 mb-4">Filter Report</h3>
                <form method="GET" class="flex gap-4 flex-wrap items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">By Specific Date</label>
                        <input type="date" name="filter_date" value="<?= $filter_date ?>"
                            class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">By Month</label>
                        <input type="month" name="filter_month" value="<?= $filter_month ?>"
                            class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2 rounded-lg transition duration-200">
                        Generate Report
                    </button>
                    <a href="reports.php"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold px-6 py-2 rounded-lg transition duration-200">
                        Clear
                    </a>
                </form>
            </div>

            <?php if($filter_date || $filter_month): ?>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-green-500">
                    <p class="text-sm text-gray-500">Total Income</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">&#2547;<?= number_format($income, 2) ?></p>
                </div>
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-red-500">
                    <p class="text-sm text-gray-500">Total Expenses</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">&#2547;<?= number_format($expenses, 2) ?></p>
                </div>
                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-indigo-500">
                    <p class="text-sm text-gray-500">Net Profit</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-1">&#2547;<?= number_format($net, 2) ?></p>
                </div>
            </div>

            <!-- Jobs Breakdown -->
            <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="text-lg font-bold text-gray-700 mb-4">
                    Jobs Breakdown —
                    <?= $filter_date ? $filter_date : $filter_month ?>
                </h3>
                <?php if(count($jobs) > 0): ?>
                <table class="w-full text-sm text-left">
                    <thead class="bg-indigo-50 text-indigo-600">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Service</th>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3">Worker</th>
                            <th class="px-4 py-3">Payment</th>
                            <th class="px-4 py-3">Worker Pay</th>
                            <th class="px-4 py-3">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($jobs as $job): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3"><?= $job['job_date'] ?></td>
                            <td class="px-4 py-3"><?= $job['service_type'] ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($job['client_name']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($job['worker_name']) ?></td>
                            <td class="px-4 py-3">&#2547;<?= number_format($job['total_payment'], 2) ?></td>
                            <td class="px-4 py-3 text-red-600">&#2547;<?= number_format($job['worker_pay'], 2) ?></td>
                            <td class="px-4 py-3 text-green-600">&#2547;<?= number_format($job['business_profit'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-gray-400 text-sm">No jobs found for this period.</p>
                <?php endif; ?>
            </div>

            <?php else: ?>
            <div class="bg-white rounded-2xl shadow p-12 text-center">
                <p class="text-gray-400 text-lg">Select a date or month above to generate a report.</p>
            </div>
            <?php endif; ?>

        </main>
    </div>

    <footer class="text-center text-xs text-gray-400 py-4">
        Built by <a href="about.php" target="_blank" class="text-indigo-500 hover:text-indigo-700 font-semibold">JRSphere&#8482;</a>
    </footer>

</body>
</html>