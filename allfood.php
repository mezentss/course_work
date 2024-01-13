<?php
require("session.php");
require("db_connect.php");
require("facts.php");

if (isset($_GET["user_id"])) {
    $_SESSION["user"] = ["id" => $_GET["user_id"]];
    $session_user = $_SESSION["user"];
}

// Создание временной таблицы с вычисленной насыщенностью
mysqli_query($conn, "CREATE TEMPORARY TABLE IF NOT EXISTS temp_satiety_counts AS
                        SELECT 
                            CASE 
                                WHEN f.satiety_index <= 0.4 THEN 'Трудно насытиться' 
                                WHEN f.satiety_index > 0.4 AND f.satiety_index < 0.7 THEN 'Достаточно сытно' 
                                ELSE 'Очень сытно' 
                            END AS satiety_category,
                            COUNT(f.id) as product_count 
                        FROM food f 
                        GROUP BY satiety_category");

// Получение данных из временной таблицы
$result = mysqli_query($conn, "SELECT satiety_category, product_count FROM temp_satiety_counts");

$chartDataSatiety = array();

$chartDataSatiety = array();

while ($row = mysqli_fetch_assoc($result)) {
    $chartDataSatiety[$row['satiety_category']] = $row['product_count'];
}

$labelsSatiety = ["Трудно насытиться", "Достаточно сытно", "Очень сытно"];
$dataSatiety = array_values($chartDataSatiety);
$chartHtmlSatiety = "<canvas id='myPieChartSatiety' style='max-width: 300px; margin: auto;'></canvas>";

$result = mysqli_query($conn, "SELECT * FROM categories");
$title = "";
$content = "<div style='display: flex;'><div style='flex: 1;'>";
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
                        $chartHtmlSatiety
                    </div>
                </div>
            </div>
        </div>";


require("template.php");
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    var ctxSatiety = document.getElementById('myPieChartSatiety').getContext('2d');
    var myPieChartSatiety = new Chart(ctxSatiety, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($labelsSatiety); ?>,
            datasets: [{
                data: <?php echo json_encode($dataSatiety); ?>,
                backgroundColor: [
                    '#DAFA82',
                    '#F45870',
                    '#3B931A'
                ]
            }]
        },
    });
</script>