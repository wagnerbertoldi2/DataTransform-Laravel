# Teste Técnico – Desenvolvedor PHP Laravel

## Objetivo

Desenvolver uma aplicação backend responsável pelo processamento, transformação e sincronização de dados de produtos e preços, utilizando Views SQL para padronização das informações e disponibilizando os dados por meio de uma API REST.

## Requisitos Técnicos

**Tecnologias obrigatórias:**

- PHP 8.0+
- Laravel 11.0+
- SQLite
- Docker
- Docker Compose

### Restrições Obrigatórias

**O projeto deve:**

- Rodar integralmente via Docker.
- Possuir arquivo `docker-compose.yml`.
- Expor exclusivamente endpoints de API REST.
- Conter testes automatizados.
- Incluir instruções de execução no `README.md`.
- Documentar os endpoints disponíveis.

**O projeto não deve:**

- Exigir instalação de dependências na máquina host além do Docker.
- Conter qualquer tipo de interface web.

## Modelagem de Banco de Dados

### Tabelas de Origem

Devem ser criadas duas tabelas base:

- `produtos_base`
- `precos_base`

O script de criação das tabelas base encontra-se na raiz do projeto.

### Tabelas de Destino

Devem ser criadas duas tabelas para armazenamento dos dados processados:

- `produto_insercao`
- `preco_insercao`

Considere modelagem adequada, chaves e índices quando necessário.

## Processamento com Views SQL

A transformação dos dados deve ser realizada obrigatoriamente por meio de Views SQL.

Devem ser criadas:

- Uma View para produtos.
- Uma View para preços.

As Views devem contemplar:

- Normalização dos dados.
- Processamento apenas de registros ativos.

## Processo de Sincronização

A sincronização deve:

- Consumir os dados a partir das Views.
- Inserir, atualizar ou remover registros nas tabelas de destino.
- Evitar duplicidade.
- Evitar operações desnecessárias.

## API REST

A aplicação deve disponibilizar os seguintes endpoints:

### Sincronizar Produtos

`POST /api/sincronizar/produtos`

Executa o processo de transformação e sincronização dos dados de `produtos_base` para `produto_insercao`.

### Sincronizar Preços

`POST /api/sincronizar/precos`

Executa o processo de transformação e sincronização dos dados de `precos_base` para `preco_insercao`.

### Listar Produtos Sincronizados (Paginado)

`GET /api/produtos-precos`

Deve retornar os produtos processados com seus respectivos preços de forma paginada. A paginação deve aceitar parâmetros de controle via query string.

## Como executar o projeto?

### Pré-requisitos

- Docker e Docker Compose instalados na máquina.

### Instalação

```bash
git clone https://github.com/wagnerbertoldi2/DataTransform-Laravel.git
cd DataTransform-Laravel
./setup.sh
```

O script `setup.sh` cuida de todo o processo automaticamente:

1. Verifica se o Docker está instalado e rodando.
2. Remove containers anteriores do projeto, se existirem.
3. Constrói a imagem Docker e inicia o container.
4. Aguarda a inicialização completa (instalação de dependências via Composer, geração da `APP_KEY`, execução das migrations e seed dos dados base).

Ao finalizar, a API estará disponível em `http://localhost:8000`.

### Executando os testes

```bash
docker compose exec app php artisan test
```

### Parando o ambiente

```bash
docker compose down
```

### Uso dos endpoints

Uma collection para o Postman está disponível na raiz do projeto: `DataTransform_API.postman_collection.json`.

Após a instalação, os dados base já estarão carregados no banco. Para sincronizar:

```bash
# 1. Sincronizar produtos (executar primeiro)
curl -X POST 'http://localhost:8000/api/sincronizar/produtos'

# 2. Sincronizar preços
curl -X POST 'http://localhost:8000/api/sincronizar/precos'

# 3. Consultar produtos com preços (paginado)
curl 'http://localhost:8000/api/produtos-precos?per_page=5&page=1'
```
