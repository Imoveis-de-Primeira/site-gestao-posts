# IP Gestão de Posts

Plugin WordPress para acompanhar a produção editorial por meio de um dashboard administrativo e de um calendário de publicações.

## Recursos

- indicadores de posts publicados, autores ativos, categorias utilizadas e média mensal por autor;
- filtros por período e tipo de post;
- rankings de autores e categorias;
- gráfico de evolução e heatmap de publicações;
- metas de publicação com acompanhamento de progresso;
- calendário editorial mensal, semanal e em lista;
- cores personalizadas por autor;
- suporte aos tipos `post`, `page`, `lp_lancamento` e `lp_lancamentos`, quando registrados no WordPress.

## Requisitos

- WordPress 5.5 ou superior;
- PHP 7.4 ou superior;
- uma conta WordPress com a permissão `edit_posts` para visualizar o dashboard e o calendário;
- uma conta administradora com `manage_options` para alterar configurações, metas e cores.

Não há etapa de build nem dependências de Composer, npm ou Python. Chart.js 4.4.1 e FullCalendar 6.1.10 estão versionados em `assets/vendor/`, permitindo que o plugin funcione sem baixar recursos de CDNs.

## Instalação

### Pelo Git

Na pasta `wp-content/plugins` da instalação WordPress, execute:

```bash
git clone https://github.com/Imoveis-de-Primeira/site-gestao-posts.git ip-gestao-de-posts
```

Depois, acesse **Plugins > Plugins instalados** no painel do WordPress e ative **IP Gestão de Posts**.

### Por arquivo ZIP

1. Baixe ou gere um ZIP contendo este diretório.
2. No painel do WordPress, acesse **Plugins > Adicionar plugin > Enviar plugin**.
3. Selecione o ZIP, instale e ative o plugin.

O arquivo `ip-gestao-de-posts.php` deve ficar na raiz da pasta do plugin, e não dentro de uma segunda pasta aninhada.

## Uso

Depois da ativação, o menu **IP Gestão de Posts** aparece no painel administrativo:

- **Dashboard** apresenta indicadores, rankings, metas e evolução das publicações;
- **Calendário** organiza os posts publicados por data e autor;
- **Configurações** controla informações exibidas, metas e cores dos autores.

As consultas consideram apenas conteúdo com status `publish`. Os dados do dashboard podem permanecer em cache por até uma hora.

## Estrutura do projeto

```text
assets/                     CSS, JavaScript e bibliotecas de terceiros
includes/                   Serviços e controladores PHP
languages/                  Arquivos de tradução
views/                      Telas do painel administrativo
ip-gestao-de-posts.php      Arquivo principal do plugin
requirements.txt            Declaração de dependências Python (nenhuma)
```

## Segurança

As rotas AJAX exigem nonce do WordPress e verificam as capacidades do usuário. Não adicione ao repositório arquivos como `.env`, chaves privadas, tokens de acesso, senhas ou exports do banco de dados.

Antes de publicar uma alteração, verifique tanto os arquivos atuais quanto o histórico Git. Se uma credencial real já tiver sido commitada, revogue-a imediatamente; apagá-la apenas do commit mais recente não é suficiente.

## Dependências de terceiros

- [Chart.js 4.4.1](https://www.chartjs.org/) — licença MIT;
- [FullCalendar 6.1.10](https://fullcalendar.io/) — licença MIT para os componentes incluídos neste projeto.

## Contribuição

1. Crie um fork e uma branch para a alteração.
2. Teste o plugin em uma instalação WordPress compatível.
3. Abra um pull request descrevendo o problema resolvido, o comportamento esperado e como a mudança foi validada.

Ao contribuir, não inclua dados reais de sites, usuários ou clientes nos commits e exemplos.

## Licença

Este repositório ainda não inclui uma licença para o código próprio. Antes de incentivar uso e redistribuição por parceiros, escolha uma licença compatível com o ecossistema WordPress e adicione o arquivo `LICENSE`.
