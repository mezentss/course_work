<?php
require("session.php");
require("db_connect.php");

$user_id = ($session_user)['id'];

$sugar_level_query = "SELECT sugar_level FROM current_sugar WHERE user_id = $user_id";
$sugar_level_result = mysqli_query($conn, $sugar_level_query);
$sugar_level = mysqli_fetch_assoc($sugar_level_result)['sugar_level'];

$selected_cluster_id_query = "SELECT cluster_id FROM food ORDER BY satiety_index DESC LIMIT 1";
if ($sugar_level < 5.4) {
    $selected_cluster_id_query = "SELECT cluster_id FROM food ORDER BY satiety_index DESC LIMIT 1";
} elseif ($sugar_level > 8.4) {
    $selected_cluster_id_query = "SELECT cluster_id FROM food ORDER BY satiety_index ASC LIMIT 1";
}

$selected_cluster_id_result = mysqli_query($conn, $selected_cluster_id_query);
$selected_cluster_id = mysqli_fetch_assoc($selected_cluster_id_result)['cluster_id'];

$selected_food_query = "SELECT * FROM food WHERE cluster_id = $selected_cluster_id";
$selected_food_result = mysqli_query($conn, $selected_food_query);

$selected_food_data = [];
while ($row = mysqli_fetch_assoc($selected_food_result)) {
    $selected_food_data[] = $row;
}

$title = "Возможно вы захотите перекусить этими продуктами:";
$content = "<table><tr><th>Название продукта</th><th>Индекс насыщения</th></tr>";
$favourite_present = false;

foreach ($selected_food_data as $row) {
    if (checkIfFavourite($conn, $row['category'], $user_id)) {
        $favourite_present = true;
    }
    $content .= "<tr><td>" . $row['name'] . "</td><td>" . $row['satiety_index'] . "</td></tr>";
}

if (!$favourite_present) {
    $favourite_product_query = "SELECT * FROM food WHERE category = (SELECT favourite FROM likes WHERE user_id = $user_id) LIMIT 1";
    $favourite_product_result = mysqli_query($conn, $favourite_product_query);
    $favourite_product = mysqli_fetch_assoc($favourite_product_result);
    $content .= "<tr><td>" . $favourite_product['name'] . "</td><td>" . $favourite_product['satiety_index'] . "</td></tr>";
}

$content .= "</table>";
require("template.php");

function checkIfFavourite($conn, $category_id, $user_id) {
    $check_favourite_query = "SELECT * FROM likes WHERE user_id = $user_id AND favourite = $category_id";
    $check_favourite_result = mysqli_query($conn, $check_favourite_query);
    return mysqli_num_rows($check_favourite_result) > 0;
}
?>

<h3>График содержания сахара и насыщенности</h3>
<canvas id='sugarSatietyChart' width='400' height='200'></canvas>

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