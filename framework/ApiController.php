<?php
declare(strict_types=1);

require_once __DIR__ .'/Request.php';
require_once __DIR__ .'/../rest_api/utils.php';

/**
 * @brief Controlls and handles calls to the API
 */
class ApiController {
    private Request $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function handle(array $args) {
        $subroutes = [
            'product' => 'products',
            'size' => 'sizes',
            'condition' => 'conditions',
            'category' => 'categories',
            'brand' => 'brands',
            'user' => 'users',
            'message' => 'messages',
            'wishlist' => 'wishlist'
        ];

        $resource = $args[0];
        $subroute = $subroutes[$resource];

        if ($subroute) {
            $this->$subroute($args);
        } else {
            header('Content-Type: application/json');
            sendNotFound();
        }
    }

    private function products(array $args) {
        require_once __DIR__ . '/../rest_api/api_product.php';
    }

    private function sizes(array $args) {
        require_once __DIR__ . '/../rest_api/api_size.php';
    }

    private function conditions(array $args) {
        require_once __DIR__ . '/../rest_api/api_condition.php';
    }

    private function categories(array $args) {
        require_once __DIR__ . '/../rest_api/api_category.php';
    }

    private function brands(array $args) {
        require_once __DIR__ . '/../rest_api/api_brand.php';
    }

    private function users(array $args) {
        require_once __DIR__ . '/../rest_api/api_users.php';
    }

    private function messages(array $args) {
        require_once __DIR__ . '/../rest_api/api_message.php';
    }

    private function wishlist(array $args) {
        require_once __DIR__ . '/../rest_api/api_wishlist.php';
    }
}