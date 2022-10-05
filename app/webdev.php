<?php

namespace App;

require '../vendor/autoload.php';

use mysqli;

$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

class webdev
{

    private mysqli $current_db;
    private \DOMDocument $xml;
    private float $VAT;

    public function __construct()
    {
        $this->current_db = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);

        $this->VAT = 0.21;

        $this->xml = new \DOMDocument();
    }

    private function getFullCategoryPath($product_id): string
    {
        $full_category_path [] = $this->getProductCategory($product_id);
        $category_id = implode(mysqli_query($this->current_db, "
            SELECT category_id 
            FROM product_to_category 
            WHERE product_to_category.product_id = {$product_id} ")->fetch_row());

        $upper_category_id = implode(mysqli_query($this->current_db, "
            SELECT parent_id
            FROM category
            WHERE category.category_id = {$category_id}")->fetch_row());

        while ($upper_category_id != "0") {
            $current_category = implode(mysqli_query($this->current_db, "
            SELECT name
            FROM category_description
            WHERE category_id = {$upper_category_id} AND language_id = 1
            ")->fetch_row());

            $full_category_path[] = $current_category;

            $upper_category_id = implode(mysqli_query($this->current_db, "
            SELECT parent_id
            FROM category
            WHERE category_id = {$upper_category_id}
            ")->fetch_row());

        }

        return implode(" >> ", array_reverse($full_category_path));
    }

    private function getProductCategory($product_id): string
    {
        $category_in_lv = mysqli_query($this->current_db, "
            SELECT category_description.name
                FROM product, product_to_category, category_description
                    WHERE product.product_id = {$product_id} 
                        AND product_to_category.product_id = {$product_id}
                        AND product_to_category.category_id = category_description.category_id
                        AND category_description.language_id=1;");

        $data = $category_in_lv->fetch_row();
        return implode($data);
    }

    public function addTax($value): string
    {
        $value = number_format($value + $value * $this->VAT, 2);
        return $value;
    }

    public function formatImagePath($current_path_value): string
    {
        $current_path_value = 'https://www.webdev.lv/' . $current_path_value;
        return $current_path_value;
    }

    public function getProductDescriptionInLvEnRu($parent_row, $product_id)
    {

        $description_in_lv_en_ru = mysqli_query($this->current_db, "
            SELECT 
                product_description.language_id,
                product_description.meta_description
            FROM product_description, product
            WHERE  product.product_id = {$product_id} and product_description.product_id = {$product_id};");

        $data = $description_in_lv_en_ru->fetch_all();

        foreach ($data as $item) {
            switch ($item[0]) {
                case 1:
                    $row_name = 'lv';
                    break;
                case 2:
                    $row_name = 'en';
                    break;
                case 3:
                    $row_name = 'ru';
                    break;
            }

            $sentence = explode(' ', $item[1]);
            $compact_sentence = [];

            if (strlen($item[1]) > 200) {
                for ($i = 0; $i < count($sentence); $i++) {
                    $compact_sentence[] = $sentence[$i];
                    if (strlen(implode(' ', $compact_sentence)) > 200) {
                        array_pop($compact_sentence);
                        break;
                    }
                }
                $compact_sentence[] = '...';

                $child_row = $this->xml->createElement($row_name, implode(' ', $compact_sentence));
                $parent_row->appendChild($child_row);
            } else {
                $child_row = $this->xml->createElement($row_name, $item[1]);
                $parent_row->appendChild($child_row);
            }
        }
    }

    public function getProductNamesInLvEnRu($parent_row, $product_id)
    {

        $names_in_lv_en_ru = mysqli_query($this->current_db, "
            SELECT 
                product.product_id,
                product_description.language_id,
                product_description.name
            FROM product_description, product
            WHERE  product.product_id = {$product_id} and product_description.product_id = {$product_id};");

        $data = $names_in_lv_en_ru->fetch_all();
        $row_name = '';
        foreach ($data as $item) {
            switch ($item[1]) {
                case 1:
                    $row_name = 'lv';
                    break;
                case 2:
                    $row_name = 'en';
                    break;
                case 3:
                    $row_name = 'ru';
                    break;
            }
            $child_row = $this->xml->createElement($row_name, $item[2]);
            $parent_row->appendChild($child_row);
        }
    }

    public function getAllProductInfoXML()
    {

        $product_db = mysqli_query($this->current_db, "
            select
                    product.product_id,
                    product.model,
                    product.status,
                    product.quantity,
                    product.ean,
                    product.image as image_url,
                    DATE_FORMAT(product.date_added, '%d-%m-%Y') as date_added,
                    product.price,
                    DATE_FORMAT(product_special.date_end,'%Y-%m-%d') as date_end,
                    product_special.price as special_price
            
            from  product
            left join product_special
            ON product.product_id = product_special.product_id");

        $table = $this->xml->appendChild($this->xml->createElement('Products'));

        foreach ($product_db as $row) {
            $data = $this->xml->createElement('item');
            $table->appendChild($data);

            foreach ($row as $name => $value) {
                if ($name == "product_id") {
                    continue;
                } elseif ($name == "price") {
                    $value = $this->addTax($value);
                    $line = $this->xml->createElement($name, $value);
                    $data->appendChild($line);
                } elseif ($name == "image_url") {
                    $value = $this->formatImagePath($value);

                    $line = $this->xml->createElement($name, $value);
                    $data->appendChild($line);
                } elseif ($name == "status") {

                    $line = $this->xml->createElement($name, $value);
                    $data->appendChild($line);

                    $sub_row = $this->xml->createElement('name');
                    $data->appendChild($sub_row);

                    $this->getProductNamesInLvEnRu($sub_row, $row['product_id']);

                    $sub_row = $this->xml->createElement('description');
                    $data->appendChild($sub_row);

                    $this->getProductDescriptionInLvEnRu($sub_row, $row['product_id']);

                } elseif ($name == "date_end") {
                    if ($value != null) {
                        $correct_date = $value;
                    } else $correct_date = null;
                } elseif ($name == "special_price") {
                    if ($value != null && $correct_date != null && $correct_date >= date('Y-m-d')) {
                        $value = number_format($value + $value * $this->VAT, 2);
                        $line = $this->xml->createElement($name, $value);
                        $data->appendChild($line);
                    } else continue;
                } else {
                    $line = $this->xml->createElement($name, $value);
                    $data->appendChild($line);
                }

            }

            $current_category = $this->getProductCategory($row['product_id']);
            $current_category = $this->xml->createElement('category', $current_category);
            $data->appendChild($current_category);

            $full_category_path = $this->getFullCategoryPath($row['product_id']);
            $full_category_path = $this->xml->createElement('full_category', htmlspecialchars_decode($full_category_path));
            $data->appendChild($full_category_path);

            $this->xml->encoding = "utf-8";
            $this->xml->preserveWhiteSpace = true;
            $this->xml->formatOutput = true;
            $this->xml->save('products.xml');
        }
    }
}

$webdev_results = new webdev();
$webdev_results->getAllProductInfoXML();