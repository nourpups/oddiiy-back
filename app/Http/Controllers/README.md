```php
return [
'collections' => (new CollectionCollection($collections))
    ->response()
    ->getData(true),
'products' => $productResourceCollection
    ->response()
    ->getData(true),
];
```
эти дополнения в виде `->response()->getData(true)` нужны для того, 
чтобы, мета данные не исчезали при возвращении нескольких ресурсов за раз
саму проблему [смотреть тут](https://laracasts.com/discuss/channels/laravel/paginate-while-returning-array-of-api-resource-objects-to-the-resource-collection), 
решение предложил `mahmoudyounes`

