### Installations


add to routes/web.php
```php
$router->post('/user/todo/select', [
	'middleware' => 'auth',
	'uses' => 'TodoController@select'
]);
```
