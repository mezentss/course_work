<?php
require("session.php");
require("db_connect.php");
require("facts.php");

if (isset($_GET["user_id"])) {
    $_SESSION["user"] = ["id" => $_GET["user_id"]];
    $session_user = $_SESSION["user"];
}

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
$content = "<div style='display: flex;'>
<div  class='category-link' style='flex: 1; 
border: 2px dotted #78E251;
border-radius: 10px;'>";
if(!$result || mysqli_num_rows($result) == 0){
    $content .= "В базе данных нет категорий.";
} else {
    $imageCounter = 1;
    $content .= "<h2>Категории</h2>";
    $content .= '<script>
                  var categoryImages = ["images/category1.jpg", "images/category2.jpg", "images/category3.jpg",
                    "images/category4.jpg","images/category5.jpg","images/category6.jpg","images/category7.jpg",
                    "images/category8.jpg","images/category9.jpg","images/category10.jpg","images/category11.jpg",
                  ];
                </script>'; 

    while($category = mysqli_fetch_assoc($result)){
        $content .= "<h3 class='category-item' data-category-index='$imageCounter'>
        <a href='category.php?id={$category['id']}' class='category-link'><span>{$category['name']}</span></a>
        <img src='images/category{$imageCounter}.jpg' class='category-image' alt='{$category['name']}' />
    </h3>";
            $imageCounter++;
    }
}

$randomFact = get_random_fact();

$content .= "</div>
            <div style='flex: 1; 
            background: radial-gradient(
                circle, 
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.5) 0%,
                #78E251 50%, 
                rgba(255, 255, 255, 0.5) 90%,
                rgba(255, 255, 255, 0) 100% 
            );
            padding: 10px;'>
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

    document.addEventListener('DOMContentLoaded', function() {
        var categoryLinks = document.querySelectorAll('.category-link');
        categoryLinks.forEach(function(link) {
            link.addEventListener('mouseenter', function() {
                var categoryIndex = parseInt(this.getAttribute('data-category-index')) - 1;
                this.style.backgroundImage = 'url(' + categoryImages[categoryIndex] + ')';
            });
            link.addEventListener('mouseleave', function() {
                this.style.backgroundImage = 'none';
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
    var categoryItems = document.querySelectorAll('.category-item');
    categoryItems.forEach(function(item) {
        item.addEventListener('mouseenter', function() {
            var image = this.querySelector('.category-image');
            image.style.opacity = 1; // при наведении показываем изображение
        });
        item.addEventListener('mouseleave', function() {
            var image = this.querySelector('.category-image');
            image.style.opacity = 0; // при уходе курсора скрываем изображение
        });
    });
});

</script>