<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Node Proxmox</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        h5 {
            font-weight: bold;
        }
        .chart-container {
            position: relative;
            margin: auto;
            height: 40vh;
            width: 100%;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Monitoring Node Proxmox</h1>

    <div class="row">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-memory"></i> Memory Usage</h5>
                    <div class="chart-container">
                        <canvas id="memoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-cpu"></i> CPU Usage</h5>
                    <div class="chart-container">
                        <canvas id="cpuChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-hdd"></i> Disk Usage</h5>
                    <div class="chart-container">
                        <canvas id="diskChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Server Specifications</h5>
                    <ul class="list-group" id="serverSpecs">
                        <!-- Info akan diisi melalui AJAX -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js"></script>

<script>
    var memoryCtx = document.getElementById('memoryChart').getContext('2d');
    var cpuCtx = document.getElementById('cpuChart').getContext('2d');
    var diskCtx = document.getElementById('diskChart').getContext('2d');

    var memoryChart = new Chart(memoryCtx, {
        type: 'doughnut',
        data: {
            labels: ['Used Memory', 'Free Memory'],
            datasets: [{
                label: 'Memory Usage',
                data: [0, 0],
                backgroundColor: ['#FF6384', '#36A2EB'],
                hoverBackgroundColor: ['#FF6384', '#36A2EB'],
                borderWidth: 1,
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    var cpuChart = new Chart(cpuCtx, {
        type: 'doughnut',
        data: {
            labels: ['Used CPU', 'Idle CPU'],
            datasets: [{
                label: 'CPU Usage',
                data: [0, 0],
                backgroundColor: ['#FFCE56', '#4BC0C0'],
                hoverBackgroundColor: ['#FFCE56', '#4BC0C0'],
                borderWidth: 1,
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    var diskChart = new Chart(diskCtx, {
        type: 'doughnut',
        data: {
            labels: ['Used Disk', 'Free Disk'],
            datasets: [{
                label: 'Disk Usage',
                data: [0, 0],
                backgroundColor: ['#FF6384', '#36A2EB'],
                hoverBackgroundColor: ['#FF6384', '#36A2EB'],
                borderWidth: 1,
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

function updateStatus() {
    fetch('/monitoring/status/pve24') // Ganti 'pve24' dengan nama node Anda
        .then(response => response.json())
        .then(data => {
            console.log(data); // Menampilkan data di konsol untuk debugging

            // Cek apakah data yang diterima ada
            if (!data.data || !data.data.data) {
                console.error('Data tidak ditemukan.');
                return;
            }

            const nodeData = data.data.data;

            // Update Memory Chart
            if (nodeData.memory) {
                const totalMemory = nodeData.memory.total / (1024 * 1024); // Convert to MB
                const usedMemory = nodeData.memory.used / (1024 * 1024); // Convert to MB
                memoryChart.data.datasets[0].data = [usedMemory, totalMemory - usedMemory];
                memoryChart.update();
            } else {
                console.error('Data memori tidak ditemukan.');
            }

            // Update CPU Chart
            if (nodeData.cpu) {
                const usedCpu = nodeData.cpu * 100; // Convert to percentage
                cpuChart.data.datasets[0].data = [usedCpu, 100 - usedCpu];
                cpuChart.update();
            } else {
                console.error('Data CPU tidak ditemukan.');
            }

            // Update Disk Chart
            if (nodeData.rootfs) {
                const usedDisk = nodeData.rootfs.used / (1024 * 1024); // Convert to MB
                const freeDisk = nodeData.rootfs.free / (1024 * 1024); // Convert to MB
                diskChart.data.datasets[0].data = [usedDisk, freeDisk];
                diskChart.update();
            } else {
                console.error('Data disk tidak ditemukan.');
            }

            // Update Server Specifications
            if (nodeData.cpuinfo) {
                document.getElementById('serverSpecs').innerHTML = `
                    <li class="list-group-item"><strong>Total Memory:</strong> ${Math.round(nodeData.memory.total / (1024 * 1024))} MB</li>
                    <li class="list-group-item"><strong>Used Memory:</strong> ${Math.round(nodeData.memory.used / (1024 * 1024))} MB</li>
                    <li class="list-group-item"><strong>Free Memory:</strong> ${Math.round(nodeData.memory.free / (1024 * 1024))} MB</li>
                    <li class="list-group-item"><strong>CPU Model:</strong> ${nodeData.cpuinfo.model}</li>
                    <li class="list-group-item"><strong>Load Average:</strong> ${nodeData.loadavg.join(', ')}</li>
                    <li class="list-group-item"><strong>Uptime:</strong> ${nodeData.uptime} seconds</li>
                `;
            } else {
                console.error('Data informasi CPU tidak ditemukan.');
            }
        })
        .catch(error => console.error('Error:', error));
}



    // Update status setiap 1 detik
    setInterval(updateStatus, 1000);
    updateStatus(); // Panggil fungsi pertama kali untuk mendapatkan data segera
</script>
</body>
</html>
