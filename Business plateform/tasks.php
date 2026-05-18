<?php
require_once __DIR__ . '/functions.php';
require_login();
$user = $_SESSION['user'];
$errors = [];
$success = null;
$editTask = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_task') {
        $title = sanitize($_POST['title'] ?? '');
        $notes = sanitize($_POST['notes'] ?? '');
        if (empty($title)) {
            $errors[] = 'Task title is required.';
        } else {
            add_task($title, $notes);
            record_activity('Created task: ' . $title, 'task');
            $success = 'Task created successfully.';
        }
    }

    if ($action === 'update_task') {
        $taskId = (int) ($_POST['task_id'] ?? 0);
        $title = sanitize($_POST['title'] ?? '');
        $notes = sanitize($_POST['notes'] ?? '');
        $status = sanitize($_POST['status'] ?? 'pending');
        if ($taskId <= 0 || empty($title)) {
            $errors[] = 'Valid task title is required.';
        } else {
            update_task($taskId, $title, $notes, $status);
            record_activity('Updated task: ' . $title, 'task');
            $success = 'Task updated successfully.';
        }
    }

    if ($action === 'delete_task') {
        $taskId = (int) ($_POST['task_id'] ?? 0);
        if ($taskId > 0) {
            $task = get_task_by_id($taskId);
            if ($task) {
                delete_task($taskId);
                record_activity('Deleted task: ' . $task['title'], 'task');
                $success = 'Task deleted successfully.';
            }
        }
    }
}

if (!empty($_GET['edit_id'])) {
    $editTask = get_task_by_id((int) $_GET['edit_id']);
}

$tasks = fetch_task_list();
require_once __DIR__ . '/header.php';
?>
<section class="dashboard-overview">
    <div class="dashboard-hero">
        <h1>Task Management</h1>
        <p>Manage your tasks, update status, and keep your activity feed synchronized.</p>
    </div>
    <?php if ($errors): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?= sanitize($error); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= sanitize($success); ?></div>
    <?php endif; ?>
</section>
<section class="dashboard-grid">
    <article class="panel">
        <h2><?= $editTask ? 'Edit Task' : 'Create Task'; ?></h2>
        <form method="post" class="task-form">
            <?php if ($editTask): ?>
                <input type="hidden" name="action" value="update_task">
                <input type="hidden" name="task_id" value="<?= sanitize($editTask['id']); ?>">
            <?php else: ?>
                <input type="hidden" name="action" value="create_task">
            <?php endif; ?>
            <label>Title<input type="text" name="title" value="<?= sanitize($editTask['title'] ?? ''); ?>" required></label>
            <label>Notes<textarea name="notes" rows="4"><?= sanitize($editTask['notes'] ?? ''); ?></textarea></label>
            <?php if ($editTask): ?>
                <label>Status<select name="status">
                    <option value="pending" <?= $editTask['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in progress" <?= $editTask['status'] === 'in progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?= $editTask['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select></label>
            <?php endif; ?>
            <button class="button button-primary" type="submit"><?= $editTask ? 'Save changes' : 'Add task'; ?></button>
            <?php if ($editTask): ?>
                <a class="button button-secondary" href="tasks.php">Cancel</a>
            <?php endif; ?>
        </form>
    </article>
    <article class="panel">
        <h2>All Tasks</h2>
        <ul class="task-list">
            <?php foreach ($tasks as $task): ?>
                <li class="task-item">
                    <div>
                        <h3><?= sanitize($task['title']); ?></h3>
                        <p><?= sanitize($task['notes']); ?></p>
                        <span class="status-badge status-<?= sanitize(str_replace(' ', '-', $task['status'])); ?>"><?= sanitize(ucfirst($task['status'])); ?></span>
                    </div>
                    <div class="task-status-form">
                        <a href="tasks.php?edit_id=<?= sanitize($task['id']); ?>" class="button button-secondary">Edit</a>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="delete_task">
                            <input type="hidden" name="task_id" value="<?= sanitize($task['id']); ?>">
                            <button class="button button-danger" type="submit" onclick="return confirm('Delete this task?');">Delete</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </article>
</section>
<?php require_once __DIR__ . '/footer.php'; ?>