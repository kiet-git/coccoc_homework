<!DOCTYPE html>
<html>
<head>
    <title>Student Data</title>
    <style>
        table{
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        #genderChart {
            width: 30%;
            margin: auto;
        } 
    </style>
<body>
    <h1>Student Data</h1>

    <table>
        <?php
        $maleCount = 0;
        $femaleCount = 0;

        $row = 1;
        if (($handle = fopen("students.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if($data[0] == '') {
                    break;
                }
                    
                echo "<tr>";

                for ($c = 1; $c < 6; $c++) {
                    if ($c != 4) {
                        if ($row == 1) {
                            echo "<th>" . str_replace('_', ' ', ucwords($data[$c])) . "</th>";
                        } else {
                            echo "<td>" . $data[$c] . "</td>";
                        }
                    } 
                    if($c == 5) {
                        if($data[$c] == 'M') {
                            $maleCount++;
                        } else {
                            $femaleCount++;
                        }
                    }
                }
                
                echo "</tr>";
                $row++;
            }
            fclose($handle);
        } else {
            echo "Failed to open the CSV file.";
        }
        ?>
    </table>

    <div id="chart-container">
        <h1>Gender Chart</h1>
        <canvas id="genderChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        var ctx = document.getElementById('genderChart');
        var genderChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [<?php echo $maleCount; ?>, <?php echo $femaleCount; ?>],
                    backgroundColor: ['#36A2EB', '#FF6384'],
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
            }
        });
    </script>
</body>
</html>