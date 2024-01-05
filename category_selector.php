<?php
function getCategoryOptions($conn)
{
    $categories_query = "SELECT * FROM categories";
    $categories_result = mysqli_query($conn, $categories_query);

    $options = "";

    while ($category = mysqli_fetch_assoc($categories_result)) {
        $options .= "<option value=\"{$category['id']}\">{$category['name']}</option>";
    }

    return $options;
}



