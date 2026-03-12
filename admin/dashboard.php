<?php
session_start();
require_once __DIR__ . '/../includes/config/constants.php';
require_once __DIR__ . '/../includes/classes/Database.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/admin_functions.php';

// Get dashboard data
$stats = getAdminStats();
$recentActivities = getRecentActivities(10);
$chartData = getBookingChartData(30);

$page_title = 'Dashboard - ' . APP_NAME;
include INCLUDES_PATH . '/templates/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportDashboard()">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Total Bookings</h6>
                        <h2 class="card-number mb-0"><?php echo $stats['total_bookings']; ?></h2>
                        <small class="text-white-50">All time</small>
                    </div>
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Today's Bookings</h6>
                        <h2 class="card-number mb-0"><?php echo $stats['today_bookings']; ?></h2>
                        <small class="text-white-50"><?php echo date('F j, Y'); ?></small>
                    </div>
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Pending</h6>
                        <h2 class="card-number mb-0"><?php echo $stats['pending_bookings']; ?></h2>
                        <small class="text-white-50">Awaiting confirmation</small>
                    </div>
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Revenue</h6>
                        <h2 class="card-number mb-0">$<?php echo number_format($stats['estimated_revenue']); ?></h2>
                        <small class="text-white-50">Estimated</small>
                    </div>
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Booking Trends (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="bookingsChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Activities</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="timeline-item">
                            <div class="timeline-badge <?php echo $activity['type'] == 'booking' ? 'bg-primary' : 'bg-success'; ?>">
                                <i class="fas fa-<?php echo $activity['type'] == 'booking' ? 'calendar-check' : 'user'; ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>
                                    <?php if ($activity['type'] == 'booking'): ?>
                                        New booking from <?php echo htmlspecialchars($activity['title']); ?>
                                    <?php else: ?>
                                        Admin <?php echo htmlspecialchars($activity['title']); ?> added
                                    <?php endif; ?>
                                </h6>
                                <small class="text-muted">
                                    <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="bookings.php" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-list"></i> View All Bookings
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="add_admin.php" class="btn btn-outline-success w-100 mb-2">
                            <i class="fas fa-user-plus"></i> Add Admin
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="send_email.php" class="btn btn-outline-info w-100 mb-2">
                            <i class="fas fa-envelope"></i> Send Email
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="settings.php" class="btn btn-outline-secondary w-100 mb-2">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Booking Trends Chart
var ctx = document.getElementById('bookingsChart').getContext('2d');
var bookingsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chartData['labels']); ?>,
        datasets: [{
            label: 'Bookings',
            data: <?php echo json_encode($chartData['values']); ?>,
            backgroundColor: 'rgba(102, 126, 234, 0.2)',
            borderColor: 'rgba(102, 126, 234, 1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Status Distribution Chart
var statusCtx = document.getElementById('statusChart').getContext('2d');
var statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Confirmed', 'Pending', 'Cancelled', 'Completed'],
        datasets: [{
            data: [
                <?php 
                    $db = DatabaseConnection::getInstance()->getConnection();
                    $stmt = $db->query("SELECT status, COUNT(*) as count FROM bookings GROUP BY status");
                    $statusData = ['confirmed' => 0, 'pending' => 0, 'cancelled' => 0, 'completed' => 0];
                    while ($row = $stmt->fetch()) {
                        $statusData[$row['status']] = $row['count'];
                    }
                    echo $statusData['confirmed'] . ',';
                    echo $statusData['pending'] . ',';
                    echo $statusData['cancelled'] . ',';
                    echo $statusData['completed'];
                ?>
            ],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(23, 162, 184, 0.8)'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

function exportDashboard() {
    window.location.href = 'export_dashboard.php';
}
</script>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 50px;
    margin-bottom: 20px;
}

.timeline-badge {
    position: absolute;
    left: 0;
    top: 0;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.timeline-content {
    padding: 10px 15px;
    background: #f8f9fa;
    border-radius: 5px;
}

.timeline-content h6 {
    margin-bottom: 5px;
    color: #333;
}

.timeline-content small {
    color: #6c757d;
}
</style>

<?php include INCLUDES_PATH . '/templates/footer.php'; ?>