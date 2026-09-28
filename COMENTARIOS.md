# Sistema de Comentários - Documentação

## Visão Geral
Foi implementado um sistema completo de listagem e cadastro de comentários nos produtos. O sistema permite que visitantes deixem comentários, avaliações (em estrelas) e que administradores gerenciem a aprovação desses comentários.

## Novas Funcionalidades

### 1. **Tabela de Comentários no Banco de Dados**
- Adicionada tabela `comentarios` com os seguintes campos:
  - `id`: Identificador único (INT AUTO_INCREMENT)
  - `produto_id`: ID do produto relacionado (FK)
  - `nome`: Nome do visitante (VARCHAR 120)
  - `email`: E-mail do visitante (VARCHAR 180)
  - `comentario`: Texto do comentário (TEXT)
  - `avaliacao`: Nota de 0 a 5 estrelas (INT)
  - `status`: Pendente | Aprovado | Rejeitado (ENUM)
  - `criado_em`: Data de criação (TIMESTAMP)

### 2. **Página de Formulário de Comentários** (`form_comentario.php`)
- Acessível via `/form_comentario.php?produto_id=[ID]`
- Exibe informações do produto
- Formulário para deixar comentários com:
  - Campo de nome (obrigatório)
  - Campo de e-mail (obrigatório, validado)
  - Seletor de avaliação em estrelas (0-5)
  - Área de texto para comentário (obrigatório)
- Lista comentários aprovados do produto

**Funcionalidades:**
- Sistema de rating interativo com hover visual
- Validação client-side e server-side
- Mensagem de sucesso após envio
- Comentários aguardam aprovação antes de aparecer

### 3. **Página de Listagem de Comentários** (`comentarios.php`)
- Acessível via `/comentarios.php` (requer autenticação)
- Lista todos os comentários do sistema
- Filtros por:
  - Produto específico
  - Status (Pendente, Aprovado, Rejeitado)

**Funcionalidades:**
- Visualização completa de cada comentário
- Visualização de estrelas de avaliação
- Informações do autor (nome, e-mail)
- Data de criação
- Ações de moderação para cada comentário

### 4. **Scripts de Gerenciamento de Comentários**

#### `salvar_comentario.php`
- Processa formulário de comentário via POST
- Validação de dados
- Verifica se produto existe
- Salva comentário com status "Pendente"
- Redireciona com mensagem de sucesso

#### `aprovar_comentario.php`
- Altera status do comentário para "Aprovado"
- Comentário fica visível no formulário do produto
- Acessível apenas para usuários autenticados

#### `rejeitar_comentario.php`
- Altera status do comentário para "Rejeitado"
- Comentário não aparece mais na listagem de aprovados
- Permanece no banco para auditoria

#### `excluir_comentario.php`
- Remove comentário do banco de dados
- Requer confirmação do usuário

### 5. **Integração com Página de Produtos** (`produtos.php`)
- Nova coluna "Comentários" na tabela de produtos
- Mostra total de comentários por produto
- Badge com número de comentários pendentes
- Link para gerenciar comentários do produto
- Botão "Comentários" no header para acessar página de listagem

### 6. **Estilos e Interface** (`assets/css/style.css`)
- Estilos responsivos para todos os formulários
- Cards de comentários com design moderno
- Rating de estrelas visual (★)
- Badges para status de comentários
- Suporte mobile

### 7. **Scripts JavaScript** (`assets/js/script.js`)
- Confirmação para ações destrutivas
- Sistema interativo de rating com hover
- Auto-ocultar mensagens de alerta após 5s
- Validações client-side

## Fluxo de Funcionamento

### Para Visitantes
1. Acessa página de produto via link "Comentários"
2. Visualiza comentários aprovados anteriores
3. Preenche formulário com nome, email, avaliação e comentário
4. Envia comentário que fica com status "Pendente"
5. Recebe mensagem de sucesso

### Para Administradores
1. Acessa `/comentarios.php` via painel administrativo
2. Visualiza todos os comentários
3. Filtra por produto ou status
4. Aprova comentários desejados (ficam visíveis)
5. Rejeita comentários inadequados
6. Exclui comentários conforme necessário
7. Visualiza estatísticas de comentários por produto na lista de produtos

## Validações

### Client-Side
- Nome: obrigatório
- E-mail: obrigatório e validado como email
- Comentário: obrigatório

### Server-Side
- Validação de todos os campos
- Validação de e-mail com filter_var()
- Verificação de comprimento máximo de strings
- Verificação da existência do produto
- Avaliação restrita entre 0-5

## Segurança

- Uso de prepared statements (PDO) contra SQL Injection
- Sanitização com `htmlspecialchars()` para evitar XSS
- Autenticação obrigatória para ações administrativas
- `nl2br()` para preservar quebras de linha em comentários

## Próximas Melhorias Sugeridas

1. **Sistema de Resposta**: Administradores podem responder comentários
2. **Paginação**: Quando há muitos comentários
3. **Notificações**: E-mail ao administrador quando novo comentário é deixado
4. **Relatórios**: Dashboard com estatísticas de comentários
5. **Filtro por data**: Comentários de um período específico
6. **Média de avaliações**: Mostrar nota média do produto
7. **Comentários anexados**: Permitir fotos nos comentários
8. **Sistema anti-spam**: Verificação de IP repetido

## Banco de Dados

Não esqueça de executar o arquivo `database.sql` para atualizar o banco de dados com a nova tabela de comentários:

```sql
CREATE TABLE IF NOT EXISTS comentarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id INT UNSIGNED NOT NULL,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    comentario TEXT NOT NULL,
    avaliacao INT DEFAULT 0,
    status ENUM('Pendente','Aprovado','Rejeitado') NOT NULL DEFAULT 'Pendente',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comentarios_produtos
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;
```

## Estrutura de Arquivos

```
├── comentarios.php           # Listagem de comentários (painel admin)
├── form_comentario.php       # Formulário de comentário
├── salvar_comentario.php     # Salvar comentário
├── aprovar_comentario.php    # Aprovar comentário
├── rejeitar_comentario.php   # Rejeitar comentário
├── excluir_comentario.php    # Excluir comentário
├── produtos.php              # Atualizado com integração de comentários
├── database.sql              # Atualizado com tabela de comentários
└── assets/
    ├── css/
    │   └── style.css         # Estilos completos
    └── js/
        └── script.js         # JavaScript
```

## Testes Recomendados

1. ✓ Deixar comentário como visitante
2. ✓ Visualizar mensagem de sucesso
3. ✓ Verificar comentário como pendente no painel
4. ✓ Aprovar comentário
5. ✓ Visualizar comentário aprovado na página de produto
6. ✓ Rejeitar comentário
7. ✓ Excluir comentário
8. ✓ Testar validações (campos obrigatórios, email inválido)
9. ✓ Testar filtros de status
10. ✓ Testar filtros por produto
11. ✓ Verificar contagem de comentários na lista de produtos
12. ✓ Testar responsividade em mobile

---
Sistema implementado com sucesso!
