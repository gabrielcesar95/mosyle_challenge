# Mosyle Challenge

API para registro de quantas vezes o usário bebeu água

#### Funcionalidades:
    
* Cadastro de usuários
* Autenticação via Token
* Cadastro, visualização, alteração e exclusão de usuários
* Cadastro de consumos de água


#### Como utilizar
Campos em **negrito** são obrigatórios

##### Cadastro de Usuário

###### Requisição
URL: /Users  
Método: POST  
Campos: **name**, **email** e **password**
Headers: (nenhum header necessário)

###### Retorno
Em caso de sucesso é retornada mensagem de sucesso e os dados de id, name e email do usuário, sob o código de status 200 (OK).  
em caso de falha pode ser retornado código 422 (Unprocessable Entity) para erros devalidação ou código 400 (Bad Request) para usuários já cadastrados 

[Collection no Postman](https://www.postman.com)
