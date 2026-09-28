SISTEMA DE GERENCIAMENTO DE PRODUTOS - EMPRESA DE ACESSÓRIOS

REQUISITOS
- XAMPP com Apache, PHP e MySQL
- PHP com extensão PDO, fileinfo e mbstring habilitadas

INSTALAÇÃO NO XAMPP
1. Copie a pasta "empresa-acessorios" para:
   C:\xampp\htdocs\

2. Abra o XAMPP Control Panel.

3. Inicie:
   - Apache
   - MySQL

4. Abra o phpMyAdmin:
   http://localhost/phpmyadmin/

5. Importe o arquivo:
   database.sql

   O banco "empresa_acessorios" e as tabelas serão criados automaticamente.

6. Confira o arquivo conexao.php.
   Configuração padrão do XAMPP:
   - host: localhost
   - usuário: root
   - senha: vazia

7. Acesse:
   http://localhost/empresa-acessorios/login.php

PRIMEIRO USUÁRIO
- Clique em "Criar conta".
- Preencha nome, e-mail e senha.
- Depois faça login.

CADASTRO DE PRODUTO
- Entre no sistema.
- Clique em "+ Adicionar Produto".
- Preencha os dados.
- Selecione uma ou várias imagens.
- Salve.

OBSERVAÇÕES
- As imagens ficam em uploads/produtos/.
- As senhas são armazenadas usando password_hash().
- O login usa password_verify().
- Produtos exigem autenticação.
- O sistema usa PDO e prepared statements.
