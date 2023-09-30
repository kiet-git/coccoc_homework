
<!DOCTYPE html>
<html>
<head>
    <title>Student Data</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-image: url('https://i.pinimg.com/564x/19/9f/d2/199fd29184c6cff24e3445f849af463e.jpg'); 
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.3); 
            backdrop-filter: blur(2px);
            z-index: -1;
        }

        table.table-hover tbody tr:hover, th {
            background-color: rgba(0, 0, 0, 0.7); 
            color: white; 
        }
    </style>
</head>
<body>
    <?php
    // Report error
    ini_set ('display_errors', 1);
    ini_set ('display_startup_errors', 1);
    error_reporting (E_ALL);

    // Database connection details
    $dsn = "mysql:host=localhost;dbname=student_db";
    $user = "user1";
    $passwd = "user1";

    // Create a PDO instance
    try {
        $pdo = new PDO($dsn, $user, $passwd);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Database connection failed: " . $e->getMessage();
        die();
    }

    // Drop and create the 'students' table
    $pdo->exec("DROP TABLE IF EXISTS students");

    $sql = "CREATE TABLE students (
        id INT PRIMARY KEY AUTO_INCREMENT, 
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        birth DATE NOT NULL,
        gender CHAR(1) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);

    // Import data from CSV
    if (($handle = fopen("students.csv", "r")) !== FALSE) {
        fgetcsv($handle, 1000, ",");

        $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, birth, gender) VALUES (:first_name, :last_name, :birth, :gender)");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (empty($data[0])) {
                break;
            }

            $stmt->bindParam(":first_name", $data[1]);
            $stmt->bindParam(":last_name", $data[2]);
            $birth = DateTime::createFromFormat('m/Y', $data[3])->format('Y-m-01');
            $stmt->bindParam(":birth", $birth);
            $stmt->bindParam(":gender", $data[5]);

            $stmt->execute();
        }

        fclose($handle);
    } else {
        echo "Failed to open the CSV file.";
    }
    ?>
    <div class="overlay"></div>

    <div class="container">
        <h1 class="text-center mt-4 text-white">Student Data</h1>
        <table class="table table-bordered table-striped table-hover">
            <thead class="text-white">
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Birth</th>
                    <th>Gender</th>
                </tr>
            </thead>
            <tbody class="text-white">
                <?php
                // Fetch and display data from the 'students' table
                $stmt = $pdo->query("SELECT id, first_name, last_name, birth, gender FROM students");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rows as $row) {
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['first_name']}</td>";
                    echo "<td>{$row['last_name']}</td>";
                    echo "<td>" . date('m/Y', strtotime($row['birth'])) . "</td>";
                    echo "<td>{$row['gender']}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <div id="chart-container" class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h1 class="text-center">Gender Chart</h1>
                        <div class="chart-container">
                            <canvas id="genderChart" style="max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- Include Chart.js library for charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        <?php
        $stmt = $pdo->query("SELECT COUNT(*) AS count, gender FROM students GROUP BY gender");
        $genderData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $genderLabels = [];
        $genderCounts = [];
        foreach ($genderData as $row) {
            $genderLabels[] = $row['gender'];
            $genderCounts[] = $row['count'];
        }
        ?>

        // Create a pie chart to show gender distribution
        var ctx = document.getElementById('genderChart').getContext('2d');
        var genderChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($genderLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($genderCounts); ?>,
                    backgroundColor: ['pink', 'blue'],
                }],
            }
        });
    </script>
</body>
</html>
