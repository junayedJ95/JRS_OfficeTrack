<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

$success = '';
$error   = '';

// Handle RESET balance
if(isset($_GET['reset_balance'])){
    try {
        $pdo->query("UPDATE account SET balance = 0 WHERE id = 1");
        $pdo->query("INSERT INTO transactions (type, amount, description, transaction_date) VALUES ('debit', 0, 'Account balance manually reset to 0', CURDATE())");
        $success = "Account balance reset to 0!";
    } catch(Exception $e){
        $error = "Failed to reset balance!";
    }
}

// Handle CLEAR all transactions
if(isset($_GET['clear_history'])){
    try {
        $pdo->query("DELETE FROM transactions");
        $success = "All transaction history cleared!";
    } catch(Exception $e){
        $error = "Failed to clear history!";
    }
}

// Handle DELETE single transaction
if(isset($_GET['delete_txn'])){
    $txn_id = intval($_GET['delete_txn']);
    try {
        // Get transaction first to reverse balance
        $txn = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
        $txn->execute([$txn_id]);
        $txn_data = $txn->fetch();

        if($txn_data){
            if($txn_data['type'] === 'credit'){
                $pdo->prepare("UPDATE account SET balance = balance - ? WHERE id = 1")->execute([$txn_data['amount']]);
            } else {
                $pdo->prepare("UPDATE account SET balance = balance + ? WHERE id = 1")->execute([$txn_data['amount']]);
            }
            $pdo->prepare("DELETE FROM transactions WHERE id = ?")->execute([$txn_id]);
            $success = "Transaction deleted and balance updated!";
        }
    } catch(Exception $e){
        $error = "Failed to delete transaction!";
    }
}

// Handle ADD manual transaction
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $type        = $_POST['type'];
    $amount      = trim($_POST['amount']);
    $description = trim($_POST['description']);
    $date        = $_POST['transaction_date'];

    if(empty($amount) || !is_numeric($amount) || floatval($amount) <= 0){
        $error = "Amount must be a valid number greater than 0!";
    } elseif(empty($description)){
        $error = "Description is required!";
    } else {
        try {
            $amount = floatval($amount);

            if($type === 'credit'){
                $pdo->prepare("UPDATE account SET balance = balance + ? WHERE id = 1")->execute([$amount]);
            } else {
                $pdo->prepare("UPDATE account SET balance = balance - ? WHERE id = 1")->execute([$amount]);
            }

            $pdo->prepare("INSERT INTO transactions (type, amount, description, transaction_date) VALUES (?, ?, ?, ?)")
                ->execute([$type, $amount, $description, $date]);

            $success = "Transaction added successfully!";
        } catch(Exception $e){
            $error = "Something went wrong. Please try again!";
        }
    }
}

// Get balance
$balance = $pdo->query("SELECT balance FROM account WHERE id = 1")->fetchColumn();

// Get transactions with filter
$filter_date  = $_GET['filter_date'] ?? '';
$filter_month = $_GET['filter_month'] ?? '';

if($filter_date){
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE transaction_date = ? ORDER BY created_at DESC");
    $stmt->execute([$filter_date]);
} elseif($filter_month){
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE DATE_FORMAT(transaction_date, '%Y-%m') = ? ORDER BY created_at DESC");
    $stmt->execute([$filter_month]);
} else {
    $stmt = $pdo->query("SELECT * FROM transactions ORDER BY created_at DESC LIMIT 20");
}

$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ledger | JRS OfficeTrack</title>
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
                <a href="ledger.php" class="block px-4 py-2 rounded-lg bg-indigo-50 text-indigo-600 font-semibold">Ledger</a>
                <a href="workers.php" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">Workers</a>
                <a href="reports.php" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">Reports</a>
            </nav>
        </aside>

        <main class="flex-1 p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Account Ledger</h2>

            <?php if($success): ?>
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6">&#10003; <?= $success ?></div>
            <?php endif; ?>
            <?php if($error): ?>
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-6">&#10007; <?= $error ?></div>
            <?php endif; ?>

            <!-- Balance Card + Danger Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

                <div class="bg-indigo-600 rounded-2xl shadow p-8 text-white">
                    <p class="text-indigo-200 text-sm mb-1">Main Account Balance</p>
                    <p class="text-5xl font-bold">&#2547;<?= number_format($balance, 2) ?></p>
                    <p class="text-indigo-200 text-xs mt-4">Updates automatically with every job and manual entry</p>

                    <!-- Danger Buttons -->
                    <div class="flex gap-3 mt-6 flex-wrap">
                        <a href="?reset_balance=1"
                            onclick="return confirm('Reset main balance to 0? This cannot be undone!')"
                            class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 text-xs font-semibold px-4 py-2 rounded-lg transition">
                            Reset Balance to 0
                        </a>
                        <a href="?clear_history=1"
                            onclick="return confirm('Clear ALL transaction history? This cannot be undone!')"
                            class="bg-red-500 hover:bg-red-600 text-white text-xs font-semibold px-4 py-2 rounded-lg transition">
                            Clear All History
                        </a>
                    </div>
                </div>

                <!-- Add Transaction Form -->
                <div class="bg-white rounded-2xl shadow p-6">
                    <h3 class="text-lg font-bold text-gray-700 mb-4">Add Manual Transaction</h3>
                    <form method="POST">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select name="type" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="credit">&#43; Add Money (Credit)</option>
                                <option value="debit">&#45; Deduct Money (Debit)</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount (&#2547;)</label>
                            <input type="number" name="amount" required step="0.01" min="1"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="0.00">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input type="text" name="description" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="e.g. Investment, Expense, Withdrawal">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input type="date" name="transaction_date" required
                                value="<?= date('Y-m-d') ?>"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-lg transition duration-200">
                            Add Transaction
                        </button>
                    </form>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-2xl shadow p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-700 mb-4">Filter Transactions</h3>
                <form method="GET" class="flex gap-4 flex-wrap items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">By Date</label>
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
                        Filter
                    </button>
                    <a href="ledger.php"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold px-6 py-2 rounded-lg transition duration-200">
                        Clear
                    </a>
                </form>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="text-lg font-bold text-gray-700 mb-4">Transaction History</h3>
                <?php if(count($transactions) > 0): ?>
                <table class="w-full text-sm text-left">
                    <thead class="bg-indigo-50 text-indigo-600">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transactions as $t): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3"><?= $t['transaction_date'] ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($t['description']) ?></td>
                            <td class="px-4 py-3">
                                <?php if($t['type'] === 'credit'): ?>
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">+ Credit</span>
                                <?php else: ?>
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-semibold">- Debit</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 font-semibold <?= $t['type'] === 'credit' ? 'text-green-600' : 'text-red-600' ?>">
                                <?= $t['type'] === 'credit' ? '+' : '-' ?>&#2547;<?= number_format($t['amount'], 2) ?>
                            </td>
                            <td class="px-4 py-3">
                                <a href="?delete_txn=<?= $t['id'] ?>"
                                    onclick="return confirm('Delete this transaction? Balance will be reversed!')"
                                    class="bg-red-100 text-red-600 hover:bg-red-200 px-3 py-1 rounded-lg text-xs font-semibold">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-gray-400 text-sm">No transactions found.</p>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <footer class="text-center text-xs text-gray-400 py-4">
        Built by <a href="about.php" target="_blank" class="text-indigo-500 hover:text-indigo-700 font-semibold">JRSphere&#8482;</a>
    </footer>

</body>
</html>