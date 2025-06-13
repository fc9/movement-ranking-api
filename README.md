# Movement Ranking API

RESTful API em PHP puro para sistema de ranking de movimentos para teste técnico na **Tecnofit**.

## Características

- **Arquitetura Monolítica**: Separação clara de camadas (Controllers, Services, Repositories)
- **PHP Puro**: Sem frameworks, usando apenas bibliotecas essenciais (Eloquent ORM)
- **Nginx + PHP-FPM**: Servidor web otimizado para alta performance
- **Docker**: Ambiente containerizado com MySQL 8, Nginx e PHP-FPM
- **Xdebug**: Suporte completo para debugging
- **Testes Unitários**: Cobertura completa com PHPUnit
- **Segurança**: Validação, sanitização, rate limiting e headers de segurança
- **Performance**: Cache, queries otimizadas e índices de banco
- **RESTful**: Endpoints padronizados com respostas JSON

## Estrutura do Projeto

```
movement-ranking-api/
├── dev/
│   └── postman-collection.json # Coleção para teste no Postman
│   └── BACKLOG.md              # Backlog de desenvolvimento
│   └── TESTING.md              # Documentação do Test
│   └── XDEBUG.md               # Documentação do Xdebug
├── docker/
│   ├── logs/              # Volumes para logs
│   ├── Dockerfile.mysql   # Container MySql
│   ├── Dockerfile.nginx   # Container Nginx
│   ├── Dockerfile.php     # Container PHP-FPM
│   ├── nginx.conf         # Configuração do Nginx
├── sql/
│   ├── init.sql           # Script de inicialização MySQL
│   └── init_sqlite.sql    # Script de inicialização SQLite
├── src/
│   ├── app/
│   │   ├── Config/        # Configurações (Database, Router)
│   │   ├── Controllers/   # Camada de controle
│   │   ├── Models/        # Modelos/Entidades
│   │   ├── Providers/     # Camada de Providers (funcionalidades transversais)
│   │   ├── Repositories/  # Camada de repositórios (acesso a dados)
│   │   ├── Services/      # Camada de serviços (lógica de negócio)
│   │   └── Utils/         # Utilitários (Cache, Validator)
│   ├── public/
│   │   └── index.php      # Ponto de entrada da aplicação
│   ├── tests/
│   │   ├── Unit/          # Testes unitários
│   │   └── Integration/   # Testes de integração
│   ├── composer.json      # Dependências PHP
│   ├── phpunit.xml        # Configuração dos testes
│   └── .env.example       # Configurações de exemplo
├── docker-compose.yml     # Orquestração dos containers
└── README.md              # Documentação principal
```

## Requisitos

- Docker
- Docker Compose

## Instalação e Execução

1. **Clone o projeto**:
```bash
git clone <repository-url>
cd movement-ranking-api
```

2. **Inicie os containers**:
```bash
docker compose up -d
```

3. **Instale as dependências**:
```bash
docker compose exec php composer install
```

4. **Aguarde a inicialização do banco** (pode levar alguns segundos)

5. **Teste a API**:
```bash
curl http://localhost:8080/health
```

## Endpoints da API

### GET /movements/{identifier}/ranking

Retorna o ranking de um movimento específico.

**Parâmetros:**
- `identifier`: ID numérico ou nome do movimento

**Exemplo de Requisição:**
```bash
# Por ID
curl http://localhost:8080/movements/1/ranking

# Por nome
curl http://localhost:8080/movements/Deadlift/ranking
```

**Exemplo de Resposta:**
```json
{
    "success": true,
    "data": {
        "movement": {
            "id": 1,
            "name": "Deadlift"
        },
        "ranking": [
            {
                "user_id": 2,
                "user_name": "Jose",
                "personal_record": 190.0,
                "personal_record_date": "2021-01-06 00:00:00",
                "ranking_position": 1
            },
            {
                "user_id": 1,
                "user_name": "Joao",
                "personal_record": 180.0,
                "personal_record_date": "2021-01-02 00:00:00",
                "ranking_position": 2
            }
        ]
    },
    "timestamp": "2025-06-11T10:30:00+00:00"
}
```

### GET /health

Endpoint de verificação de saúde da API.

### GET /

Documentação básica da API.

## Arquitetura

### Nginx + PHP-FPM

