# 📚 Class Up API

> **Sistema de Gestão Educacional Completo** com suporte avançado a pagamentos, inscrições e relatórios financeiros.

<div align="center">

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-00758F?style=flat-square&logo=mysql)](https://www.mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](LICENSE)

[Documentação](#-documentação-da-api) • [Instalação](#-instalação-rápida) • [Postman](#-importar-postman) • [Suporte](#-suporte)

</div>

---

## ✨ Características

### 🏫 **Gestão Escolar Completa**
- ✅ Gerenciamento de turmas, salas de aula e séries
- ✅ Cadastro e acompanhamento de alunos
- ✅ Gestão de responsáveis/guardiões
- ✅ Controle de frequência e presença em tempo real
- ✅ Lançamento de notas e avaliações
- ✅ Geração de certificados digitais customizados

### 👨‍🏫 **Gestão de Instrutores**
- ✅ Cadastro completo de professores/instrutores
- ✅ Atribuição de turmas e disciplinas
- ✅ Controle de cargas horárias
- ✅ Rastreamento de qualificações

### 💳 **Sistema de Pagamentos Robusto**
- ✅ Planos de pagamento personalizados por escola
- ✅ Múltiplos métodos de pagamento (PIX, Cartão, Boleto, etc)
- ✅ Associação flexível de alunos a planos
- ✅ Pagamentos avulsos para itens extras
- ✅ Histórico completo e rastreável de transações
- ✅ Controle automático de atrasos e inadimplência
- ✅ Integração com Stripe para assinaturas

### 📊 **Relatórios Financeiros Avançados**
- ✅ Dashboard com métricas e KPIs de receita
- ✅ Análise mensal de arrecadação
- ✅ Breakdown por método de pagamento
- ✅ Breakdown por plano de pagamento
- ✅ Identificação automática de alunos com atrasos
- ✅ Filtros por período (até 24 meses)
- ✅ Exportação de dados

### 🔐 **Autenticação & Segurança**
- ✅ Autenticação via tokens JWT (Laravel Sanctum)
- ✅ Validação automática de assinatura ativa
- ✅ Suporte multi-tenant (múltiplas escolas)
- ✅ Isolamento seguro de dados por school
- ✅ Proteção contra CSRF, SQL Injection e XSS
- ✅ Rate limiting por IP

---

## 🛠️ Requisitos

| Requisito | Versão |
|-----------|--------|
| PHP | 8.2+ |
| Laravel | 11 |
| MySQL | 8.0+ |
| Composer | Latest |
| Node.js | 18+ (opcional) |

---

## ⚡ Instalação Rápida

### 1️⃣ Clone o repositório
```bash
git clone https://github.com/seu-usuario/class-up-api.git
cd class-up-api
```

### 2️⃣ Instale as dependências
```bash
composer install
```

### 3️⃣ Configure o ambiente
```bash
cp .env.example .env
php artisan key:generate
```

### 4️⃣ Configure o banco de dados
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=class_up
DB_USERNAME=root
DB_PASSWORD=
```

### 5️⃣ Execute as migrações
```bash
php artisan migrate
```

### 6️⃣ Inicie o servidor
```bash
php artisan serve
```

**API disponível em:** `http://localhost:8000/api/v1`

---

## 🐳 Instalação com Docker (Sail)

```bash
# Setup inicial
composer install
cp .env.example .env
php artisan key:generate

# Inicie com Sail
./vendor/bin/sail up -d

# Execute as migrações
./vendor/bin/sail artisan migrate
```

---

## 🔑 Configuração Essencial

### Variáveis de Ambiente

```env
# App
APP_NAME=ClassUp
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=class_up
DB_USERNAME=sail
DB_PASSWORD=password

# Stripe (Pagamentos)
STRIPE_KEY=pk_test_xxxxx
STRIPE_SECRET=sk_test_xxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxx

# Mail
MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com
```

### Configurar Stripe

1. Acesse [Stripe Dashboard](https://dashboard.stripe.com)
2. Vá para **Developers** → **API keys**
3. Copie suas chaves de teste
4. Configure no `.env`
5. Crie webhook em **Developers** → **Webhooks**

---

## 📚 Documentação da API

### Base URL
```
http://localhost/api/v1
```

### Autenticação
Todos os endpoints (exceto login/register) requerem:

```bash
Authorization: Bearer {seu_token_aqui}
Content-Type: application/json
```

### 🎯 Principais Endpoints

#### Autenticação
```bash
POST   /login                    # Login do usuário
POST   /register                 # Registrar novo usuário
POST   /logout                   # Logout
GET    /me                       # Dados do usuário autenticado
```

#### Alunos
```bash
GET    /students                 # Listar alunos
POST   /students                 # Criar aluno
GET    /students/{id}            # Ver aluno
PUT    /students/{id}            # Editar aluno
DELETE /students/{id}            # Deletar aluno
```

#### Turmas & Salas
```bash
GET    /classrooms               # Listar turmas
POST   /classrooms               # Criar turma
PUT    /classrooms/{id}          # Editar turma
DELETE /classrooms/{id}          # Deletar turma
```

#### Instrutores
```bash
GET    /instructors              # Listar instrutores
POST   /instructors              # Criar instrutor
PUT    /instructors/{id}         # Editar instrutor
DELETE /instructors/{id}         # Deletar instrutor
```

#### Planos de Pagamento
```bash
GET    /school-payment-plans     # Listar planos
POST   /school-payment-plans     # Criar plano
PUT    /school-payment-plans/{id} # Editar plano
DELETE /school-payment-plans/{id} # Deletar plano
```

#### Pagamentos
```bash
GET    /payments                 # Listar pagamentos (com filtros)
POST   /payments                 # Registrar novo pagamento
GET    /payments/{id}            # Ver detalhes do pagamento
PUT    /payments/{id}            # Editar pagamento
POST   /payments/{id}/mark-as-paid # Marcar como pago
DELETE /payments/{id}            # Cancelar pagamento
```

#### Financeiro
```bash
GET    /finance/summary          # Resumo financeiro completo
```

---

## 📖 Exemplos de Uso

### Login
```bash
curl -X POST http://localhost/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

**Resposta:**
```json
{
  "token": "1|abc123xyz789",
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@example.com",
    "school_id": 1
  }
}
```

### Criar Aluno
```bash
curl -X POST http://localhost/api/v1/students \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Maria Santos",
    "email": "maria@example.com",
    "phone": "11999999999",
    "date_of_birth": "2010-05-15"
  }'
```

### Listar Pagamentos com Filtros
```bash
curl -X GET "http://localhost/api/v1/payments?status=pending&from_date=2024-01-01&to_date=2024-12-31" \
  -H "Authorization: Bearer {token}"
```

### Resumo Financeiro
```bash
curl -X GET "http://localhost/api/v1/finance/summary?months=6" \
  -H "Authorization: Bearer {token}"
```

**Resposta:**
```json
{
  "currency": "BRL",
  "total_revenue": 15000.00,
  "total_overdue": 2500.00,
  "total_pending": 3200.00,
  "students_with_late_pay": 5,
  "monthly": [...],
  "by_method": [...],
  "by_plan": [...]
}
```

---

## 📂 Estrutura do Projeto

```
class-up-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/     # Controladores REST
│   │   └── Middleware/          # Middlewares customizados
│   ├── Models/                  # Modelos Eloquent ORM
│   └── Traits/                  # Traits reutilizáveis
├── database/
│   ├── migrations/              # Migrações do banco de dados
│   └── seeders/                 # Seeds para dados iniciais
├── routes/
│   └── api.php                  # Definição de rotas da API
├── tests/                       # Testes automatizados
├── class_up_api_postman_collection.json # Coleção Postman
└── README.md                    # Esta documentação
```

---

## 📮 Importar Postman

1. Abra o **Postman**
2. Clique em **Import**
3. Selecione: `class_up_api_postman_collection.json`
4. Configure as variáveis de ambiente:
   - `base_url`: `http://localhost`
   - `token`: Seu token de autenticação

Todos os 96+ endpoints estarão prontos para testar! 🚀

---

## 🔒 Segurança

| Aspecto | Implementação |
|---------|---------------|
| **Autenticação** | Laravel Sanctum (JWT tokens) |
| **Rate Limiting** | Configurado por IP |
| **CORS** | Configurado para sua frontend |
| **SQL Injection** | Eloquent ORM previne automaticamente |
| **XSS** | Proteção automática do Laravel |
| **CSRF** | Token CSRF em todas as requisições |
| **Validação** | Todos os inputs são validados |

---

## 📝 Estrutura de Resposta

### ✅ Sucesso (200)
```json
{
  "data": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@example.com"
  }
}
```

### ❌ Erro (400/422/500)
```json
{
  "error": "Descrição do erro",
  "code": "ERROR_CODE",
  "message": "Detalhes adicionais"
}
```

---

## 🤝 Contribuindo

1. Faça um **Fork** do projeto
2. Crie uma branch (`git checkout -b feature/NovaFuncionalidade`)
3. Commit suas mudanças (`git commit -m 'Add: Nova funcionalidade'`)
4. Push para a branch (`git push origin feature/NovaFuncionalidade`)
5. Abra um **Pull Request**

---

## 📞 Suporte

- 📧 **Email**: support@classup.com
- 🐛 **Issues**: [GitHub Issues](https://github.com/seu-usuario/class-up-api/issues)
- 📖 **Wiki**: [Documentação Completa](https://github.com/seu-usuario/class-up-api/wiki)

---

## 📄 Licença

Este projeto está licenciado sob a **Licença MIT** - veja o arquivo [LICENSE](LICENSE) para detalhes completos.

---

## 👨‍💻 Autores

- **Class Up Team** - Desenvolvimento e manutenção

---

## 🙌 Agradecimentos

- [Laravel Community](https://laravel.com)
- [Stripe](https://stripe.com) por integração de pagamentos
- Todos os contribuidores e usuários

---

<div align="center">

**Made with ❤️ by Class Up Team**

[⬆ voltar ao topo](#-class-up-api)

</div>
