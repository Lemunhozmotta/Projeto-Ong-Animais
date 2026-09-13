# 🐾 APVAC - Sistema de Gestão para ONG de Proteção Animal

Sistema web completo para gestão de ONGs de proteção animal, desenvolvido em PHP com MySQL. Permite gerenciar animais, adoções, doações, usuários e muito mais.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Composer](https://img.shields.io/badge/Composer-2.x-885630?style=for-the-badge&logo=composer&logoColor=white)

---

## 📋 Sobre o Projeto

A **APVAC** (Associação Proteção à Vida Animal Cubatão) é uma organização sem fins lucrativos dedicada ao resgate, cuidado e adoção responsável de animais em situação de vulnerabilidade.

Este sistema foi desenvolvido para automatizar e facilitar a gestão da ONG, oferecendo:

- 🐕 **Gestão de Animais** — Cadastro, edição, exclusão e upload de fotos
- ❤️ **Sistema de Adoção** — Solicitação, aprovação e acompanhamento
- 💰 **Sistema de Doações** — Doações em dinheiro (PIX) e itens
- 👥 **Gestão de Usuários** — Níveis de acesso (Admin, Gestor, Voluntário, Usuário)
- 🔐 **Recuperação de Senha** — Envio de e-mail com token seguro
- 📊 **Painel Administrativo** — Controle total do sistema

---

## 🚀 Tecnologias Utilizadas

- **PHP 8.0+** — Linguagem backend
- **MySQL** — Banco de dados
- **HTML5 + CSS3** — Interface
- **JavaScript** — Interatividade
- **Composer** — Gerenciador de dependências
- **PHPMailer** — Envio de e-mails
- **Mailtrap** — Teste de e-mails (desenvolvimento)
- **Font Awesome** — Ícones

---

## 📁 Estrutura do Projeto

Projeto-Ong-Animais/
├── estilos/
│ ├── style_geral.css
│ ├── style_index.css
│ ├── style_sobre.css
│ ├── style_projetos.css
│ ├── style_doar.css
│ ├── style_contato.css
│ ├── style_perfil.css
│ ├── style_admin.css
│ ├── style_admin_usuarios.css
│ ├── style_admin_adocoes.css
│ ├── style_admin_doacoes.css
│ └── style_recuperar.css
├── img/
│ ├── logoONG.png
│ ├── parallaxCao.jpg
│ ├── parallaxGato.webp
│ └── parallax3.png
├── uploads/
│ └── animais/ (fotos dos animais)
├── vendor/ (dependências do Composer - NÃO subir)
├── index.php
├── sobre.php
├── projetos.php
├── doar.php
├── contato.php
├── perfil.php
├── login.php
├── cadastro.php
├── logout.php
├── adotar.php
├── recuperar_senha.php
├── redefinir_senha.php
├── admin_animais.php
├── admin_editar_animal.php
├── admin_usuarios.php
├── admin_editar_usuario.php
├── admin_adocoes.php
├── admin_doacoes.php
├── conexao.php
├── mailer.php
├── .env (NÃO subir)
├── .env.example
├── .gitignore
├── composer.json
├── composer.lock
├── database.sql
└── README.md

---

## ⚙️ Como Instalar

### Pré-requisitos

- **XAMPP** (ou similar) com PHP 8.0+ e MySQL
- **Composer** ([download](https://getcomposer.org/download/))
- **Git** ([download](https://git-scm.com/))

### Passo a Passo

#### 1. Clonar o repositório

```bash
git clone https://github.com/seu-usuario/Projeto-Ong-Animais.git
cd Projeto-Ong-Animais

2. Instalar dependências do Composer
bash
composer install
Isso instalará o PHPMailer automaticamente.

3. Configurar o banco de dados
Abra o phpMyAdmin: http://localhost/phpmyadmin

Crie um banco de dados chamado ong

Importe o arquivo database.sql (está na raiz do projeto)

Ou execute no terminal:

bash
mysql -u root -p ong < database.sql
4. Configurar o arquivo .env
Copie o arquivo .env.example e renomeie para .env

Edite com suas credenciais:

env
# Banco de dados
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=ong

# Configurações de e-mail (Mailtrap para testes)
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USER=seu_username_mailtrap
MAIL_PASS=sua_senha_mailtrap
MAIL_FROM=apvac.projeto@protonmail.com
MAIL_FROM_NAME=APVAC
5. Ajustar conexao.php
Edite o arquivo conexao.php com os dados do seu banco:

php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "ong";
6. Criar a pasta de uploads
bash
mkdir uploads
mkdir uploads/animais
Ou crie manualmente pelo explorador de arquivos.

7. Acessar o sistema
Abra no navegador:

text
http://localhost/Projeto-Ong-Animais/
👤 Usuários Padrão
Após importar o database.sql, você terá estes usuários para teste:

Email	Senha	Nível
admin@apvac.org.br	123	1 - Administrador
valeria_vet@gmail.com	123	2 - Gestor
maria@email.com	123	3 - Voluntário
usuario@email.com	123	4 - Usuário
⚠️ Altere as senhas em produção!

🔐 Níveis de Acesso
Nível	Nome	O que pode fazer
1	Administrador	Acesso total (animais, adoções, doações, usuários)
2	Gestor	Acesso a animais, adoções e doações
3	Voluntário	Acesso básico (perfil, doar, adoções)
4	Usuário	Acesso comum (perfil, doar, projetos)
📧 Configuração de E-mail
Para o sistema de recuperação de senha funcionar, é necessário configurar um serviço de e-mail.

Opção 1: Mailtrap (Recomendado para desenvolvimento)
Crie uma conta em mailtrap.io

Pegue as credenciais SMTP

Coloque no .env

Opção 2: Brevo, Gmail, etc (Produção)
Para produção, substitua pelas credenciais do seu provedor.

🎯 Funcionalidades
Para Visitantes
✅ Ver página inicial com animais disponíveis

✅ Ver informações sobre a ONG

✅ Ver projetos

✅ Cadastrar-se

Para Usuários Logados
✅ Solicitar adoção

✅ Fazer doações (dinheiro via PIX ou itens)

✅ Ver perfil com histórico

✅ Recuperar senha por e-mail

Para Administradores
✅ Cadastrar/editar/excluir animais

✅ Aprovar/rejeitar adoções

✅ Confirmar/excluir doações

✅ Gerenciar usuários e níveis de acesso

🛠️ Tecnologias e Boas Práticas
✅ Separação de CSS — Um arquivo para cada página

✅ Níveis de Acesso — Sistema de permissões robusto

✅ Senhas Criptografadas — Uso de password_hash()

✅ Tokens Seguros — Recuperação de senha com random_bytes()

✅ Proteção contra SQL Injection — Uso de prepare() e bind_param()

✅ Variáveis de Ambiente — Uso de .env para credenciais

✅ Upload Seguro — Validação de tipo e tamanho de arquivo

✅ Transações — Uso de begin_transaction() para operações críticas

📸 Screenshots
Adicione aqui as imagens do sistema em funcionamento

🤝 Contribuindo
Contribuições são bem-vindas! Para contribuir:

Faça um Fork do projeto

Crie uma branch: git checkout -b minha-feature

Commit suas mudanças: git commit -m 'Adiciona nova funcionalidade'

Push para a branch: git push origin minha-feature

Abra um Pull Request

📝 Licença
Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

👨‍💻 Autor
Leandro Munhoz Motta

GitHub: @seu-usuario

LinkedIn: seu-perfil

🙏 Agradecimentos
APVAC — Por inspirar este projeto

Comunidade PHP — Por todas as bibliotecas e documentação

Mailtrap — Pela ferramenta de teste de e-mails

<p align="center"> Feito com ❤️ para ajudar os animais 🐾 </p>
