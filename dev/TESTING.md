# Instruções para Teste Local (Sem Docker)

Como o ambiente sandbox tem limitações com Docker, aqui estão as instruções para testar localmente:

## Opção 1: Teste com PHP Built-in Server

1. **Instalar dependências**:
```bash
composer install
```

2. **Configurar banco SQLite para teste**:
```bash
# Criar banco SQLite
sqlite3 test.db < sql/init_sqlite.sql
```

3. **Iniciar servidor PHP**:
```bash
cd src
php -S 0.0.0.0:8080
```

4. **Testar endpoints**:
```bash
curl http://localhost:8080/health
curl http://localhost:8080/movements/1/ranking
```

## Opção 2: Teste com Docker (Ambiente com Docker funcional)

1. **Iniciar containers**:
```bash
docker compose up -d
```

2. **Instalar dependências**:
```bash
docker compose exec php composer install
```

3. **Testar API**:
```bash
curl http://localhost:8080/health
curl http://localhost:8080/movements/1/ranking
```

## Executar Testes

```bash
# Testes unitários
composer test

# Testes com cobertura
composer test-coverage
```

## Estrutura de Resposta da API

### Sucesso (200):
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
            }
        ]
    },
    "timestamp": "2025-06-11T10:30:00+00:00"
}
```

### Erro (404):
```json
{
    "success": false,
    "error": {
        "code": 404,
        "message": "Movement not found"
    },
    "timestamp": "2025-06-11T10:30:00+00:00"
}
```

