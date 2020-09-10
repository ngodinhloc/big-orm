<?php
require_once('./vendor/autoload.php');

$authCredentials = [
    'clientId' => 'acxu0p8rfh15m8n0fn4obuxmb52tgwk',
    'authToken' => 'cyfbhepc71mns8xnykv86wruxzh45wi',
    'storeHash' => 'e87g0h02r5',
    'baseUrl' => 'https://api.service.bcdev'
];
$options = [
    'verify' => false,
    'timeout' => 60,
    'contentType' => 'application/json',
    'debug' => true
];

try {
    $config = new \Bigcommerce\ORM\Configuration($authCredentials, $options);
    $entityManager = $config->configEntityManager();

    /** create new object and set data */
    $review1 = new \Bigcommerce\ORM\Entities\ProductReview();
    $review1
        ->setProductId(111)
        ->setTitle('Great Product')
        ->setText('I love this product so much')
        ->setStatus('approved')
        ->setRating(5)
        ->setName('Ken Ngo')
        ->setEmail('ken.ngo@bigcommerce.com')
        ->setDateReviewed(date('c'));
    $isPatch1 = $review1->isPatched();
    echo $isPatch1 . PHP_EOL;

    /** create object using EntityManager */
    $data = [
        'product_id' => 111,
        'title' => 'Very good product',
        'text' => 'I love this product a lot',
        'status' => 'approved',
        'rating' => 5,
        'name' => 'Ken Ngo',
        'email' => 'ken.ngo@bigcommerce.com',
        'date_reviewed' => date('c')
    ];
    $review2 = $entityManager->new(\Bigcommerce\ORM\Entities\ProductReview::class, $data);
    $isPatch2 = $review2->isPatched();
    echo $isPatch2 . PHP_EOL;

    /** create new entity then patch it with data */
    $review3 = new \Bigcommerce\ORM\Entities\ProductReview();
    $review3 = $entityManager->patch($review3, $data);
    $isPatch3 = $review3->isPatched();
    echo $isPatch3 . PHP_EOL;

} catch (\Exception $e) {
    echo $e->getMessage();
}