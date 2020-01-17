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
URL: /users  
Método: POST  
Campos: **name**, **email** e **password**
Headers: (nenhum header necessário)

###### Retorno
Em caso de sucesso é retornada mensagem de sucesso e os dados de id, name e email do usuário, sob o código de status 200 (OK).  
em caso de falha pode ser retornado código 422 (Unprocessable Entity) para erros de validação ou código 400 (Bad Request) para usuários já cadastrados.

##### Login

###### Requisição
URL: /login  
Método: POST  
Campos: **email** e **password**
Headers: (nenhum header necessário)

###### Retorno
Em caso de sucesso são retornados os dados de id, name e email, token e drink_counter do usuário, sob o código de status 200 (OK).  
em caso de falha pode ser retornado código 401 (Unauthorized) para dados de acesso incorretos.

##### Consulta de Usuário

###### Requisição
URL: /users/xx (ID de usuário)  
Método: GET  
Campos:  
Headers: **token**

###### Retorno
Em caso de sucesso são retornados os dados de id, name e email e drink_counter do usuário com o ID solicitado, sob o código de status 200 (OK).  
em caso de falha pode ser retornado código 404 (Not Found) para usuários inexistentes ou código 422 (Unprocessable Entity) para IDs inválidos.

##### Listagem de Usuários

###### Requisição
URL: /users  
Método: GET  
Campos:  
Headers: **token**, page, search

###### Retorno
Em caso de sucesso são retornados os campos results, page, pages, e o array users, com os dados de id, name e email, created_at, updated_at e drink_counter dos usuários que se encaixem no filtro ```search``` (caso utilizado), sob o código de status 200 (OK).  
em caso de falha pode ser retornado código 404 (Not Found) para usuários inexistentes ou não encontrados.

##### Edição de Usuário

###### Requisição
URL: /users/xx (ID de usuário)  
Método: POST  
Campos: name, email e password  
Headers: **token**

###### Retorno
Em caso de sucesso é retornada mensagem de sucesso e os dados de id, name e email do usuário, sob o código de status 200 (OK).  
em caso de falha pode ser retornado código 422 (Unprocessable Entity) para erros de validação.

[Collection no Postman](https://www.postman.com)
