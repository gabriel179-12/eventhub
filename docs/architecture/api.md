# Contrato e convenções da API

## Convenções gerais

- Base URL: `/api/v1`.
- Formato: JSON em requisições e respostas, exceto upload multipart.
- Datas: ISO 8601 em UTC, convertidas para o fuso do evento na interface.
- Valores: inteiros em centavos, por exemplo `30000` para R$ 300,00.
- Identificadores públicos: UUID ou ULID; não expor IDs sequenciais em URLs públicas.
- Listagens: paginação com `data`, `meta` e `links`.
- Documentação: OpenAPI/Swagger publicada junto da API.

## Recursos iniciais

| Recurso | Rotas exemplares |
|---|---|
| Autenticação | `POST /auth/register`, `POST /auth/login`, `POST /auth/forgot-password`, `POST /auth/logout` |
| Usuário atual | `GET /me`, `PATCH /me`, `PATCH /me/password` |
| Eventos públicos | `GET /events`, `GET /events/{event}` |
| Eventos do organizador | `POST /organizers/{organizer}/events`, `PATCH /events/{event}`, `POST /events/{event}/publish` |
| Ingressos | `POST /events/{event}/ticket-types`, `PATCH /ticket-types/{ticketType}` |
| Pedidos | `POST /orders`, `GET /orders/{order}`, `GET /me/orders` |
| Pagamentos | `POST /orders/{order}/payments`, `POST /payments/webhook/{provider}` |
| Ingressos do participante | `GET /me/tickets`, `GET /tickets/{ticket}` |
| Check-in | `POST /checkins/validate`, `POST /checkins/entry`, `POST /checkins/exit` |
| Métricas | `GET /dashboard/organizer`, `GET /admin/dashboard` |

## Estrutura de resposta

Resposta de sucesso:

```json
{
  "data": {
    "id": "01J...",
    "status": "published"
  }
}
```

Erro de validação:

```json
{
  "message": "Os dados enviados são inválidos.",
  "errors": {
    "starts_at": ["A data de início deve ser futura."]
  }
}
```

## Códigos HTTP

| Código | Uso |
|---|---|
| `200` | Consulta ou atualização bem-sucedida |
| `201` | Recurso criado |
| `202` | Tarefa assíncrona aceita |
| `204` | Operação sem corpo de resposta |
| `401` | Não autenticado |
| `403` | Autenticado, mas sem permissão |
| `404` | Recurso inexistente ou inacessível |
| `409` | Conflito de estado, estoque ou operação repetida |
| `422` | Regra de validação não atendida |
| `429` | Limite de requisições excedido |

## Regras para endpoints críticos

### Criação de pedido

`POST /orders` recebe os itens escolhidos. A API recalcula valores e disponibilidade no servidor; ela não aceita preço nem total calculado pelo navegador. O retorno inclui o pedido pendente, prazo de expiração e instruções de pagamento.

### Confirmação de pagamento

O endpoint de webhook ou confirmação deve ser idempotente, autenticado pelo provedor e registrar a referência externa. Um pagamento aprovado novamente retorna sucesso sem gerar novos ingressos.

### Check-in

O endpoint recebe o token lido e a ação solicitada. Ele não confia em evento ou usuário enviados pelo cliente: ambos são obtidos/validados a partir do token e da sessão do operador. Toda tentativa — inclusive negada — pode ser auditada.

## Versionamento e compatibilidade

Mudanças incompatíveis criam uma versão nova, como `/api/v2`; correções, campos adicionais opcionais e novas rotas permanecem na versão atual. A documentação OpenAPI deve ser atualizada no mesmo pull request de cada mudança de contrato.
