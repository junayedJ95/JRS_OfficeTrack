<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

$balance = $pdo->query("SELECT balance FROM account WHERE id = 1")->fetchColumn();

$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_payment), 0) FROM jobs WHERE job_date = ?");
$stmt->execute([$today]);
$today_income = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(worker_pay), 0) FROM jobs WHERE job_date = ?");
$stmt->execute([$today]);
$today_expenses = $stmt->fetchColumn();

$today_net = $today_income - $today_expenses;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | JRS OfficeTrack</title>
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
                <a href="dashboard.php" class="block px-4 py-2 rounded-lg bg-indigo-50 text-indigo-600 font-semibold">Dashboard</a>
                <a href="job_entry.php" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">Job Entry</a>
                <a href="ledger.php" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">Ledger</a>
                <a href="workers.php" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">Workers</a>
                <a href="reports.php" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">Reports</a>
            </nav>
        </aside>

        <main class="flex-1 p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Welcome, Mahi</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-indigo-500">
                    <p class="text-sm text-gray-500">Main Account Balance</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-1">&#2547;<?= number_format($balance, 2) ?></p>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-green-500">
                    <p class="text-sm text-gray-500">Today's Income</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">&#2547;<?= number_format($today_income, 2) ?></p>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-red-500">
                    <p class="text-sm text-gray-500">Today's Expenses</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">&#2547;<?= number_format($today_expenses, 2) ?></p>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-yellow-500">
                    <p class="text-sm text-gray-500">Today's Net Balance</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-1">&#2547;<?= number_format($today_net, 2) ?></p>
                </div>

            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="text-lg font-bold text-gray-700 mb-4">Recent Jobs</h3>
                <?php
                $jobs = $pdo->query("SELECT * FROM jobs ORDER BY created_at DESC LIMIT 5")->fetchAll();
                ?>
                <?php if(count($jobs) > 0): ?>
                <table class="w-full text-sm text-left">
                    <thead class="bg-indigo-50 text-indigo-600">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Service</th>
                            <th class="px-4 py-3">Client</th>
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
                            <td class="px-4 py-3"><?= $job['client_name'] ?></td>
                            <td class="px-4 py-3">&#2547;<?= number_format($job['total_payment'], 2) ?></td>
                            <td class="px-4 py-3">&#2547;<?= number_format($job['worker_pay'], 2) ?></td>
                            <td class="px-4 py-3">&#2547;<?= number_format($job['business_profit'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-gray-400 text-sm">No jobs recorded yet.</p>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <footer class="text-center text-xs text-gray-400 py-4">
        <footer class="text-center text-xs text-gray-400 py-4">
    Built by <a href="about.php" target="_blank" class="text-indigo-500 hover:text-indigo-700 font-semibold">JRSphere&#8482;</a>
</footer>
    </footer>

</body>
</html>