# Contributing to Dev.Mode

Obrigado pelo seu interesse em contribuir para o Dev.Mode! 🎉

O Dev.Mode é um projeto open source gratuito e valorizamos todas as contribuições da comunidade. Este guia irá ajudá-lo a começar.

## 📋 Código de Conduta

Ao participar neste projeto, espera-se que mantenha um ambiente respeitoso e acolhedor para todos. Seja gentil, construtivo e profissional nas suas interações.

## 🚀 Como Contribuir

### Reportar Bugs 🐛

Antes de reportar um bug, verifique se já não existe uma issue similar:

1. Vá para [Issues](https://github.com/julioz07/dev-mode-wordpress/issues)
2. Pesquise por problemas similares
3. Se não existir, crie uma nova issue com:
   - **Título claro e descritivo**
   - **Descrição detalhada** do problema
   - **Passos para reproduzir** o bug
   - **Comportamento esperado** vs **comportamento atual**
   - **Ambiente**: versão WordPress, PHP, tema, outros plugins
   - **Screenshots** se aplicável

### Sugerir Melhorias 💡

Tem uma ideia para melhorar o plugin? Adoramos sugestões!

1. Verifique se a sugestão já não foi feita
2. Crie uma issue com:
   - **Título claro** da funcionalidade
   - **Descrição detalhada** da melhoria
   - **Justificação** - porque seria útil
   - **Exemplos de uso** se aplicável

### Contribuir com Código 💻

#### Configuração do Ambiente

1. **Fork** o repositório
2. **Clone** o seu fork:
   ```bash
   git clone https://github.com/SEU-USERNAME/dev-mode-wordpress.git
   cd dev-mode-wordpress
   ```
3. **Instale** num ambiente WordPress local
4. **Teste** se tudo funciona

#### Processo de Desenvolvimento

1. **Crie um branch** para a sua funcionalidade:
   ```bash
   git checkout -b feature/nova-funcionalidade
   ```

2. **Faça as suas alterações** seguindo as diretrizes:
   - Código limpo e bem comentado
   - Siga as convenções WordPress
   - Teste todas as alterações
   - Mantenha compatibilidade com versões suportadas

3. **Commit** as suas alterações:
   ```bash
   git commit -m "Adiciona nova funcionalidade X"
   ```

4. **Push** para o seu fork:
   ```bash
   git push origin feature/nova-funcionalidade
   ```

5. **Crie um Pull Request** com:
   - Título claro do que foi alterado
   - Descrição detalhada das mudanças
   - Referência à issue relacionada (se existir)
   - Screenshots se aplicável

## 📝 Diretrizes de Código

### Padrões PHP
- Seguir [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- PHP 8.1+ compatível
- DocBlocks em todas as funções e classes
- Nomes de variáveis/funções em inglês

### Estrutura de Classes
```php
<?php
namespace DevMode;

/**
 * Descrição da classe
 */
class MinhaClasse {
    
    /**
     * Descrição do método
     *
     * @param string $param Descrição do parâmetro
     * @return bool Descrição do retorno
     */
    public function meuMetodo($param) {
        // Código aqui
    }
}
```

### JavaScript/CSS
- Código limpo e bem estruturado
- Comentários quando necessário
- Compatibilidade com browsers modernos
- Seguir convenções WordPress

### Internacionalização
- Todas as strings devem usar text-domain `dev-mode`
- Usar funções `__()`, `_e()`, `_n()` adequadamente
- Atualizar ficheiros .pot quando necessário

## 🧪 Testes

Antes de submeter um PR, teste:

1. **Instalação/Ativação** do plugin
2. **Toggle entre estados** (Admin Bar + Settings)
3. **Funcionalidades de proteção** em ambos os estados
4. **Interface administrativa** (responsiva)
5. **Compatibilidade** com outros plugins comuns
6. **Diferentes versões** WordPress/PHP suportadas

## 📚 Tipos de Contribuições

### 🔧 Código
- Correção de bugs
- Novas funcionalidades
- Melhorias de performance
- Refatoração de código

### 📖 Documentação
- Melhorias no README
- Comentários no código
- Tutoriais e guias
- Tradução da documentação

### 🌍 Traduções
- Tradução para novos idiomas
- Correção de traduções existentes
- Atualização de ficheiros .po/.pot

### 🎨 Design/UI
- Melhorias na interface
- Icons e assets
- Experiência do utilizador
- Responsividade

## 📋 Checklist para Pull Requests

Antes de submeter, verifique:

- [ ] Código funciona sem erros
- [ ] Testes manuais realizados
- [ ] Documentação atualizada se necessário
- [ ] Tradução portuguesa atualizada
- [ ] Código segue padrões WordPress
- [ ] Sem conflitos com branch main
- [ ] Descrição clara do PR

## 🏷️ Versionamento

Seguimos [Semantic Versioning](https://semver.org/):

- **MAJOR** (1.0.0): Mudanças incompatíveis
- **MINOR** (1.1.0): Funcionalidades compatíveis
- **PATCH** (1.0.1): Correções de bugs

## 📞 Suporte

- **Issues**: Para bugs e sugestões
- **Discussions**: Para perguntas gerais
- **GitHub**: [@julioz07](https://github.com/julioz07)
- **LinkedIn**: [Júlio Rodrigues](https://www.linkedin.com/in/juliocesarrodrigues07/)

## 📄 Licença

Ao contribuir, concorda que as suas contribuições serão licenciadas sob a mesma licença do projeto: [Creative Commons BY-NC-SA 4.0](LICENSE).

**Importante**: Este projeto é gratuito para uso pessoal e não-comercial. Contribuições não podem ser vendidas ou redistribuídas comercialmente.

---

**Obrigado por contribuir para o Dev.Mode! 🙏**

Cada contribuição, grande ou pequena, ajuda a tornar este plugin melhor para toda a comunidade WordPress.

**Desenvolvido por [Júlio Rodrigues](https://github.com/julioz07) com assistência do Claude Sonnet (Anthropic AI)**