# BackLog

Geral:
- [x] Estrutura de pastas.
- [x] Docker, serviços e containers.
- [x] Otimizar esquema de banco de dados.
- [x] Funcionalidades básicas.
- [x] Refactor (Clean code, SOLID...).
- [x] Validação e sanitização de entrada.
- [x] Suporte básico a testes automátizados (Testes unitários com PHPUnit). 
- [x] Teste(s) de integração.
- [x] Criar mocks para isolamento.
- [x] Ajustar casos de erro.
- [x] Corrigir e padronizar PSR4 dos arquivos (Type-hinting).
- [x] Mudar pasta raiz para /var/www/public.
- [x] Mudar pasta de tests.
- [x] Mudar de Apache para Nginx.
- [x] Suporte a XDebug.
- [x] Suporte básico a Cache (TTL). 
- [x] Segurança básica headers (CSP, XSS Protection). 
- [x] Suporte básico a SQL Injection.
- [x] Otimização (índices...).
- [x] Suporte básico a rate limiting (100req/hora por IP). 
- [x] Providers para mover rate limiting e outras funcionalidades transversais para uma cama dedicada e extensível.
- [x] Serviço/util para http status codes.
- [x] Criar BaseController para reutilização de código.
- [x] Centralizar getClientIp para reutilização.
- [x] Criar arquivo de rotas para expansão.
- [x] Simplificar dependency injection para controllers.
- [ ] Testes para providers.
- [x] Suporte a rotas POST.
- [x] Trocar DependencyInjector para PHP-DI.
- [x] Criar pipeline para Code Review (usar AI).
- [x] Refactor pós CR.
- [x] Adicionar configuração de logs ao Dockerfile.php.
- [x] Criar volumes para logs no docker-compose.yml.
- [x] Arquivos de rotas para Postman (e/ou Insomnia).
- [x] Criar arquivo(s) Markdown (usar AI).
- [x] Repositório no GitHub.
- [ ] Testar instalação limpa.
- [ ] Revisar README.

Bugs:
- [x] Redirect Apache inconsistente.
> Corrigido migrando para Nginx.
- [x] Conexão ao banco de dados.
- [x] SQLSTATE[42000] [1231] Variable 'sql_mode' can't be set to the value of 'NO_AUTO_CREATE_USER' (sql_mode).
- [x] SQLSTATE[42000] [1064] You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near.
- [x] There was 1 PHPUnit test runner warning: XDEBUG_MODE=coverage (environment variable) or xdebug.mode=coverage (PHP configuration setting) has to be set. (docker-compose.yml).
- [x] Permissão de pastas para Cache.
- [x] Ranking vazio.
- [x] Sistema de logs (AI).
- [x] ini.sql inconsistente no Docker.
- [ ]


