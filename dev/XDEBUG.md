# Xdebug Configuration for Development

## IDE Configuration

### PhpStorm
#### 1. Verifique se todos o Docker está funcionando corretamente no WSL2.

```bash
docker-compose up -d
docker ps
```

#### 2. Configure um servidor
1. Vá em `File` > `Settings` > `PHP` > `Servers`
2. Adicione um novo servidor:
- Name: **Movement Ranking API**
- Host: **localhost**
- Port: **8080** (a porta mapeada do Nginx)
- Debugger: **Xdebug**
3. Marque *"Use path mappings"*.
4. No "Path Mappings" abaixo:
- Mapeie a raiz do projeto no container do serviço "php" (`/var/www`) para o caminho local no WSL2 (algo como \\wsl$\Ubuntu\caminho\para\seu\projeto\src).

#### 2. PHPStorm : Configurar o interpretador PHP

1. Abra o PHPStorm e seu projeto
2. Vá em File > Settings > PHP
3. Clique em ```...``` ao lado de "CLI Interpreter"
4. Clique em ```+``` e selecione "From Docker, Vagrant..." e escolha "Docker Compose".
5. Em Docker Composer:
- Server: **Docker**
- Service: **php**
6. Click em "Ok" e aguarde.
7. Nomeie o interpretando "Movement Ranking PHP8".

> PHPStorm detectará automaticamente a versão do PHP

#### 3. Configurar o Xdebug
1. Vá em File > Settings > PHP > Debug
2. Em "Pre-configuration":
- Verifique se mostra "Debugger: Xdebug 3.x" (de acordo com sua versão)
3. External connections:
- Marque "Ignore external connections through unregistered server configurations"
- Desmarque "Break at first line in PHP scripts"
4. Na seção Xdebug:
- Debug port: **9003** (deve bater com a configuração no Docker)
- Marque "Can accept external connections".

#### 4. Configuração do Run/Debug
1. Crie uma nova configuração de Run:
- Run > Edit Configurations...
- Clique em + e selecione "PHP Web Page"
2. Configure:
- Name: **Movement Ranking API**
- Server: **Movement Ranking API** (o que você criou antes)
- Start URL: **/** (ou qualquer endpoint para testar)

#### 5. Testando a Configuração
1. Coloque um breakpoint em algum arquivo PHP (ex: `src/app/Controllers/RankingController.php`)
2. Inicie a sessão de debug com o ícone de "Start Listening for PHP Debug Connections"
3. Acesse no navegador: `http://localhost:8080/movements/1/ranking`
4. O PHPStorm deve parar no breakpoint

### VS Code
1. Install PHP Debug extension
2. Create .vscode/launch.json:
```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www": "${workspaceFolder}/src"
            }
        }
    ]
}
```

## Environment Variables

The following Xdebug environment variables are configured:

- `XDEBUG_MODE=debug` - Enable debugging mode
- `XDEBUG_CONFIG=client_host=host.docker.internal client_port=9003` - Client configuration

## Xdebug Settings

The following Xdebug settings are configured in the PHP container:

```ini
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
xdebug.discover_client_host=1
xdebug.idekey=PHPSTORM
```

## Usage

1. Start the containers: `docker compose up -d`
2. Set breakpoints in your IDE
3. Start listening for debug connections in your IDE
4. Make a request to the API: `curl http://localhost:8080/movements/1/ranking`
5. The debugger should stop at your breakpoints

## Troubleshooting

- Ensure port 9003 is not blocked by firewall
- Check that your IDE is listening on port 9003
- Verify path mappings are correct
- For Windows/Mac, `host.docker.internal` should work automatically
- For Linux, you may need to use `host.docker.internal` or the host IP address

