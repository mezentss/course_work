<?php

function get_random_fact() {
    require("db_connect.php");
    $random_fact_query = "SELECT description FROM facts ORDER BY RAND() LIMIT 1";
    $random_fact_result = mysqli_query($conn, $random_fact_query);
    $random_fact = mysqli_fetch_assoc($random_fact_result);    
    
    return $random_fact['description'];
}
?>
