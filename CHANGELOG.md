# Changelog

Todas as mudanças notáveis deste projeto serão documentadas neste ficheiro.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Em Desenvolvimento
- Melhorias na interface de utilizador
- Suporte para mais tipos de servidor
- Funcionalidades de auditoria avançadas

## [1.0.0] - 2025-01-06

### ✨ Adicionado
- **Toggle de Estados**: Alternância entre modo Ativo (desenvolvimento) e Protegido (produção)
- **Interface Admin Bar**: Indicador visual na barra de administração com toggle rápido
- **Página de Definições**: Interface completa em `Definições > Dev.Mode`
- **Proteção de Ficheiros**: Bloqueio automático de edições e modificações em modo protegido
- **Proteção de Utilizadores**: Impede criação não autorizada de contas
- **Hardening de Uploads**: Proteção contra execução de PHP na pasta uploads
- **Auto-Reversão**: Funcionalidade para voltar automaticamente ao modo protegido
- **Log de Atividade**: Registo detalhado de todas as alterações de estado
- **Interface AJAX**: Toggle de estados sem recarregar página
- **Suporte Multi-Servidor**: Compatibilidade com Apache (.htaccess) e IIS (web.config)
- **Internacionalização**: Suporte completo para traduções (i18n)
- **Tradução Portuguesa**: Tradução completa para pt_PT

### 🛡️ Segurança
- Verificação de nonces em todas as ações AJAX
- Validação de capabilities (`manage_options`)
- Sanitização e validação de todos os inputs
- Proteção contra execução de PHP malicioso em uploads
- Bloqueio de APIs de plugins e atualizações em modo protegido

### 🔧 Técnico
- **Namespace PSR-4**: Organização de classes com autoloader
- **WordPress Coding Standards**: Código segue convenções oficiais
- **Compatibilidade**: WordPress 6.0+ e PHP 8.1+
- **Hooks Extensíveis**: Sistema de actions/filters para extensibilidade
- **Funções Helper**: API simples para developers (`devmode_is_protected()`, etc.)

### 📚 Documentação
- README.md completo com instruções de uso
- CONTRIBUTING.md com diretrizes para contribuições
- Comentários DocBlocks em todo o código
- Ficheiros de tradução (.pot/.po)

### 🎨 Interface
- Design responsivo e acessível
- Indicadores visuais claros (verde/vermelho)
- Mensagens de confirmação para ações críticas
- Feedback visual para estados de carregamento
- Compatibilidade com temas WordPress

---

## Tipos de Mudanças

- **✨ Adicionado** para novas funcionalidades
- **🔄 Alterado** para mudanças em funcionalidades existentes  
- **❌ Depreciado** para funcionalidades que serão removidas
- **🗑️ Removido** para funcionalidades removidas
- **🔧 Corrigido** para correção de bugs
- **🛡️ Segurança** para vulnerabilidades corrigidas

## Versionamento

Este projeto usa [Semantic Versioning](https://semver.org/):

- **MAJOR** (`X.0.0`): Mudanças incompatíveis na API
- **MINOR** (`1.X.0`): Funcionalidades adicionadas de forma compatível
- **PATCH** (`1.0.X`): Correções de bugs compatíveis

## Links

- [Compare Versions](https://github.com/julioz07/dev-mode-wordpress/compare)
- [Releases](https://github.com/julioz07/dev-mode-wordpress/releases)
- [Issues](https://github.com/julioz07/dev-mode-wordpress/issues)

---

**Desenvolvido por [Júlio Rodrigues](https://github.com/julioz07) com assistência do Claude Sonnet (Anthropic AI)**