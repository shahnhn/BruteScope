<?php
    include("./config.php");

    $success_count=0;
    $fail_count=0;
    $sql="SELECT status, COUNT(*) AS count FROM login_attempts GROUP BY status";
    $result=$conn->query($sql);
    if($result){
        while($row=$result->fetch_assoc()){
            if($row['status']=='success'){
                $success_count=(int)$row['count'];
            }else{
                $fail_count+=(int)$row['count'];
            }
        } 
    } else {
        die("Database error: ".$conn->error);
    }

    $top_usernames=[];
    $sql="SELECT username, COUNT(*) AS count FROM login_attempts WHERE username != '' GROUP BY username ORDER BY count DESC LIMIT 5";
    $result=$conn->query($sql);
    if($result){
        while($row=$result->fetch_assoc()){
            $top_usernames[]=['username' => $row['username'], 'count' => (int)$row['count']];
        }
    } else {
        die("Database error: ".$conn->error);
    }

    $attempts_over_time=[];
    $sql="SELECT DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour_slot, COUNT(*) as count
    FROM login_attempts
    WHERE created_at >= NOW() - INTERVAL 24 HOUR
    GROUP BY hour_slot
    ORDER BY hour_slot ASC";
    $result=$conn->query($sql);
    if($result){
        while($row=$result->fetch_assoc()){
            $attempts_over_time[]=[
                'time'=>$row['hour_slot'],
                'count'=>(int)$row['count']
            ];
        }
    }
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BruteScope - Dashboard</title>
  <!-- Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/dashboard.css">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="min-h-screen flex flex-col items-center">
  <div class="w-full max-w-7xl px-4 py-6">
    <h1 class="text-2xl md:text-3xl mb-2 text-center">🛡 BruteScope Dashboard</h1>
    <p class="text-center mb-6 text-gray-400">Visual analytics of login attempts</p>
    <div class="flex justify-center items-center mb-4 space-x-4 text-gray-400 text-sm">
      <span id="lastUpdated">Last updated: --</span>
      <span id="loading" class="hidden animate-pulse text-green-500">Loading…</span>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <div class="custom-card">
        <div class="custom-header px-4 py-2">Attempts Over Time</div>
        <div class="p-4">
          <canvas id="attemptsOverTime"></canvas>
        </div>
      </div>

      <div class="custom-card">
        <div class="custom-header px-4 py-2">Success vs Fail</div>
        <div class="p-4">
          <canvas id="statusPieChart"></canvas>
        </div>
      </div>
    </div>

    <div class="custom-card">
      <div class="custom-header px-4 py-2">Top 5 Usernames Attempted</div>
      <div class="p-4">
        <canvas id="topUsernamesChart"></canvas>
      </div>
    </div>
  </div>

  <script>
    // PHP data passed to JS
    const successCount = <?php echo json_encode($success_count); ?>;
    const failCount = <?php echo json_encode($fail_count); ?>;
    const topUsernames = <?php echo json_encode(array_column($top_usernames, 'username')); ?>;
    const topUsernameCounts = <?php echo json_encode(array_column($top_usernames, 'count')); ?>;
    const timeLabels = <?php echo json_encode(array_column($attempts_over_time,'time')); ?>;
    const timeCounts = <?php echo json_encode(array_column($attempts_over_time,'count')); ?>;

  // Keep chart instances globally
  let lineChart, pieChart, barChart;

// Initial chart creation
function createCharts() {
  // Line chart
  const ctxTime = document.getElementById('attemptsOverTime').getContext('2d');
  lineChart = new Chart(ctxTime, {
    type: 'line',
    data: {
      labels: timeLabels,
      datasets: [{
        label: 'Attempts',
        data: timeCounts,
        borderColor: '#00ff00',
        backgroundColor: 'rgba(0,255,0,0.1)',
        tension: 0.3,
        fill: true,
        pointRadius: 3
      }]
    },
    options: {
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { color: '#ccc' }, grid: { color: '#333' }},
        y: { beginAtZero: true, ticks: { color: '#ccc' }, grid: { color: '#333' }}
      }
    }
  });
  // Pie chart
  const ctxPie = document.getElementById('statusPieChart').getContext('2d');
  pieChart = new Chart(ctxPie, {
    type: 'pie',
    data: {
      labels: ['Success', 'Fail'],
      datasets: [{
        data: [successCount, failCount],
        backgroundColor: ['#00ff00', '#ff4c4c']
      }]
    },
    options: {
      plugins: {
        legend: { labels: { color: '#ccc', font: { size: 14 } } }
      }
    }
  });
  // Bar chart
  const ctxBar = document.getElementById('topUsernamesChart').getContext('2d');
  barChart = new Chart(ctxBar, {
    type: 'bar',
    data: {
      labels: topUsernames,
      datasets: [{
        label: 'Attempts',
        data: topUsernameCounts,
        backgroundColor: '#00ff00'
      }]
    },
    options: {
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { color: '#ccc' }, grid: { color: '#333' }},
        y: { beginAtZero: true, ticks: { color: '#ccc' }, grid: { color: '#333' }}
      }
    }
  });
}

// Initial call
createCharts();

// Polling function
async function fetchAndUpdate() {
  const loadingEl = document.getElementById('loading');
  const updatedEl = document.getElementById('lastUpdated');
  try {
    loadingEl.classList.remove('hidden');  // show spinner

    const res = await fetch('api/dashboard_data.php');
    const data = await res.json();
    // Update line chart
    lineChart.data.labels = data.attempts_over_time.map(item => item.time);
    lineChart.data.datasets[0].data = data.attempts_over_time.map(item => item.count);
    lineChart.update();
    // Update pie chart
    pieChart.data.datasets[0].data = [data.success_count, data.fail_count];
    pieChart.update();
    // Update bar chart
    barChart.data.labels = data.top_usernames.map(item => item.username);
    barChart.data.datasets[0].data = data.top_usernames.map(item => item.count);
    barChart.update();
    // Update timestamp
    const now = new Date();
    updatedEl.textContent = 'Last updated: ' + now.toLocaleTimeString();
  } catch (error) {
    console.error('Failed to fetch dashboard data:', error);
  } finally {
    loadingEl.classList.add('hidden');  // hide spinner
  }
}

// Poll every 10 seconds
setInterval(fetchAndUpdate, 10000);
  </script>
</body>
</html>