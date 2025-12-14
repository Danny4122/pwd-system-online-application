<?php
$host = "pdao_postgres";
$dbname = "pdao_db";
$user = "postgres";
$password = "thesisit";

$conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Database connection failed: " . pg_last_error($conn));
}
