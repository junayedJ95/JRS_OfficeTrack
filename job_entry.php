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

// Fetch workers
$workers = $pdo->query("SELECT * FROM workers ORDER BY worker_name ASC")->fetchAll();

// Handle DELETE job
if(isset($_GET['delete_job'])){
    $job_id = intval($_GET['delete_job']);
    try {
        // Get job first to reverse account balance
        $job = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
        $job->execute([$job_id]);
        $job_data = $job->fetch();

        if($job_data){
            // Reverse balance
            $pdo->prepare("UPDATE account SET balance = balance - ? WHERE id = 1")
                ->execute([$job_data['business_profit']]);
            // Reverse worker stats
            $pdo->prepare("UPDATE workers SET total_jobs = total_jobs - 1, total_paid = total_paid - ? WHERE id = ?")
                ->execute([$job_data['worker_pay'], $job_data['worker_id']]);
            // Delete job
            $pdo->prepare("DELETE FROM jobs WHERE id = ?")->execute([$job_id]);
            $success = "Job deleted successfully!";
        }
    } catch(Exception $e){
        $error = "Failed to delete job!";
    }
}

// Handle ADD job
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_job'])){
    $service_type  = trim($_POST['service_type']);
    $client_name   = trim($_POST['client_name']);
    $worker_id     = $_POST['worker_id'];
    $total_payment = trim($_POST['total_payment']);
    $commission    = trim($_POST['commission_percent']);
    $job_date      = $_POST['job_date'];

    // Validation
    if(empty($service_type) || empty($client_name) || empty($worker_id) || empty($job_date)){
        $error = "All fields are required!";
    } elseif(!preg_match("/^[a-zA-Z\s]+$/", $client_name)){
        $error = "Client name must contain letters only — no numbers or symbols!";
    } elseif(!is_numeric($total_payment) || floatval($total_payment) <= 0){
        $error = "Total payment must be a valid number greater than 0!";
    } elseif(!is_numeric($commission) || floatval($commission) < 0 || floatval($commission) > 100){
        $error = "Commission must be a number between 0 and 100!";
    } else {
        try {
            $total_payment   = floatval($total_payment);
            $commission      = floatval($commission);
            $worker_pay      = ($total_payment * $commission) / 100;
            $business_profit = $total_payment - $worker_pay;

            $stmt = $pdo->prepare("INSERT INTO jobs 
                (service_type, client_name, worker_id, total_payment, commission_percent, worker_pay, business_profit, job_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$service_type, $client_name, $worker_id, $total_payment, $commission, $worker_pay, $business_profit, $job_date]);

            $pdo->prepare("UPDATE account SET balance = balance + ? WHERE id = 1")
                ->execute([$business_profit]);

            $pdo->prepare("INSERT INTO transactions (type, amount, description, transaction_date) VALUES ('credit', ?, ?, ?)")
                ->execute([$business_profit, "Job: $service_type for $client_name", $job_date]);

            $pdo->prepare("UPDATE workers SET total_jobs = total_jobs + 1, total_paid = total_paid + ? WHERE id = ?")
                ->execute([$worker_pay, $worker_id]);

            $success = "Job added successfully!";
            $workers = $pdo->query("SELECT * FROM workers ORDER BY worker_name ASC")->fetchAll();

        } catch(Exception $e){
            $error = "Something went wrong. Please try again!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Entry | JRS OfficeTrack</title>
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
                <a href="job_entry.php" class="block px-4 py-2 rounded-lg bg-indigo-50 text-indigo-600 font-semibold">Job Entry</a>
                <a href="ledger.php" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">Ledger</a>
                <a href="workers.php" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">Workers</a>
                <a href="reports.php" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">Reports</a>
            </nav>
        </aside>

        <main class="flex-1 p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">New Job Entry</h2>

            <?php if($success): ?>
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6">&#10003; <?= $success ?></div>
            <?php endif; ?>

            <?php if($error): ?>
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-6">&#10007; <?= $error ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- Job Form -->
                <div class="bg-white rounded-2xl shadow p-6">
                    <h3 class="text-lg font-bold text-gray-700 mb-4">Job Details</h3>
                    <form method="POST">

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Service Type</label>
                            <select name="service_type" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Select service...</option>
                                <option value="AC Service">AC Service</option>
                                <option value="Gas Service">Gas Service</option>
                                <option value="Cleaning">Cleaning</option>
                                <option value="Plumbing">Plumbing</option>
                                <option value="Electrical">Electrical</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Client Name <span class="text-red-400 text-xs">(letters only)</span></label>
                            <input type="text" name="client_name" required
                                pattern="[a-zA-Z\s]+"
                                title="Client name must contain letters only"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Enter client name">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Worker</label>
                            <select name="worker_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Select worker...</option>
                                <?php foreach($workers as $w): ?>
                                <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['worker_name']) ?> (<?= $w['service_type'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Payment (&#2547;)</label>
                            <input type="number" name="total_payment" id="total_payment" required step="0.01" min="1"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="0.00" oninput="calculate()">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Worker Commission (%)</label>
                            <input type="number" name="commission_percent" id="commission" required step="0.01" min="0" max="100"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="30" oninput="calculate()">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Job Date</label>
                            <input type="date" name="job_date" required
                                value="<?= date('Y-m-d') ?>"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <button type="submit" name="save_job"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-lg transition duration-200">
                            Save Job
                        </button>
                    </form>
                </div>

                <!-- Calculation Preview -->
                <div class="bg-white rounded-2xl shadow p-6 h-fit">
                    <h3 class="text-lg font-bold text-gray-700 mb-4">Auto Calculation</h3>
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4 flex justify-between items-center">
                            <span class="text-gray-600">Total Payment</span>
                            <span class="font-bold text-gray-800">&#2547;<span id="show_total">0.00</span></span>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 flex justify-between items-center">
                            <span class="text-gray-600">Worker Gets</span>
                            <span class="font-bold text-red-600">&#2547;<span id="show_worker">0.00</span></span>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 flex justify-between items-center">
                            <span class="text-gray-600">Business Keeps</span>
                            <span class="font-bold text-green-600">&#2547;<span id="show_business">0.00</span></span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Recent Jobs Table -->
            <div class="bg-white rounded-2xl shadow p-6 mt-8">
                <h3 class="text-lg font-bold text-gray-700 mb-4">Recent Jobs</h3>
                <?php
                $jobs = $pdo->query("SELECT j.*, w.worker_name FROM jobs j 
                    LEFT JOIN workers w ON j.worker_id = w.id 
                    ORDER BY j.created_at DESC LIMIT 10")->fetchAll();
                ?>
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
                            <th class="px-4 py-3">Action</th>
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
                            <td class="px-4 py-3">&#2547;<?= number_format($job['worker_pay'], 2) ?></td>
                            <td class="px-4 py-3">&#2547;<?= number_format($job['business_profit'], 2) ?></td>
                            <td class="px-4 py-3">
                                <a href="?delete_job=<?= $job['id'] ?>" 
                                    onclick="return confirm('Delete this job? This will reverse the account balance!')"
                                    class="bg-red-100 text-red-600 hover:bg-red-200 px-3 py-1 rounded-lg text-xs font-semibold">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-gray-400 text-sm">No jobs recorded yet.</p>
                <?php endif; ?>
            </div>

            <!-- Add New Worker -->
            <div class="bg-white rounded-2xl shadow p-6 mt-8">
                <h3 class="text-lg font-bold text-gray-700 mb-4">Add New Worker</h3>
                <form method="POST" action="add_worker.php" class="flex gap-4 flex-wrap">
                    <input type="text" name="worker_name" required
                        pattern="[a-zA-Z\s]+"
                        title="Worker name must contain letters only"
                        placeholder="Worker name (letters only)"
                        class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <select name="service_type" required
                        class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select service type...</option>
                        <option value="AC Service">AC Service</option>
                        <option value="Gas Service">Gas Service</option>
                        <option value="Cleaning">Cleaning</option>
                        <option value="Plumbing">Plumbing</option>
                        <option value="Electrical">Electrical</option>
                        <option value="Other">Other</option>
                    </select>
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2 rounded-lg transition duration-200">
                        Add Worker
                    </button>
                </form>
            </div>

        </main>
    </div>

    <footer class="text-center text-xs text-gray-400 py-4">Built by JRSphere</footer>

    <script>
        function calculate() {
            const total = parseFloat(document.getElementById('total_payment').value) || 0;
            const commission = parseFloat(document.getElementById('commission').value) || 0;
            const workerPay = (total * commission) / 100;
            const businessKeeps = total - workerPay;
            document.getElementById('show_total').textContent = total.toFixed(2);
            document.getElementById('show_worker').textContent = workerPay.toFixed(2);
            document.getElementById('show_business').textContent = businessKeeps.toFixed(2);
        }
    </script>

</body>
</html>