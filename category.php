<?php
require("session.php");
require("db_connect.php");

$result = mysqli_query($conn, "SELECT * FROM categories WHERE id = {$_GET['id']}");

if(!$result || mysqli_num_rows($result) == 0){
    echo "В базе данных нет категории с таким id.";
    exit;
}

$category = mysqli_fetch_assoc($result);
$title = "Категория: " . $category['name'];
$content = "";

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$food_result = mysqli_query($conn, "SELECT * FROM food WHERE category = {$category['id']} LIMIT $limit OFFSET $offset");

if(!$food_result || mysqli_num_rows($food_result) == 0){
    $content .= "<p>К сожалению, в этой категории нет продуктов.</p>";
} else {
    $content .= "<table>
    <tr>
        <th>Название</th>
        <th>Содержание сахара</th>
        <th>Содержание углеводов</th>
    </tr>";
while($food = mysqli_fetch_assoc($food_result)){
$sugar_text = '';
$carbs_text = '';

if ($food['sugar'] <= 0.4) {
$sugar_text = "Мало";
} elseif ($food['sugar'] > 0.4 && $food['sugar'] < 0.7) {
$sugar_text = "В норме";
} else {
$sugar_text = "Много";
}

if ($food['carbs'] <= 0.3) {
$carbs_text = "Мало";
} elseif ($food['carbs'] > 0.3 && $food['carbs'] < 0.7) {
$carbs_text = "В норме";
} else {
$carbs_text = "Много";
}

$content .= "<tr>
    <td>{$food['name']}</td>
    <td style='text-align: center;'>{$sugar_text}</td>
    <td style='text-align: center;'>{$carbs_text}</td>
  </tr>";
}
$content .= "</table>";

    ob_start();
    require("template.php");
    $output = ob_get_clean();

    echo $output;

    echo "<div style='display: flex; flex-direction: column; align-items: center;'>
            <div>
                <canvas id='sugarSatietyChart' width='600' height='250'></canvas>
            </div>
            <div>
                <canvas id='carbsSatietyChart' width='600' height='250'></canvas>
            </div>
          </div>";

    $nextPage = $page + 1;
    $prevPage = $page - 1;

    $prevDisabled = ($prevPage <= 0) ? "disabled" : "";
    $nextDisabled = (mysqli_num_rows($food_result) < $limit) ? "disabled" : "";
    
    if ($page == 1 && mysqli_num_rows($food_result) == $limit) {
        echo "<div style='text-align: center;'>
                <a href='?id={$category['id']}&page={$nextPage}' class='btn' $nextDisabled>Следующая страница</a>
              </div>";
    } elseif ($page > 1 && mysqli_num_rows($food_result) == $limit) {
        echo "<div style='text-align: center;'>
                <a href='?id={$category['id']}&page={$prevPage}' class='btn' $prevDisabled>Предыдущая страница</a>
                <span style='margin: 0 10px;'></span> 
                <a href='?id={$category['id']}&page={$nextPage}' class='btn' $nextDisabled>Следующая страница</a>
              </div>";
    } elseif ($page > 1 && mysqli_num_rows($food_result) < $limit) {
        echo "<div style='text-align: center;'>
                <a href='?id={$category['id']}&page={$prevPage}' class='btn' $prevDisabled>Предыдущая страница</a>
              </div>";
    }    
}    
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
<script>
var sugarData = [];
var satietyData = [];
var carbsData = [];
var nameData = [];

<?php
mysqli_data_seek($food_result, 0);
while ($food = mysqli_fetch_assoc($food_result)) {
?>
    sugarData.push(<?php echo $food['sugar']; ?>);
    satietyData.push(<?php echo $food['satiety_index']; ?>);
    carbsData.push(<?php echo $food['carbs']; ?>);
    nameData.push("<?php echo $food['name']; ?>");
<?php
}
?>

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
            backgroundColor: '#F45870'
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

var carbsSatietyCtx = document.getElementById('carbsSatietyChart').getContext('2d');
var carbsSatietyChart = new Chart(carbsSatietyCtx, {
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
                    labelString: 'Углеводы %'
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

</body>
</html>