O projeto utiliza Nginx como servidor web e PHP-FPM para processamento PHP, proporcionando:

- **Alta Performance**: Nginx otimizado para servir conteúdo estático e proxy reverso
- **Escalabilidade**: PHP-FPM com pool de processos configurável
- **Segurança**: Headers de segurança configurados no Nginx
- **Cache**: Cache de arquivos estáticos configurado

### Containers Docker

- **nginx**: Servidor web (porta 8080)
- **php**: PHP-FPM com Xdebug (porta 9003 para debug)
- **mysql**: Banco de dados MySQL 8 (porta 3306)

## Debugging com Xdebug

O projeto inclui suporte completo ao Xdebug 3.x:

- **Porta**: 9003 (padrão do Xdebug 3.x)
- **Configuração**: Automática via Docker
- **IDEs Suportadas**: PhpStorm, VS Code, outros

Consulte `docker/XDEBUG.md` para instruções detalhadas de configuração.

## Regras de Negócio

- **Ranking por Recorde Pessoal**: Usuários são ranqueados pelo maior valor registrado
- **Empates**: Usuários com mesmo valor compartilham a mesma posição
- **Ordenação**: Decrescente por valor, crescente por data em caso de empate
- **Data do Recorde**: Retorna a data do registro que estabeleceu o recorde pessoal

## Testes

### Executar Testes Unitários

```bash
docker compose exec php composer test
```

### Executar Testes com Cobertura

```bash
docker compose exec php composer test-coverage
```

### Estrutura dos Testes

- **Unit Tests**: Testam componentes isoladamente com mocks
- **Integration Tests**: Testam fluxo completo com banco de dados
- **Cobertura**: Relatórios HTML e texto disponíveis

## Segurança

- **Validação de Entrada**: Sanitização e validação de todos os parâmetros
- **Proteção SQL Injection**: Queries preparadas e validação de padrões
- **Rate Limiting**: Limite de 100 requisições por hora por IP
- **Headers de Segurança**: CSP, XSS Protection, CSRF Protection
- **CORS**: Configurado para permitir requisições cross-origin

## Performance

- **Cache**: Sistema de cache em arquivo com TTL configurável
- **Queries Otimizadas**: Uso de índices e queries eficientes
- **Nginx**: Servidor web otimizado para alta performance
- **PHP-FPM**: Pool de processos configurável
- **Compressão**: Gzip habilitado no Nginx

## Monitoramento

- **Logs**: Registro de erros e atividades
- **Health Check**: Endpoint para verificação de status
- **Métricas**: Tempo de resposta e uso de recursos

## Desenvolvimento

### Estrutura de Camadas

1. **Controllers**: Recebem requisições HTTP e retornam respostas
2. **Services**: Contêm lógica de negócio e validações
3. **Repositories**: Abstraem acesso aos dados
4. **Models**: Representam entidades do banco de dados

### Padrões Utilizados

- **Repository Pattern**: Abstração do acesso a dados
- **Dependency Injection**: Injeção de dependências com PHP-DI
- **PSR-4**: Autoloading de classes
- **RESTful**: Padrões REST para endpoints

### Namespaces

O projeto utiliza o namespace `App\` como raiz:

```php
App\Controllers\RankingController
App\Services\RankingService
App\Repositories\MovementRepository
App\Models\Movement
App\Config\Database
App\Utils\Cache
```

## Configuração

### Variáveis de Ambiente

Copie `.env.example` para `.env` e ajuste conforme necessário:

```bash
cp src/.env.example src/.env
```

### Banco de Dados

O banco é inicializado automaticamente com:
- Estrutura das tabelas
- Dados de exemplo
- Índices otimizados

## Troubleshooting

### Problemas Comuns

1. **Erro de conexão com banco**: Aguarde alguns segundos para inicialização
2. **Permissões**: Verifique se o Docker tem permissões adequadas
3. **Porta ocupada**: Altere a porta no docker-compose.yml se necessário
4. **Xdebug não conecta**: Verifique configuração da IDE e firewall

### Logs

```bash
# Logs da aplicação
docker compose logs php

# Logs do Nginx
docker compose logs nginx

# Logs do banco
docker compose logs mysql
```

## Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature
3. Implemente com testes
4. Submeta um Pull Request

## Licença

Este projeto está sob licença MIT.

