<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// ── Fetch Admin Name ───────────────────────────────
$admin_data = $pdo->query("SELECT full_name FROM admin WHERE id = 1")->fetch();
$admin_name = $admin_data['full_name'] ?? $_SESSION['admin'];

// ── Stats ──────────────────────────────────────────
$balance        = $pdo->query("SELECT balance FROM account WHERE id = 1")->fetchColumn();
$today          = date('Y-m-d');

$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_payment), 0) FROM jobs WHERE job_date = ?");
$stmt->execute([$today]);
$today_income   = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(worker_pay), 0) FROM jobs WHERE job_date = ?");
$stmt->execute([$today]);
$today_expenses = $stmt->fetchColumn();

$today_net      = $today_income - $today_expenses;
$total_workers  = $pdo->query("SELECT COUNT(*) FROM workers")->fetchColumn();
$total_people   = $pdo->query("SELECT COUNT(*) FROM people")->fetchColumn();

// ── Recent Jobs ────────────────────────────────────
$recent_jobs = $pdo->query("
    SELECT j.*, w.worker_name
    FROM jobs j
    LEFT JOIN workers w ON j.worker_id = w.id
    ORDER BY j.created_at DESC LIMIT 5
")->fetchAll();
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

    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div style="margin-left:256px; margin-top:64px;" class="p-8">

        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-400 rounded-2xl p-6 mb-8 text-white shadow-lg">
            <h2 class="text-2xl font-bold">Welcome back, <?= htmlspecialchars($admin_name) ?>! 👋</h2>
            <p class="text-indigo-100 mt-1">Here's what's happening in your business today.</p>
            <p class="text-indigo-200 text-sm mt-1"><?= date('l, F j, Y') ?></p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

            <div class="bg-white rounded-2xl shadow p-6 border-l-4 border-indigo-500">
                <p class="text-sm text-gray-500">Main Balance</p>
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
                <p class="text-sm text-gray-500">Today's Net</p>
                <p class="text-3xl font-bold text-yellow-600 mt-1">&#2547;<?= number_format($today_net, 2) ?></p>
            </div>

        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

            <div class="bg-white rounded-2xl shadow p-6 flex items-center gap-4">
                <div class="bg-indigo-100 rounded-xl p-4 text-3xl">👷</div>
                <div>
                    <p class="text-sm text-gray-500">Total Workers</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $total_workers ?></p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow p-6 flex items-center gap-4">
                <div class="bg-green-100 rounded-xl p-4 text-3xl">👥</div>
                <div>
                    <p class="text-sm text-gray-500">Total People</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $total_people ?></p>
                </div>
            </div>

        </div>

        <!-- Recent Jobs -->
        <div class="bg-white rounded-2xl shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-700">Recent Jobs</h3>
                <a href="job_entry.php" class="text-sm text-indigo-600 hover:underline">+ Add New</a>
            </div>

            <?php if (count($recent_jobs) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-indigo-50 text-indigo-600">
                        <tr>
                            <th class="px-4 py-3 rounded-tl-lg">Date</th>
                            <th class="px-4 py-3">Service</th>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3">Worker</th>
                            <th class="px-4 py-3">Payment</th>
                            <th class="px-4 py-3 rounded-tr-lg">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_jobs as $job): ?>
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-4 py-3"><?= $job['job_date'] ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($job['service_type']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($job['client_name']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($job['worker_name'] ?? 'N/A') ?></td>
                            <td class="px-4 py-3">&#2547;<?= number_format($job['total_payment'], 2) ?></td>
                            <td class="px-4 py-3 text-green-600 font-semibold">&#2547;<?= number_format($job['business_profit'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-10 text-gray-400">
                <p class="text-5xl mb-3">📋</p>
                <p class="text-sm">No jobs recorded yet.</p>
                <a href="job_entry.php" class="mt-3 inline-block bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Add First Job</a>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Footer -->
    <div style="margin-left:256px;" class="text-center text-xs text-gray-400 py-4 border-t">
        Built by <a href="about.php" target="_blank" class="text-indigo-500 hover:text-indigo-700 font-semibold">JRSphere&#8482;</a>
    </div>

</body>
</html>