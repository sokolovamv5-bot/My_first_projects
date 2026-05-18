<?php
/**
 * Application Routes
 */

return [
    // Public routes
    'GET /' => 'HomeController@index',
    'GET /catalog/products' => 'CatalogController@productsIndex',
    'GET /catalog/master-classes' => 'CatalogController@mcIndex',
    
    'GET /product/{id}' => 'ProductController@show',
    'GET /master-class/{id}' => 'MasterClassController@show',
    
    'GET /login' => 'UserController@showLoginForm',
    'POST /login' => 'UserController@login',
    'GET /register' => 'UserController@showRegisterForm',
    'POST /register' => 'UserController@register',
    'POST /logout' => 'UserController@logout',
    
    'GET /profile' => 'UserController@profile',
    'POST /profile' => 'UserController@updateProfile',
    
    'GET /my/master-classes' => 'UserController@myMasterClasses',
    'GET /my/orders' => 'UserController@myOrders',
    'GET /my/favorites' => 'FavoritesController@index',
    'POST /favorites/toggle' => 'FavoritesController@toggle',
    
    'GET /cart' => 'CartController@index',
    'POST /cart/add' => 'CartController@add',
    'POST /cart/update' => 'CartController@update',
    'POST /cart/remove' => 'CartController@remove',
    
    'GET /checkout' => 'OrderController@showCheckout',
    'POST /checkout' => 'OrderController@processCheckout',
    
    'GET /master-class/{id}/buy' => 'MasterClassController@buyForm',
    'POST /master-class/{id}/buy' => 'MasterClassController@processBuy',
    
    'GET /order/success' => 'OrderController@successPage',
    'GET /master-class/{id}/video' => 'MasterClassController@streamVideo',
    
    // API routes
    'GET /api/products' => 'Api\ProductController@index',
    'GET /api/products/{id}' => 'Api\ProductController@show',
    'GET /api/master-classes' => 'Api\MasterClassController@index',
    'GET /api/master-classes/{id}' => 'Api\MasterClassController@show',
    
    'GET /api/cart' => 'Api\CartController@index',
    'POST /api/cart/items' => 'Api\CartController@store',
    'PATCH /api/cart/items/{id}' => 'Api\CartController@update',
    'DELETE /api/cart/items/{id}' => 'Api\CartController@destroy',
    
    'GET /api/favorites' => 'Api\FavoritesController@index',
    'POST /api/favorites' => 'Api\FavoritesController@store',
    'DELETE /api/favorites/{id}' => 'Api\FavoritesController@destroy',
    
    'GET /api/my/master-classes' => 'Api\UserController@myMasterClasses',
    
    'GET /api/master-classes/{id}/questions' => 'Api\MasterClassController@questions',
    'POST /api/master-classes/{id}/questions' => 'Api\MasterClassController@askQuestion',
    
    // Admin routes
    'GET /admin' => 'Admin\AdminDashboardController@index',
    'GET /admin/dashboard' => 'Admin\AdminDashboardController@index',
    
    'GET /admin/products' => 'Admin\AdminProductController@index',
    'GET /admin/products/create' => 'Admin\AdminProductController@create',
    'POST /admin/products' => 'Admin\AdminProductController@store',
    'GET /admin/products/{id}/edit' => 'Admin\AdminProductController@edit',
    'POST /admin/products/{id}/update' => 'Admin\AdminProductController@update',
    'POST /admin/products/{id}/delete' => 'Admin\AdminProductController@delete',
    
    'GET /admin/master-classes' => 'Admin\AdminMasterClassController@index',
    'GET /admin/master-classes/create' => 'Admin\AdminMasterClassController@create',
    'POST /admin/master-classes' => 'Admin\AdminMasterClassController@store',
    'GET /admin/master-classes/{id}/edit' => 'Admin\AdminMasterClassController@edit',
    'POST /admin/master-classes/{id}/update' => 'Admin\AdminMasterClassController@update',
    'POST /admin/master-classes/{id}/delete' => 'Admin\AdminMasterClassController@delete',
    
    'GET /admin/master-classes/{id}/content' => 'Admin\AdminMasterClassController@editContent',
    'POST /admin/master-classes/{id}/content' => 'Admin\AdminMasterClassController@saveContent',
    
    'GET /admin/orders' => 'Admin\AdminOrderController@index',
    'GET /admin/orders/{id}' => 'Admin\AdminOrderController@show',
    'POST /admin/orders/{id}/status' => 'Admin\AdminOrderController@updateStatus',
    
    'GET /admin/questions' => 'Admin\AdminQuestionController@index',
    'GET /admin/questions/{id}' => 'Admin\AdminQuestionController@showInContext',
    'POST /admin/questions/{id}/answer' => 'Admin\AdminQuestionController@answer',
    
    'POST /admin/edit-mode/toggle' => 'Admin\AdminContentController@toggleEditMode',
];
