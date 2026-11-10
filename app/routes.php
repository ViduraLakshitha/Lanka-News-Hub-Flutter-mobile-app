<?php
use App\Controllers\NewsController as News;
use App\Controllers\AccountController as Account;
use App\Controllers\ClassifiedController as Classified;
use App\Controllers\NewsroomController as Admin;
use App\Controllers\HealthController as Health;
use App\Controllers\ApiController as Api;
$router->get('/health', [Health::class, 'check']);
$router->get('/', [News::class,'index']);
$router->get('/news/{slug}', [News::class,'article']);
$router->get('/category/{slug}', [News::class,'listing']);
$router->get('/edition/{section}', [News::class,'listing']);
$router->get('/search', [News::class,'search']);
$router->get('/feed', [News::class,'rss']);

foreach (['/account/login','/admin/login'] as $path) { $router->get($path,[Account::class,'login']); $router->post($path,[Account::class,'login']); }
$router->get('/account/register',[Account::class,'register']);
$router->post('/account/register',[Account::class,'register']);
$router->get('/account',[Account::class,'dashboard']);
$router->post('/account',[Account::class,'dashboard']);
$router->post('/account/logout',[Account::class,'logout']);
// Classifieds module routes
$router->get('/classifieds',[Classified::class,'index']);
$router->get('/classifieds/create',[Classified::class,'edit']);
$router->post('/classifieds/create',[Classified::class,'edit']);
$router->get('/classifieds/edit/{id}',[Classified::class,'edit']);
$router->post('/classifieds/edit/{id}',[Classified::class,'edit']);
$router->get('/classifieds/{id}',[Classified::class,'show']);
foreach (['renew','withdraw','favorite','delete'] as $action) $router->post('/classifieds/'.$action.'/{id}',[Classified::class,$action]);
$router->get('/admin',[Admin::class,'index']);
$router->get('/admin/articles',[Admin::class,'articles']);
foreach (['/admin/articles/create','/admin/articles/edit/{id}'] as $path) { $router->get($path,[Admin::class,'editArticle']); $router->post($path,[Admin::class,'editArticle']); }
$router->post('/admin/articles/archive/{id}',[Admin::class,'archive']);
$router->post('/admin/articles/restore/{id}',[Admin::class,'restore']);
$router->post('/admin/articles/delete/{id}',[Admin::class,'deleteArticle']);
$router->post('/admin/media/delete/{id}',[Admin::class,'deleteMedia']);
$router->post('/admin/categories/delete/{id}',[Admin::class,'deleteCategory']);
foreach (['media','categories','classifieds','advertisements','users','settings','menus','payments'] as $section) { $router->get('/admin/'.$section,[Admin::class,$section]); $router->post('/admin/'.$section,[Admin::class,$section]); }
$router->get('/admin/reports',[Admin::class,'reports']);
$router->get('/admin/logs',[Admin::class,'logs']);

// Mobile Application REST API v1
$router->get('/api/v1/bootstrap', [Api::class, 'bootstrap']);
$router->get('/api/v1/news', [Api::class, 'newsList']);
$router->get('/api/v1/news/{id}', [Api::class, 'newsDetail']);
$router->get('/api/v1/live-updates', [Api::class, 'liveUpdates']);
$router->get('/api/v1/classifieds', [Api::class, 'classifiedsList']);
$router->get('/api/v1/classifieds/{id}', [Api::class, 'classifiedDetail']);
$router->post('/api/v1/classifieds', [Api::class, 'submitClassified']);
$router->post('/api/v1/classifieds/{id}', [Api::class, 'updateClassified']);
$router->post('/api/v1/classifieds/{id}/renew', [Api::class, 'renewClassified']);
$router->post('/api/v1/classifieds/{id}/delete', [Api::class, 'deleteClassified']);
$router->post('/api/v1/classifieds/{id}/favorite', [Api::class, 'toggleFavorite']);
$router->get('/api/v1/account/ads', [Api::class, 'userAds']);
$router->get('/api/v1/account/favorites', [Api::class, 'userFavorites']);
$router->post('/api/v1/auth/register', [Api::class, 'register']);
$router->post('/api/v1/auth/login', [Api::class, 'login']);
$router->get('/api/v1/auth/me', [Api::class, 'me']);
$router->post('/api/v1/auth/profile', [Api::class, 'updateProfile']);
$router->get('/api/v1/account/saved-articles', [Api::class, 'userSavedArticles']);
$router->post('/api/v1/articles/{id}/save', [Api::class, 'toggleSavedArticle']);
$router->get('/api/v1/notifications', [Api::class, 'notificationsList']);
$router->get('/api/v1/notifications/preferences', [Api::class, 'getNotificationPreferences']);
$router->post('/api/v1/notifications/preferences', [Api::class, 'updateNotificationPreferences']);
$router->post('/api/v1/notifications/device-token', [Api::class, 'registerDeviceToken']);
