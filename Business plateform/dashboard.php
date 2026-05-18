<?php
require_once __DIR__ . '/functions.php';
require_login();
$user = $_SESSION['user'];
$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_task') {
        $title = sanitize($_POST['task_title'] ?? '');
        $notes = sanitize($_POST['task_notes'] ?? '');
        if (empty($title)) {
            $errors[] = 'Task title is required.';
        } else {
            add_task($title, $notes);
            record_activity('Created task: ' . $title, 'task');
            $success = 'Task added successfully.';
        }
    }

    if ($action === 'update_status') {
        $taskId = (int) ($_POST['task_id'] ?? 0);
        $status = sanitize($_POST['task_status'] ?? 'pending');
        if ($taskId > 0) {
            update_task_status($taskId, $status);
            record_activity('Updated task status to ' . $status, 'task');
            $success = 'Task status updated.';
        }
    }
}

$metrics = fetch_dashboard_metrics();
$activities = fetch_recent_activity();
$tasks = fetch_task_list();

require_once __DIR__ . '/header.php';
?>
<section class="dashboard-overview">
    <div class="dashboard-hero">
        <h1>Welcome back, <?= sanitize($user['name']); ?>.</h1>
        <p>Manage your business operations, review key metrics, and get AI-powered business recommendations.</p>
    </div>
    <?php if ($errors): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?= sanitize($error); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= sanitize($success); ?></div>
    <?php endif; ?>
    <div class="metrics-grid">
        <div class="metric-card">
            <span>Total Revenue</span>
            <strong>$<?= number_format($metrics['total_revenue'], 2); ?></strong>
        </div>
        <div class="metric-card">
            <span>Total Orders</span>
            <strong><?= sanitize($metrics['total_orders']); ?></strong>
        </div>
        <div class="metric-card">
            <span>Total Customers</span>
            <strong><?= sanitize($metrics['total_customers']); ?></strong>
        </div>
        <div class="metric-card">
            <span>Growth</span>
            <strong><?= sanitize($metrics['growth_rate']); ?>%</strong>
        </div>
    </div>
    <div class="chart-grid">
        <div class="panel">
            <h2>Order Status Breakdown</h2>
            <?php
                $statusData = $pdo->query('SELECT status, COUNT(*) AS total FROM orders GROUP BY status')->fetchAll();
            ?>
            <canvas id="orderStatusChart" data-chart='<?= htmlspecialchars(json_encode($statusData), ENT_QUOTES); ?>'></canvas>
        </div>
        <div class="panel">
            <h2>Revenue Trend</h2>
            <?php
                $revenueData = $pdo->query('SELECT DATE_FORMAT(created_at, "%Y-%m-%d") AS day, SUM(amount) AS total FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day')->fetchAll();
            ?>
            <canvas id="revenueTrendChart" data-chart='<?= htmlspecialchars(json_encode($revenueData), ENT_QUOTES); ?>'></canvas>
        </div>
    </div>
</section>
<section class="dashboard-grid">
    <article class="panel">
        <h2>Quick Actions</h2>
        <p>Use quick buttons to add tasks or ask for AI recommendations.</p>
        <div class="dashboard-actions">
            <a href="tasks.php" class="button button-secondary">Manage tasks</a>
            <a href="#taskForm" class="button button-secondary">Add new task</a>
            <a href="#activityFeed" class="button button-secondary">View activity</a>
            <a href="#aiPanel" class="button button-primary">Ask AI</a>
        </div>
    </article>
    <article class="panel">
        <h2>Business Snapshot</h2>
        <ul class="snapshot-list">
            <li><strong><?= sanitize($metrics['active_tasks']); ?></strong> active tasks</li>
            <li><strong><?= sanitize($metrics['total_orders']); ?></strong> orders processed</li>
            <li><strong><?= sanitize($metrics['total_customers']); ?></strong> active customers</li>
            <li><strong><?= sanitize($metrics['growth_rate']); ?>%</strong> revenue growth</li>
        </ul>
    </article>
</section>
<section class="dashboard-grid">
    <article class="panel" id="taskForm">
        <h2>Task Management</h2>
        <p>Create smart, actionable tasks for your team and keep priorities visible.</p>
        <form method="post" class="task-form">
            <input type="hidden" name="action" value="add_task">
            <label>Task title<input type="text" name="task_title" placeholder="Example: Follow up with top customer" required></label>
            <label>Notes<textarea name="task_notes" rows="3" placeholder="Add details or next steps..."></textarea></label>
            <button class="button button-primary" type="submit">Create Task</button>
        </form>
    </article>
    <article class="panel">
        <h2>Recent Tasks</h2>
        <ul class="task-list">
            <?php foreach ($tasks as $task): ?>
                <li class="task-item">
                    <div>
                        <h3><?= sanitize($task['title']); ?></h3>
                        <p><?= sanitize($task['notes']); ?></p>
                        <span class="status-badge status-<?= sanitize(str_replace(' ', '-', $task['status'])); ?>"><?= sanitize(ucfirst($task['status'])); ?></span>
                    </div>
                    <form method="post" class="task-status-form">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="task_id" value="<?= sanitize($task['id']); ?>">
                        <select name="task_status" aria-label="Update task status">
                            <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="in progress" <?= $task['status'] === 'in progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                        <button class="button button-secondary" type="submit">Save</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </article>
</section>
<section class="dashboard-grid" id="activityFeed">
    <article class="panel">
        <h2>Recent Activity</h2>
        <ul class="feed-list">
            <?php if (empty($activities)): ?>
                <li>No activity yet.</li>
            <?php else: ?>
                <?php foreach ($activities as $activity): ?>
                    <li>
                        <p><?= sanitize($activity['message']); ?></p>
                        <span><?= date('M j, Y H:i', strtotime($activity['created_at'])); ?></span>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </article>
    <article class="panel ai-panel" id="aiPanel">
        <div>
            <h2>AI Business Assistant</h2>
            <p>Analyze metrics, generate recommendations, and automate task ideas in one place.</p>
            <div class="ai-actions">
                <button class="button button-secondary" data-prompt="Generate a revenue forecast for the next quarter.">Forecast</button>
                <button class="button button-secondary" data-prompt="How can I improve profitability and reduce expenses?">Optimize</button>
                <button class="button button-secondary" data-prompt="Create smart tasks for our sales team.">Automate task ideas</button>
            </div>
            <textarea id="aiPrompt" placeholder="Ask the business advisor a question..." rows="3"></textarea>
            <button id="runAi" class="button button-primary">Generate insight</button>
        </div>
        <div class="ai-result" id="aiResult">AI insights will appear here.</div>
    </article>
</section>
<?php require_once __DIR__ . '/footer.php'; ?>