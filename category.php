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
$content = "<h2>{$category['name']}</h2>";

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
                        <th>Уровень насыщения</th>
                    </tr>";

    while($food = mysqli_fetch_assoc($food_result)){
        $satiety_text = '';

        if ($food['satiety_index'] <= 0.4) {
            $satiety_text = "Трудно насытиться";
        } elseif ($food['satiety_index'] > 0.4 && $food['satiety_index'] < 0.7) {
            $satiety_text = "Достаточно сытно";
        } else {
            $satiety_text = "Очень сытно";
        }

        $content .= "<tr>
                        <td>{$food['name']}</td>
                        <td>{$satiety_text}</td>
                    </tr>";
    }

    $content .= "</table>";

    ob_start();
    require("template.php");
    $output = ob_get_clean();

    echo $output;

    echo "<div style='display: flex; justify-content: space-around;'>
            <div>
                <h3>Содержание сахара</h3>
                <canvas id='sugarSatietyChart' width='400' height='200'></canvas>
            </div>
            <div>
                <h3>Содержание углеводов</h3>
                <canvas id='carbsSatietyChart' width='400' height='200'></canvas>
            </div>
          </div>";

    $nextPage = $page + 1;
    $prevPage = $page - 1;

    $prevDisabled = ($prevPage <= 0) ? "disabled" : "";
    $nextDisabled = (mysqli_num_rows($food_result) < $limit) ? "disabled" : "";
    
    if ($page == 1) {
        echo "<div>
                <a href='?id={$category['id']}&page={$nextPage}' class='btn' $nextDisabled>Следующая страница</a>
              </div>";
    } elseif (mysqli_num_rows($food_result) < $limit) {
        echo "<div>
                <a href='?id={$category['id']}&page={$prevPage}' class='btn' $prevDisabled>Предыдущая страница</a>
              </div>";
    } else {
        echo "<div>
                <a href='?id={$category['id']}&page={$prevPage}' class='btn' $prevDisabled>Предыдущая страница</a>
                <a href='?id={$category['id']}&page={$nextPage}' class='btn' $nextDisabled>Следующая страница</a>
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
            backgroundColor: 'rgba(255, 99, 132, 0.5)'
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
            data: carbsData.map((value, index) => ({
                x: value,
                y: satietyData[index]
            })),
            backgroundColor: 'rgba(54, 162, 235, 0.5)'
        }]
    },
    options: {
        scales: {
            xAxes: [{
                type: 'linear',
                position: 'bottom',
                scaleLabel: {
                    display: true,
                    labelString: 'Углеводы'
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

