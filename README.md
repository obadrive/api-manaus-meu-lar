<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# 🏙️ API Portal Cidadão Manaus

API RESTful para o super app "Portal Cidadão Manaus" - uma plataforma completa de serviços municipais, gamificação e engajamento cívico.

## 🚀 Tecnologias

- **Backend**: Laravel 11 (PHP 8.2+)
- **Banco de Dados**: PostgreSQL 15+ com PostGIS
- **Autenticação**: Laravel Sanctum
- **Cache**: Redis (opcional)
- **Documentação**: OpenAPI/Swagger (futuro)

## 📋 Pré-requisitos

- PHP 8.2 ou superior
- Composer 2.0+
- PostgreSQL 15+ com extensão PostGIS
- Node.js 18+ (para frontend)

## 🛠️ Instalação

1. **Clone o repositório**
```bash
git clone https://github.com/seu-usuario/portal-cidadao-manaus.git
cd portal-cidadao-manaus
```

2. **Instale as dependências**
```bash
composer install
```

3. **Configure o ambiente**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure o banco de dados**
```bash
# Edite o .env com suas credenciais do PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=portal_manaus
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

5. **Execute as migrações**
```bash
php artisan migrate
```

6. **Inicie o servidor**
```bash
php artisan serve
```

## 🔐 Autenticação

A API utiliza **Laravel Sanctum** para autenticação via tokens. Todos os endpoints (exceto registro e login) requerem autenticação.

### Endpoints de Autenticação

#### 🔑 Registro de Usuário
```http
POST /api/auth/register
```

**Body:**
```json
{
    "nome": "João Silva",
    "email": "joao@exemplo.com",
    "senha": "123456",
    "role": "morador"
}
```

**Resposta:**
```json
{
    "success": true,
    "message": "Usuário registrado com sucesso",
    "data": {
        "usuario": {
            "id": "uuid",
            "nome": "João Silva",
            "email": "joao@exemplo.com",
            "role": "morador"
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

#### 🔐 Login
```http
POST /api/auth/login
```

**Body:**
```json
{
    "email": "joao@exemplo.com",
    "senha": "123456"
}
```

**Resposta:**
```json
{
    "success": true,
    "message": "Login realizado com sucesso",
    "data": {
        "usuario": {
            "id": "uuid",
            "nome": "João Silva",
            "email": "joao@exemplo.com",
            "role": "morador"
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

#### 👤 Obter Dados do Usuário
```http
GET /api/auth/me
Authorization: Bearer {token}
```

#### 🔄 Atualizar Perfil
```http
PATCH /api/auth/profile
Authorization: Bearer {token}
```

**Body:**
```json
{
    "nome": "João Silva Atualizado",
    "email": "joao.novo@exemplo.com"
}
```

#### 🔒 Alterar Senha
```http
PATCH /api/auth/change-password
Authorization: Bearer {token}
```

**Body:**
```json
{
    "senha_atual": "123456",
    "nova_senha": "654321",
    "nova_senha_confirmation": "654321"
}
```

#### 🚪 Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

#### 🚪 Logout de Todos os Dispositivos
```http
POST /api/auth/logout-all
Authorization: Bearer {token}
```

### Roles de Usuário

- **`morador`**: Cidadão comum
- **`comerciante`**: Comerciante local
- **`admin`**: Administrador do sistema

### Uso do Token

Para acessar endpoints protegidos, inclua o token no header:

```http
Authorization: Bearer {seu_token_aqui}
```

### 🔒 Proteção por Roles

Alguns endpoints são protegidos por roles específicos:

#### Endpoints Admin (role: admin)
- `GET /api/usuarios` - Listar usuários
- `POST /api/usuarios` - Criar usuário
- `PATCH /api/postagens/{id}/fixar` - Fixar postagem
- `PATCH /api/postagens/{id}/aprovar` - Aprovar postagem
- `PATCH /api/postagens/{id}/rejeitar` - Rejeitar postagem

#### Endpoints Públicos (autenticados)
- `GET /api/bairros` - Listar bairros
- `GET /api/eventos` - Listar eventos
- `GET /api/postagens` - Listar postagens
- `GET /api/servicos` - Listar serviços

#### Exemplo de Acesso Negado
```json
{
    "success": false,
    "message": "Acesso negado. Role necessário: admin"
}
```

## 📊 Estrutura do Banco de Dados

### Tabelas Principais

- **usuarios**: Usuários do sistema (moradores, comerciantes, admins)
- **bairros**: Bairros de Manaus com geometria
- **pontos_interesse**: Pontos de interesse no mapa
- **eventos**: Eventos comunitários e oficiais
- **anuncios**: Anúncios do marketplace
- **postagens**: Postagens do feed social
- **servicos**: Serviços municipais
- **gamificacao**: Sistema de gamificação
- **notificacoes**: Sistema de notificações

### Tabelas Auxiliares

- **imagens**: Imagens polimórficas
- **avaliacoes**: Avaliações polimórficas
- **comentarios**: Comentários polimórficos
- **conquistas**: Conquistas do sistema
- **missoes**: Missões do sistema

## 📡 Endpoints da API

### Base URL
```
http://localhost:8000/api
```

### 🔐 Autenticação
*Preparado para implementação com Laravel Sanctum*

### 📍 Bairros
```http
GET    /api/bairros                    # Listar bairros
POST   /api/bairros                    # Criar bairro
GET    /api/bairros/{id}               # Buscar bairro
PATCH  /api/bairros/{id}               # Atualizar bairro
DELETE /api/bairros/{id}               # Remover bairro
PATCH  /api/bairros/{id}/restore       # Restaurar bairro
GET    /api/bairros/{id}/proximos      # Bairros próximos
GET    /api/bairros/{id}/estatisticas  # Estatísticas do bairro
```

### 👥 Usuários
```http
GET    /api/usuarios                   # Listar usuários
POST   /api/usuarios                   # Criar usuário
GET    /api/usuarios/{id}              # Buscar usuário
PATCH  /api/usuarios/{id}              # Atualizar usuário
DELETE /api/usuarios/{id}              # Remover usuário
PATCH  /api/usuarios/{id}/restore      # Restaurar usuário
GET    /api/usuarios/estatisticas      # Estatísticas
GET    /api/usuarios/proximos          # Usuários próximos
```

### 🗺️ Pontos de Interesse
```http
GET    /api/pontos-interesse           # Listar pontos
POST   /api/pontos-interesse           # Criar ponto
GET    /api/pontos-interesse/{id}      # Buscar ponto
PATCH  /api/pontos-interesse/{id}      # Atualizar ponto
DELETE /api/pontos-interesse/{id}      # Remover ponto
GET    /api/pontos-interesse/proximos  # Pontos próximos
GET    /api/pontos-interesse/categorias # Categorias
GET    /api/pontos-interesse/estatisticas # Estatísticas
```

### 🎉 Eventos
```http
GET    /api/eventos                    # Listar eventos
POST   /api/eventos                    # Criar evento
GET    /api/eventos/{id}               # Buscar evento
PATCH  /api/eventos/{id}               # Atualizar evento
DELETE /api/eventos/{id}               # Remover evento
GET    /api/eventos/proximos           # Eventos próximos
GET    /api/eventos/categorias         # Categorias
GET    /api/eventos/estatisticas       # Estatísticas
```

### 🛍️ Anúncios
```http
GET    /api/anuncios                   # Listar anúncios
POST   /api/anuncios                   # Criar anúncio
GET    /api/anuncios/{id}              # Buscar anúncio
PATCH  /api/anuncios/{id}              # Atualizar anúncio
DELETE /api/anuncios/{id}              # Remover anúncio
GET    /api/anuncios/proximos          # Anúncios próximos
GET    /api/anuncios/categorias        # Categorias
GET    /api/anuncios/estatisticas      # Estatísticas
```

### 📱 Postagens
```http
GET    /api/postagens                  # Listar postagens
POST   /api/postagens                  # Criar postagem
GET    /api/postagens/{id}             # Buscar postagem
PATCH  /api/postagens/{id}             # Atualizar postagem
DELETE /api/postagens/{id}             # Remover postagem
PATCH  /api/postagens/{id}/fixar       # Fixar postagem
PATCH  /api/postagens/{id}/desfixar    # Desfixar postagem
PATCH  /api/postagens/{id}/aprovar     # Aprovar postagem
PATCH  /api/postagens/{id}/rejeitar    # Rejeitar postagem
GET    /api/postagens/proximas         # Postagens próximas
GET    /api/postagens/estatisticas     # Estatísticas
```

### 🏛️ Serviços
```http
GET    /api/servicos                   # Listar serviços
POST   /api/servicos                   # Criar serviço
GET    /api/servicos/{id}              # Buscar serviço
PATCH  /api/servicos/{id}              # Atualizar serviço
DELETE /api/servicos/{id}              # Remover serviço
GET    /api/servicos/proximos          # Serviços próximos
GET    /api/servicos/categorias        # Categorias
GET    /api/servicos/estatisticas      # Estatísticas
```

### 🎮 Gamificação
```http
GET    /api/gamificacao                # Listar gamificações
POST   /api/gamificacao                # Criar gamificação
GET    /api/gamificacao/{id}           # Buscar gamificação
PATCH  /api/gamificacao/{id}           # Atualizar gamificação
DELETE /api/gamificacao/{id}           # Remover gamificação
POST   /api/gamificacao/{id}/adicionar-xp      # Adicionar XP
POST   /api/gamificacao/{id}/adicionar-gocoins # Adicionar GoCoins
GET    /api/gamificacao/estatisticas   # Estatísticas
```

### 🔔 Notificações
```http
GET    /api/notificacoes               # Listar notificações
POST   /api/notificacoes               # Criar notificação
GET    /api/notificacoes/{id}          # Buscar notificação
PATCH  /api/notificacoes/{id}          # Atualizar notificação
DELETE /api/notificacoes/{id}          # Remover notificação
PATCH  /api/notificacoes/{id}/marcar-lida      # Marcar como lida
PATCH  /api/notificacoes/marcar-todas-lidas    # Marcar todas como lidas
GET    /api/notificacoes/nao-lidas     # Notificações não lidas
GET    /api/notificacoes/estatisticas  # Estatísticas
```

## 📊 Parâmetros de Consulta

### Filtros Comuns
- `per_page`: Itens por página (padrão: 15)
- `ativo`: Filtrar por status ativo (true/false)
- `aprovado`: Filtrar por status aprovado (true/false)
- `categoria`: Filtrar por categoria
- `tipo`: Filtrar por tipo

### Geolocalização
- `lat`: Latitude
- `lng`: Longitude
- `raio`: Raio de busca em metros (padrão: 5000)

### Exemplo de Uso
```http
GET /api/pontos-interesse?lat=-3.1190&lng=-60.0217&raio=3000&categoria=restaurante&ativo=true&per_page=20
```

## 🔧 Funcionalidades Especiais

### 🗺️ Operações Geoespaciais
- Busca por proximidade usando PostGIS
- Cálculo de distâncias
- Filtros por região

### 📈 Estatísticas
- Contadores por categoria
- Médias e totais
- Rankings e top performers

### 🎮 Gamificação
- Sistema de XP e níveis
- GoCoins como moeda virtual
- Conquistas e missões

### 🔔 Notificações
- Sistema de notificações em tempo real
- Marcação de leitura
- Filtros por tipo e categoria

## 📝 Exemplos de Requisições

### Criar um Usuário
```bash
curl -X POST http://localhost:8000/api/usuarios \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "João Silva",
    "email": "joao@email.com",
    "senha": "123456",
    "telefone": "(92) 99999-9999",
    "tipo_usuario": "morador",
    "bairro_id": "uuid-do-bairro",
    "geometria": {
      "type": "Point",
      "coordinates": [-60.0217, -3.1190]
    }
  }'
```

### Buscar Pontos Próximos
```bash
curl "http://localhost:8000/api/pontos-interesse/proximos?lat=-3.1190&lng=-60.0217&raio=2000&categoria=restaurante"
```

### Adicionar XP
```bash
curl -X POST http://localhost:8000/api/gamificacao/{id}/adicionar-xp \
  -H "Content-Type: application/json" \
  -d '{
    "xp": 100
  }'
```

## 🚨 Códigos de Resposta

- `200`: Sucesso
- `201`: Criado com sucesso
- `400`: Requisição inválida
- `404`: Não encontrado
- `422`: Dados de validação inválidos
- `500`: Erro interno do servidor

## 📋 Estrutura de Resposta

### Sucesso
```json
{
  "success": true,
  "data": {...},
  "message": "Operação realizada com sucesso"
}
```

### Erro
```json
{
  "success": false,
  "message": "Mensagem de erro",
  "error": "Detalhes do erro"
}
```

### Paginação
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

## 🔒 Segurança

- Validação de dados em todas as requisições
- Sanitização de inputs
- Proteção contra SQL injection
- Soft deletes para preservação de dados
- Logs de auditoria (preparado)

## 🧪 Testes

```bash
# Executar testes
php artisan test

# Executar testes específicos
php artisan test --filter=BairroTest
```

## 📚 Documentação Adicional

- [Laravel Documentation](https://laravel.com/docs)
- [PostGIS Documentation](https://postgis.net/documentation/)
- [GeoJSON Specification](https://geojson.org/)

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 📞 Suporte

Para suporte, envie um email para suporte@portalmanaus.com.br ou abra uma issue no repositório.
