<?php

require_once('./vendor/autoload.php');
try{
    $count = 1000;

    $pdo  = new PDO('mysql:host=localhost;dbname=matcha', 'root', 'CTSJkVNyKqaSqM23', array(
        PDO::ATTR_PERSISTENT => true
    ));
    $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );


    $seeder = new \tebazil\dbseeder\Seeder($pdo);
    $generator = $seeder->getGeneratorConfigurator();
    $faker = $generator->getFakerConfigurator();

    $stmt = $pdo->prepare("SET FOREIGN_KEY_CHECKS=0");
    $stmt->execute();


    $seeder->table('users')->columns([
            'id_users',
            'username' => function () {
            $str = "";
            $characters = array_merge(range('A','Z'), range('a','z'));
            $max = count($characters) - 1;
                for ($i = 0; $i < 6; $i++) {
                    $rand = mt_rand(0, $max);
                    $str .= $characters[$rand];
                }
                return $str;
            },
            'email' => $faker->email,
            'password' => '$2y$10$e5SEVds.53kAOiIftRdox.607A2/SEZCRL1cp9DeR8b3PXriSPqM2',
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'validation' => 1,
        ])->rowQuantity( $count );      

    $seeder->table('profile')->columns([
            'id',
            'popularity' => 50,
            'gender' => $faker->randomElement(['male','female','other']),
            'orientation' => $faker->randomElement(['straight','gay','bi']),
            'birthdate' => function(){
                return date('Y-m-d H:i:s', rand(0, time()));
            },
            'bio' => $faker->text(150),
            'characteristics' => '#fun',
            'images' => 'cyril_ferraud.jpg',
        ])->rowQuantity( $count );  

    $seeder->table('online_user')->columns([
            'id',
            'log_time' => function() use($faker) { return date('Y-m-d H:i:s');},
        ])->rowQuantity( $count );

    $seeder->table('geo')->columns([
            'id_user',
            'lat' => function(){
                return mt_rand(45, 49);
            },
            'lng' => 2.285369,
        ])->rowQuantity( $count );

    $seeder->refill();

    $stmt = $pdo->prepare("SET FOREIGN_KEY_CHECKS=1");
    $stmt->execute();

} catch(Exception $e){
    echo '<pre>';print_r($e);echo '</pre>';
}

?>