<?php
require("session.php");
require("db_connect.php");
require("facts.php");

if (isset($_GET["user_id"])) {
    $_SESSION["user"] = ["id" => $_GET["user_id"]];
    $session_user = $_SESSION["user"];
}

$result = mysqli_query($conn, "SELECT c.name as category_name, COUNT(f.id) as food_count
                                FROM categories c
                                LEFT JOIN food f ON c.id = f.category
                                GROUP BY c.name");

$chartData = array();
while ($row = mysqli_fetch_assoc($result)) {
    $chartData[] = array("category" => $row["category_name"], "count" => $row["food_count"]);
}

$labels = array();
$data = array();
$backgroundColors = array();

foreach ($chartData as $dataPoint) {
    $backgroundColors = [
        '#3B931A', '#B75F6D', '#9F1C31', '#9CF07D', '#F9A1AF',
        '#CE4AA6', '#78E251', '#861865', '#E778C5', '#E795CE',
        '#CBF458', '#A0B75F', '#7C9F1C', '#DAFA82', '#E2FAA2',
        '#78E251', '#6EA958', '#3B931A', '#9CF07D', '#B3F09C'
    ];  
    $labels[] = $dataPoint["category"];
    $data[] = $dataPoint["count"] ;
}

$chartHtml = "<canvas id='myPieChart' style='position: center; max-width: 300px; '></canvas>";

$result = mysqli_query($conn, "SELECT * FROM categories");
$title = "";
$content =
"<div style='display: flex;'>
<div style='flex: 1;'>";

if(!$result || mysqli_num_rows($result) == 0){
    $content .= "В базе данных нет категорий.";
} else {
    $content .= "<h2>Категории</h2>";
    while($category = mysqli_fetch_assoc($result)){
        $content .= "<h3><a href='category.php?id={$category['id']}'>{$category['name']}</a></h3>";
    }
}

$randomFact = get_random_fact();

$content .= "</div>
            <div style='flex: 1;'>
                <h2>Для вас!</h2>
                <p>$randomFact</p>
                <h2>Количество продуктов в категориях:</h2>
                <div style='display: flex; justify-content: center;'>
                    <div style='max-width: 350px;'>
                        <canvas id='myPieChart'></canvas>
                    </div>
                </div>
            </div>
        </div>";

require("template.php");
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('myPieChart').getContext('2d');
    var myPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                data: <?php echo json_encode($data); ?>,
                backgroundColor: <?php echo json_encode($backgroundColors); ?>
            }]
        },
    });
</script>
