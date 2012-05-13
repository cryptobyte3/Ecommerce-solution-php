<?php

$b = new Base_Class();

$normal_reads = new timer();

for ( $i = 0; $i < 5000; $i++ ) {
    $b->db->get_results( "SELECT `product_id`, `title`, `slug`, `brand_id`, `industry_id` FROM `products` WHERE `publish_visibility` <> 'deleted' LIMIT 10000", ARRAY_A );
}

echo 'Normal Reads: ' . $normal_reads->stop() . "\n<br />";

$normal_writes = new timer();

for ( $i = 0; $i < 5000; $i++ ) {
    $b->db->insert( 'test', array( 'id' => 5, 'name' => 'Random Name', 'message' => 'Hello there! How are you? Im glad I could finally meet you!', 'date_created' => '2012-05-13 10:08:00' ), 'isss' );
}

echo 'Normal Writes: ' . $normal_writes->stop() . "\n<br />";

library('rb');
R::freeze( true );
R::setup('mysql:host=199.204.138.78;dbname=imaginer_system','imaginer_admin','rbDxn6kkj2e4');

$rb_reads = new timer();

for( $i = 0; $i < 5000; $i++ ) {
    R::getAll( "SELECT `product_id`, `title`, `slug`, `brand_id`, `industry_id` FROM `products` WHERE `publish_visibility` <> 'deleted' LIMIT 10000" );
}

echo 'RedBean Reads: ' . $rb_reads->stop() . "\n<br />";

$rb_writes = new timer();

for ( $i = 0; $i < 5000; $i++ ) ) {
    R::exec( "INSERT INTO `test` ( `id`, `name`, `message`, date_created` ) VALUES ( :id, :name, :message, :date_created)", array( ':id' => 5, ':name' => 'Random Name', ':message' => 'Hello there! How are you? Im glad I could finally meet you!', ':date_created' => '2012-05-13 10:08:00' ) );
}

echo 'RedBean Writes: ' . $rb_writes->stop() . "\n<br />";