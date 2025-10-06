# 🛡️ Dev.Mode - WordPress Security Plugin

![Dev.Mode Banner](assets/images/devmode-banner.png)

![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-purple)
![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-orange)
![Version](https://img.shields.io/badge/Version-1.0.0-green)

**🌍 Language / Idioma:**  
[![Português](https://img.shields.io/badge/🇵🇹-Português-green?style=for-the-badge)](README.md) [![English](https://img.shields.io/badge/🇺🇸-English-blue?style=for-the-badge)](README_EN.md)

**Dev.Mode** é um plugin WordPress gratuito que oferece proteção inteligente alternando entre dois estados de segurança: **Ativo** para desenvolvimento e **Protegido** para produção.

## ✨ Características Principais

### 🎯 Estados Inteligentes

| Estado | Cor | Funcionalidades |
|--------|-----|-----------------|
| **🟢 Ativo** | Verde | ✅ Permite instalações/atualizações<br>✅ Permite edição de ficheiros<br>✅ Permite criação de utilizadores |
| **🔴 Protegido** | Vermelho | 🛡️ Bloqueia modificações no core<br>🛡️ Impede instalação de plugins/temas<br>🛡️ Protege contra criação de utilizadores<br>🛡️ Hardening automático |

### 🚀 Interface Simples

- **Toggle na Admin Bar**: Alternar estados com um clique
- **Página de Definições**: Configuração completa em `Definições > Dev.Mode`
- **Indicadores Visuais**: Cores claras (verde/vermelho) para identificar o estado
- **Confirmações de Segurança**: Avisos antes de alterações críticas

### 🔒 Proteções Avançadas

- **Proteção de Uploads**: Bloqueia execução de PHP na pasta uploads
- **Bloqueio de Ficheiros**: Desativa editores WordPress quando protegido
- **Gestão de Utilizadores**: Impede criação não autorizada de contas
- **Auto-Reversão**: Volta automaticamente ao modo protegido após X horas
- **Log de Atividade**: Registo detalhado de todas as alterações

## 📥 Instalação

### Método 1: Upload Direto
1. Descarregue o plugin
2. Extraia para `wp-content/plugins/dev-mode/`
3. Ative em `Plugins > Plugins Instalados`

### Método 2: Upload via Admin
1. Vá para `Plugins > Adicionar Novo > Carregar Plugin`
2. Selecione o ficheiro ZIP
3. Clique "Instalar Agora" e depois "Ativar"

## ⚙️ Como Usar

### Alternar Estados

**Via Admin Bar (Recomendado)**
1. Clique no indicador colorido na barra superior
2. Confirme a alteração no diálogo

**Via Página de Definições**
1. Aceda a `Definições > Dev.Mode`
2. Clique no botão grande de estado
3. Configure opções adicionais se necessário

### Configurações Disponíveis

- **Bloquear Criação de Utilizadores**: Impede novos registos em modo protegido
- **Desativar Modificações**: Bloqueia updates/instalações quando protegido
- **Proteger Uploads**: Impede execução de PHP na pasta uploads
- **Auto-Reversão**: Define horas para voltar automaticamente ao modo protegido

## 🎯 Casos de Uso

### 👨‍💻 Para Programadores
- Ativar durante desenvolvimento/testes
- Proteger automaticamente em produção
- Prevenir modificações acidentais

### � Para Agências
- Proteger sites de clientes
- Permitir acesso controlado para updates
- Manter log de todas as alterações

### 🛡️ Para Segurança
- Hardening automático
- Proteção contra malware em uploads
- Bloqueio de modificações suspeitas

## 📋 Requisitos

- **WordPress**: 6.0 ou superior
- **PHP**: 8.1 ou superior  
- **Permissões**: Utilizador com capability `manage_options`
- **Servidor**: Apache (recomendado) ou IIS

## 🔍 Resolução de Problemas

### Plugin Não Aparece na Admin Bar
- Verificar se o utilizador tem permissões `manage_options`
- Confirmar se o plugin está ativo
- Limpar cache do site/browser

### Proteção de Uploads Não Funciona
- Verificar se a pasta uploads é escrivível
- Testar na página de definições (secção de status)
- Confirmar tipo de servidor (Apache/IIS)

### Estados Não Persistem
- Verificar se a base de dados é escrivível
- Desativar temporariamente plugins de cache
- Verificar conflitos com outros plugins de segurança

## 🤝 Contribuições

Este é um projeto **open source gratuito**! Contribuições são muito bem-vindas:

- 🐛 **Reporte bugs** via [Issues](https://github.com/YOUR-GITHUB-USERNAME/dev-mode-wordpress/issues)
- 💡 **Sugira melhorias** com novas funcionalidades
- 🔧 **Contribua código** através de Pull Requests
- 🌍 **Ajude com traduções** para outros idiomas
- 📖 **Melhore a documentação**

Veja o [guia de contribuições](CONTRIBUTING.md) para mais detalhes.

## 📄 Licença

Este plugin está licenciado sob [Creative Commons BY-NC-SA 4.0](LICENSE).

**Em resumo:**
- ✅ **Uso gratuito** para fins pessoais e não-comerciais
- ✅ **Modificação e partilha** permitidas 
- ✅ **Contribuições** são bem-vindas
- ❌ **Uso comercial** sem autorização
- ❌ **Venda ou redistribuição** comercial

## �‍💻 Autor

**Júlio Rodrigues** - WordPress & Frontend Developer  
🌍 Portugal | 🔧 10+ anos de experiência  

- 🌐 **Website**: [julio-cr.pt](https://julio-cr.pt/)
- 💼 **LinkedIn**: [juliocesarrodrigues07](https://www.linkedin.com/in/juliocesarrodrigues07/)
- 🐙 **GitHub**: [julioz07](https://github.com/julioz07)
- 📧 **Empresa**: JCR Digital

### 🤖 Desenvolvido com o apoio de IA

Este plugin foi desenvolvido com a assistência do **Claude Sonnet** (Anthropic AI), demonstrando a colaboração entre desenvolvimento humano e inteligência artificial.

## �🙏 Agradecimentos

Obrigado a todos os contribuidores que ajudam a tornar este plugin melhor!

---

**🔗 Links Úteis**
- [Documentação Completa](README.md)
- [Guia de Contribuições](CONTRIBUTING.md)  
- [Reportar Bug](https://github.com/julioz07/dev-mode-wordpress/issues)
- [Releases](https://github.com/julioz07/dev-mode-wordpress/releases)

**Feito com ❤️ para a comunidade WordPress**