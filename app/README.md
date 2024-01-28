# DESAFIO BACKEND

## Configuração do Ambiente

### Requisitos

- Instalar o _PHP >= 8.0_ e [extensões](https://www.php.net/manual/pt_BR/extensions.php) (**não esquecer de instalar as seguintes extensões: _pdo_, _pdo_sqlite_ e _sqlite3_**);
- Instalar o [_SQLite_](https://www.sqlite.org/index.html);
- Instalar o [_Composer_](https://getcomposer.org/).

### Instalação

- Instalar dependências pelo _composer_ com `composer install` na raiz do projeto;
- Servir a pasta _public_ do projeto através de algum servidor.
  (_Sugestão [PHP Built in Server](https://www.php.net/manual/en/features.commandline.webserver.)_. Exemplo para servir a pasta public: `php -S localhost:8000 -t public`)

## Sobre o Projeto

- O cliente XPTO Ltda. contratou seu serviço para realizar alguns ajustes em seu sistema de cadastro de produtos;
- O sistema permite o cadastro, edição e remoção de _produtos_ e _categorias de produtos_ para uma _empresa_;
- Para que sejam possíveis os cadastros, alterações e remoções é necessário um usuário administrador;
- O sistema possui categorias padrão que pertencem a todas as empresas, bem como categorias personalizadas dedicadas a uma dada empresa. As categorias padrão são: (`clothing`, `phone`, `computer` e `house`) e **devem** aparecer para todas as _empresas_;
- O sistema tem um relatório de dados dedicado ao cliente.

## Sobre a API

As rotas estão divididas em:

- _CRUD_ de _categorias_;
- _CRUD_ de _produtos_;
- Rota de busca de um _relatório_ que retorna um _html_.

E podem ser acessadas através do uso do Insomnia, Postman ou qualquer ferramenta de sua preferência.

**Atenção**, é bem importante que se adicione o _header_ `admin_user_id` com o id do usuário desejado ao acessar as rotas para simular o uso de um usuário no sistema.

A documentação da API se encontra na pasta `docs/api-docs.pdf`

- A documentação assume que a url base é `localhost:8000` mas você pode usar qualquer outra url ao configurar o servidor;
- O _header_ `admin_user_id` na documentação está indicado com valor `1` mas pode ser usado o id de qualquer outro usuário caso deseje (_pesquisando no banco de dados é possível ver os outros id's de usuários_).

Caso opte por usar o [Insomnia](https://insomnia.rest/) o arquivo para importação se encontra em `docs/insomnia-api.json`.
Caso opte por usar o [Postman](https://www.postman.com/) o arquivo para importação se encontra em `docs/postman-api.json`.

## Sobre o Banco de Dados

- O banco de dados é um _sqlite_ simples e já vem com dados preenchidos por padrão no projeto;
- O banco tem um arquivo de backup em `db/db-backup.sqlite` com o estado inicial do projeto caso precise ser "resetado".

## Demandas

Abaixo, as solicitações do cliente:

### Categorias

- [x] A categoria está vindo errada na listagem de produtos para alguns casos
      (_exemplo: produto `blue trouser` está vindo na categoria `phone` e deveria ser `clothing`_);
- [x] Alguns produtos estão vindo com a categoria `null` ao serem pesquisados individualmente (_exemplo: produto `iphone 8`_);
- [x] Cadastrei o produto `king size bed` em mais de uma categoria, mas ele aparece **apenas** na categoria `furniture` na busca individual do produto.

### Filtros e Ordenamento

Para a listagem de produtos:

- [x] Gostaria de poder filtrar os produtos ativos e inativos;
- [x] Gostaria de poder filtrar os produtos por categoria;
- [x] Gostaria de poder ordenar os produtos por data de cadastro.

### Relatório

- [x] O relatório não está mostrando a coluna de logs corretamente, se possível, gostaria de trazer no seguinte formato:
      (Nome do usuário, Tipo de alteração e Data),
      (Nome do usuário, Tipo de alteração e Data),
      (Nome do usuário, Tipo de alteração e Data)
      Exemplo:
      (John Doe, Criação, 01/12/2023 12:50:30),
      (Jane Doe, Atualização, 11/12/2023 13:51:40),
      (Joe Doe, Remoção, 21/12/2023 14:52:50)

### Logs

- [x] Gostaria de saber qual usuário mudou o preço do produto `iphone 8` por último.

### Extra

- [x] Aqui fica um desafio extra **opcional**: _criar um ambiente com_ Docker _para a api_.

**Seu trabalho é atender às 7 demandas solicitadas pelo cliente.**

Caso julgue necessário, podem ser adicionadas ou modificadas as rotas da api. Caso altere, por favor, explique o porquê e indique as alterações nesse `README`.

Sinta-se a vontade para refatorar o que achar pertinente, considerando questões como arquitetura, padrões de código, padrões restful, _segurança_ e quaisquer outras boas práticas. Levaremos em conta essas mudanças.

Boa sorte! :)

## Suas Respostas, Duvidas e Observações

### Respostas

#### Categorias

- Correção na Listagem de Categorias:
  Após analisar minuciosamente o código, identifiquei que o motivo para a categoria ser exibida incorretamente na listagem estava relacionado a um problema na query SQL do método getAll da Service. Realizei os ajustes necessários, garantindo que a rota retorne o resultado esperado.
- Correção na Recuperação Individual de Categoria:
  Seguindo a mesma linha de raciocínio do primeiro tópico, corrigi a query SQL do método getOne da Service, solucionando o problema na recuperação individual de categoria.
- Busca Individual de Produto com Mais de Uma Categoria:
  Este tópico apresentou desafios adicionais. Por um longo período, suspeitei que o problema estivesse na query, mas, na realidade, residia na lógica do controller getOne. Após uma análise detalhada, ajustei a lógica para retornar o resultado desejado. Optei por retornar dois objetos semelhantes, diferindo apenas na categoria, uma escolha que destaca melhor as duas categorias distintas.

#### Filtros e Ordenamento

-Refatoração para Query Strings:
Inicialmente, considerei dividir o código em rotas e consultas SQL separadas para cada filtro e ordenador. Contudo, ao avançar para as próximas tarefas, percebi que não fazia sentido ter tantas rotas e consultas desnecessárias. Optei por utilizar query strings como input para filtrar e ordenar, permitindo a manipulação de diferentes itens simultaneamente. Criei uma função específica para aplicar todos os filtros, evitando um código muito extenso, e a aloquei em um arquivo separado dentro de uma nova pasta chamada "utils".
Para utilizar a rota com query strings, será necessário acessar da seguinte maneira: http://localhost:8000/products?category=(categoria do produto)&order=(ordem de exibição, como crescente ou decrescente usando asc ou desc)&status=(status do produto, onde "active" trará todos os produtos ativos e diferente de "active" trará todos os inativos).

#### Relatório

- Atualização na Query de Logs e Tratamento no Controller:
  Nesta tarefa, foi necessário ajustar a query de logs para incluir o nome do usuário responsável pela alteração. Após essa modificação, concentrei-me no controller de relatórios para processar os novos dados recebidos e exibir corretamente a coluna de logs.

#### Extra

- Implementação do Ambiente Docker:
  Diante das dificuldades enfrentadas na tarefa dos logs, decidi avançar para a questão extra, que consistia em criar um ambiente Docker. Inicialmente, enfrentei desafios consideráveis, mas após extensa pesquisa, consegui estabelecer um ambiente totalmente funcional. Entretanto, migrei todo o projeto para uma pasta chamada "app", impactando a apresentação do repositório Git.

#### Logs

- Filtro dos Logs
  Neste tópico, optei por verificar quais campos estavam sendo alterados ao atualizar um produto e assim enviar essa informação para a tabela de "product_logs". Dessa maneira, fui capaz de filtrar quem foi a última pessoa a alterar o preço (ou qualquer outro campo) de um produto. A forma de aplicar esse filtro foi por meio de query strings.
  Para utilizar o filtro, é necessário acessar a URL da seguinte maneira:
  http://localhost:8000/report?action=(ação para verificar a última alteração, por exemplo: update+price)&product=(produto para verificar a última alteração, por exemplo: iphone+8)
  Os espaços entre os itens a serem filtrados devem ser representados pelo sinal de "+".
