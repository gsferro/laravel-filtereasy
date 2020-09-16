# laravel FilterEasy
Forma easy e generica de fazer filtros na model

# Uso
No controller:
```php
    Use FilterEasy;
```

### No Form
- Para fazer verificação de data, usar os prefixos:
    1. :start
    1. :end
    
    exemplo:
        `created_at:start`
        `updated_at:end`

    obs: 
        Pode ser usar separadamente
        
- Para relacionamento:        
    relName:fk
    
    exemplo:
        posts:post_id aonde:
        relName = posts
        fk = post_id

- Para verificação usando Like:
    criar o atributo $likeFilterFields devolvendo um array com os campos

- Para relacionamento com Like:        
    relName:fieldName
    
    exemplo:
        posts:description aonde:
        relName = posts
        fieldName = description

    criar o atributo $likeFilterFields devolvendo um array com os campos
    repare que nesse caso, o atributo ficaria assim:
    ```php
        $likeFilterFields = ["posts:description"];
    ```

- Para verificação booleana:
    criar o atributo boolFilterFields devolvendo um array com os campos
    
- Para verificanção com In:
    passar o campo como array.    
    
    exemplo:
        permission_id[]
        
- Para os demais campos é so colocar o nome do campo que também tem que estar no fillable