<?php
require("session.php");
require("db_connect.php");
require("category_selector.php");

$user_id = ($session_user)['id'];

$title = '';

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sugar_level_query = "SELECT sugar_level FROM current_sugar WHERE user_id = $user_id";
$sugar_level_result = mysqli_query($conn, $sugar_level_query);
$sugar_level = mysqli_fetch_assoc($sugar_level_result)['sugar_level'];

$selected_cluster_id_query = "SELECT cluster_id FROM food ";

if ($sugar_level < 5.4) {
    $selected_cluster_id_query .= "ORDER BY satiety_index DESC LIMIT 10";
} elseif ($sugar_level > 8.4) {
    $selected_cluster_id_query .= "ORDER BY satiety_index ASC LIMIT 10";
} else {
    $selected_cluster_id_query .= "ORDER BY RAND() LIMIT 10";
}

$selected_cluster_id_result = mysqli_query($conn, $selected_cluster_id_query);
$selected_cluster_id = mysqli_fetch_assoc($selected_cluster_id_result)['cluster_id'];

$selected_food_query = "SELECT * FROM food WHERE cluster_id = $selected_cluster_id LIMIT $limit OFFSET $offset";
$selected_food_result = mysqli_query($conn, $selected_food_query);

$selected_food_data = [];
while ($row = mysqli_fetch_assoc($selected_food_result)) {
    $selected_food_data[] = $row;
}

$content = "<h2>Возможно сейчас вы захотите перекусить следующими продуктами:</h2>";
$content .= "<table id='productTable'><tr><th>Название продукта</th><th>Уровень насыщения</th></tr>";
$favourite_present = false;
foreach ($selected_food_data as $row) {
    $satiety_index = $row['satiety_index'];
    $description = '';
    if ($satiety_index <= 0.4) {
        $description = "Подойдёт для перекуса";
    } elseif ($satiety_index > 0.4 && $satiety_index < 0.7) {
        $description = "Достаточно сытно";
    } else {
        $description = "Очень сытно";
    }
    $content .= "<tr class='productRow' data-category='{$row['category']}'><td>{$row['name']}</td><td>{$description}</td></tr>";
}
$content .= "</table>";

require("template.php");
?>

<div style="display: flex;">
    <div style="margin-left: 250px;">
        <h3>График насыщения</h3>
        <canvas id='sugarSatietyChart' width='600' height='300'></canvas>
    </div>
</div>

<script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js'></script>
<script>
    var sugarData = <?php echo json_encode(array_column($selected_food_data, 'sugar')); ?>;
    var satietyData = <?php echo json_encode(array_column($selected_food_data, 'satiety_index')); ?>;
    var nameData = <?php echo json_encode(array_column($selected_food_data, 'name')); ?>;

    var sugarSatietyCtx = document.getElementById('sugarSatietyChart').getContext('2d');
    var sugarSatietyChart = new Chart(sugarSatietyCtx, {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Продукт',
                data: sugarData.map((value, index) => ({
                    x: value,
                    y: satietyData[index],
                    productName: nameData[index]
                })),
                backgroundColor: '#CBF458'
            }]
        },
        options: {
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        return data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index].productName;
                    }
                }
            },
            scales: {
                xAxes: [{
                    type: 'linear',
                    position: 'bottom',
                    scaleLabel: {
                        display: true,
                        labelString: 'Сахар %'
                    }
                }],
                yAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: 'Индекс насыщения'
                    }
                }]
            }
        }
    });
</script>

<?php
echo "<div style='display: flex; justify-content: center; margin-top: 20px;'>";
if ($page > 1) {
    echo "<a href='?page=" . ($page - 1) . "' class='btn'>Предыдущая страница</a>";
}
if (mysqli_num_rows($selected_food_result) == $limit) {
    echo "<a href='?page=" . ($page + 1) . "' class='btn' style='margin-left: 20px;'>Следующая страница</a>";
}
echo "</div>";
?>
