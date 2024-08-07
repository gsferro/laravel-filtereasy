<p align="center">
    <img src="logo.png" alt="FilterEasy">
    <p align="center">
        <a href="https://packagist.org/packages/gsferro/filtereasy"><img alt="Latest Version" src="https://img.shields.io/packagist/v/gsferro/filtereasy"></a>
        <a href="https://packagist.org/packages/gsferro/filtereasy"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/gsferro/filtereasy"></a>
        <a href="https://packagist.org/packages/gsferro/filtereasy"><img alt="License" src="https://img.shields.io/packagist/l/gsferro/filtereasy"></a>
    </p>
</p>

------
Forma fácil e generica de fazer filtros na model no Laravel 

### Instalação:

```shell 
 composer require gsferro/filtereasy
```

### Implementação:

1. Na Model adicione a trait do pacote:
    ```php
    Use FilterEasy;
    ```

1. O pacote se utiliza do padrão `Mass Assignment` do Laravel para tratar os filtros, mas somente os campos do array 
`$fillable` são considerados, com exceção dos relacionamentos, que é explicado logo abaixo.

1. Por default, todos os itens são tratados com a cláusula `where`. 

1. Caso seja enviado um valor contendo um `array`, sera utilizado o`whereIn`.
   - Para `array`, coloque no name um `[]`, exemplo:
       ```php
       permission_id[]
       ```

1. Para verificações utilizando o operador `like` ou uma verificação que force um `bool`, é necessário inicializar os 
seguintes atributos, respectivamente:
    ```php
    /*
    |---------------------------------------------------
    | FilterEasy
    |---------------------------------------------------
    */
    private array $likeFilterFields = [
        // coloque os campos para like
    ];
    
    private array $boolFilterFields = [
        // coloque os campos bool
    ];
    ```

1. Busca com **Datas e Hora**:
   1. Para fazer verificação de periodos de data, usar os `sufixo` a seguir, não precisam ser usados juntos:
       1. `:start`
       1. `:end`

       -  Exemplos:
         - Com o `:start` será implementado `where('created_at', '>=', $value)`
            >  created_at:start
         - Com o `:end` será implementado `where('updated_at', '<=', $value)`  
            >  updated_at:end

    1. Para implementar no formulário de filtro (ou se for utilizar diretamente no controller), basta colocar o attr: 
     `name="created_at:start"` | `name="updated_at:end"`
    
    1. Para implementar uma consulta como a do `whereBetween`, basta colocar o mesmo campo 2x, cada uma com um  `sufixo` 
       -  Exemplo:
       > deleted_at:start
        
       > deleted_at:end 

    - O sql gerado será assim:
      ```` sql
      where (`created_at`) >= ? and (`created_at`) <= ?  
      ````
      
    1. Obs: Até a  `v1.1` era utilizado `whereDate`, porém assim não é possivel enviar também a hora, caso o campo 
    seja um datetime.

1. Para **Relacionamento**:
    
- Utilize a notação `relationName:relationField` o pacote incluirá o relacionamento na query.
- Esta notação não precisa estar no `$fillable`, mas o `relationName` precisa ser um relacionamento implementando na 
  model.
  -   Exemplo:
        ```php
        posts:title
        relationName = posts
        relationField = title
        ```
- Caso queira utilizar o `like`, coloque o `relationName:relationField` no `$likeFilterFields`:
    ```php
    private array  $likeFilterFields = [
        // demais campos
        "posts:title",
  ];
    ```

### License

- **[MIT license](https://opensource.org/licenses/MIT)**.